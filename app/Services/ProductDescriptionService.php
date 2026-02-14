<?php

namespace App\Services;

use Illuminate\Support\Str;

class ProductDescriptionService
{
    /**
     * Create a standardized description structure.
     *
     * @param  array  $sections  Array of section data
     * @return array
     */
    public static function createDescription(array $sections): array
    {
        $validated = [];

        foreach ($sections as $section) {
            $validated[] = [
                'key' => $section['key'] ?? Str::slug($section['title'] ?? 'section'),
                'title' => $section['title'] ?? '',
                'content' => $section['content'] ?? '',
                'media' => isset($section['media']) ? array_filter([
                    'type' => $section['media']['type'] ?? null,
                    'url' => $section['media']['url'] ?? null,
                ]) ?: null : null,
            ];
        }

        return ['sections' => $validated];
    }

    /**
     * Get a specific section by key.
     *
     * @param  array|null  $description
     * @param  string  $key
     * @return array|null
     */
    public static function getSection(?array $description, string $key): ?array
    {
        if (! $description || ! isset($description['sections'])) {
            return null;
        }

        foreach ($description['sections'] as $section) {
            if ($section['key'] === $key) {
                return $section;
            }
        }

        return null;
    }

    /**
     * Update a specific section.
     *
     * @param  array|null  $description
     * @param  string  $key
     * @param  array  $data
     * @return array
     */
    public static function updateSection(?array $description, string $key, array $data): array
    {
        $description = $description ?? ['sections' => []];

        $sections = $description['sections'] ?? [];
        $found = false;

        foreach ($sections as &$section) {
            if ($section['key'] === $key) {
                $section = array_merge($section, $data);
                $found = true;
                break;
            }
        }

        // If section not found, add it
        if (! $found) {
            $sections[] = array_merge(['key' => $key], $data);
        }

        return ['sections' => $sections];
    }

    /**
     * Remove a section by key.
     *
     * @param  array|null  $description
     * @param  string  $key
     * @return array|null
     */
    public static function removeSection(?array $description, string $key): ?array
    {
        if (! $description || ! isset($description['sections'])) {
            return $description;
        }

        $sections = $description['sections'];
        $description['sections'] = array_filter($sections, function ($section) use ($key) {
            return ($section['key'] ?? null) !== $key;
        });

        return empty($description['sections']) ? null : $description;
    }

    /**
     * Convert legacy text description to new JSON format.
     * Useful for data migration from text to JSON.
     *
     * @param  string|null  $legacyText
     * @return array|null
     */
    public static function migrateFromText(?string $legacyText): ?array
    {
        if (! $legacyText) {
            return null;
        }

        // Create a single section with the entire text as content
        return [
            'sections' => [
                [
                    'key' => 'legacy',
                    'title' => 'Mô tả chi tiết',
                    'content' => $legacyText,
                    'media' => null,
                ],
            ],
        ];
    }

    /**
     * Get all sections as a flat array (for templates/views).
     *
     * @param  array|null  $description
     * @return array
     */
    public static function getSections(?array $description): array
    {
        return $description['sections'] ?? [];
    }

    /**
     * Export description to HTML format.
     * Useful for rendering or API responses.
     *
     * @param  array|null  $description
     * @return string
     */
    public static function toHtml(?array $description): string
    {
        if (! $description || empty($description['sections'])) {
            return '';
        }

        $html = '';
        foreach ($description['sections'] as $section) {
            $html .= sprintf(
                '<section class="product-description-section" data-key="%s">' .
                '<h3 class="section-title">%s</h3>' .
                '<div class="section-content">%s</div>',
                htmlspecialchars($section['key']),
                htmlspecialchars($section['title']),
                $section['content'] // Keep as-is, assuming it's already safe HTML
            );

            if (! empty($section['media'])) {
                $html .= self::renderMedia($section['media']);
            }

            $html .= '</section>';
        }

        return $html;
    }

    /**
     * Render media HTML.
     *
     * @param  array  $media
     * @return string
     */
    protected static function renderMedia(array $media): string
    {
        if (! isset($media['type']) || ! isset($media['url'])) {
            return '';
        }

        $url = $media['url'];
        
        // If URL is not absolute, prepend asset path
        if (! preg_match('/^https?:\/\//', $url) && ! str_starts_with($url, '/')) {
            // If it already has path structure (e.g. clothes/img.jpg), prepend img root
            if (str_contains($url, '/')) {
                 $url = asset('clients/assets/img/'.$url);
            } else {
                 // Fallback for old data: assume clothes folder
                 $url = asset('clients/assets/img/clothes/'.$url);
            }
        }
        
        $url = htmlspecialchars($url);

        return match ($media['type']) {
            'image' => sprintf(
                '<figure class="section-media"><img src="%s" alt="Section media"></figure>',
                $url
            ),
            'video' => sprintf(
                '<figure class="section-media"><video controls><source src="%s"></video></figure>',
                $url
            ),
            default => '',
        };
    }

    /**
     * Parse HTML content into structured sections based on H2/H3 tags.
     *
     * @param string|null $html
     * @return array
     */
    public static function parseHtmlToSections(?string $html): array
    {
        if (empty($html)) {
            return ['sections' => []];
        }

        // 1. Clean up "data-start/end" attributes from the user's tool
        $html = preg_replace('/\s+data-(start|end|col-size)="[^"]*"/', '', $html);

        // 2. Load into DOMDocument
        $dom = new \DOMDocument();
        // Force UTF-8 and wrap in body to ensure correct parsing of fragments
        $uniqueId = 'html-wrapper-' . uniqid();
        $htmlWrapped = '<!DOCTYPE html><html><body id="'.$uniqueId.'">' . $html . '</body></html>';
        
        // Suppress warnings for malformed HTML
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $htmlWrapped, LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $sections = [];
        $currentSection = null;
        
        $xpath = new \DOMXPath($dom);
        // Get direct children of the body wrapper
        $nodes = $xpath->query('//body[@id="'.$uniqueId.'"]/*');

        foreach ($nodes as $node) {
            $tagName = strtolower($node->nodeName);
            
            // If we find H2 or H3, start a new section
            if ($tagName === 'h2' || $tagName === 'h3') {
                // Save previous section if exists
                if ($currentSection) {
                    $sections[] = $currentSection;
                }

                // Start new section
                $title = trim($node->textContent);
                $key = Str::slug($title);
                
                // Map common titles to standard keys
                if (Str::contains($key, ['gioi-thieu', 'introduction'])) $key = 'intro';
                elseif (Str::contains($key, ['dac-diem', 'feature'])) $key = 'feature';
                elseif (Str::contains($key, ['cong-dung', 'use'])) $key = 'use';
                elseif (Str::contains($key, ['y-nghia', 'meaning'])) $key = 'meaning';
                elseif (Str::contains($key, ['cham-soc', 'care', 'huong-dan'])) $key = 'care';

                $currentSection = [
                    'key' => $key,
                    'title' => $title,
                    'content' => '',
                    'media' => null, // Media parsing is harder, leaving null for now
                ];
            } else {
                // Append content to current section
                // If it's the start and no section exists yet, create an 'intro' section
                if (!$currentSection) {
                    // Check if node is empty text
                    if ($node->nodeType === XML_TEXT_NODE && trim($node->textContent) === '') {
                        continue;
                    }
                    // For HR tags, ignore them as section separators if they appear before headers
                    if ($tagName === 'hr') {
                        continue;
                    }

                    $currentSection = [
                        'key' => 'intro', // Default first section key
                        'title' => 'Giới thiệu',
                        'content' => '',
                        'media' => null,
                    ];
                }

                // If node is HR, it might signal end of section, but generally we split by Headers.
                // We'll ignore HRs as they are often just visual separators.
                if ($tagName === 'hr') {
                    continue;
                }

                $content = $dom->saveHTML($node);
                $currentSection['content'] .= $content;
            }
        }

        // Add the last section
        if ($currentSection) {
            $sections[] = $currentSection;
        }

        return ['sections' => $sections];
    }
}
