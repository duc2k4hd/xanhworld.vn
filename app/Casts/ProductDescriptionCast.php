<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class ProductDescriptionCast implements CastsAttributes
{
    /**
     * Cast the value from the database.
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return array|null
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?array
    {
        if (is_null($value)) {
            return null;
        }

        // Try to decode JSON
        $decoded = json_decode($value, true);

        // If valid JSON and has strict structure, return it
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && isset($decoded['sections'])) {
            return $decoded;
        }

        // BACKWARD COMPATIBILITY:
        // If value is not valid JSON or doesn't have 'sections', treat it as legacy text
        return [
            'sections' => [
                [
                    'key' => 'legacy',
                    'title' => 'Mô tả chi tiết',
                    'content' => $value, // The original text
                    'media' => null,
                ]
            ]
        ];
    }

    /**
     * Cast the value for storage.
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return string|null
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        \Illuminate\Support\Facades\Log::info('ProductDescriptionCast::set - Incoming value', ['value' => $value]);
        
        if (is_null($value)) {
            return null;
        }

        // If already a JSON string, validate and re-encode for consistency
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException('Invalid JSON provided for description');
            }
            $value = $decoded;
        }

        // Cleanup empty media URLs
        if (is_array($value) && isset($value['sections']) && is_array($value['sections'])) {
            foreach ($value['sections'] as &$section) {
                if (isset($section['media']) && is_array($section['media'])) {
                    if (empty($section['media']['url'])) {
                        // Create a temporary array without 'media' to avoid modifying the array structure while iterating if possible,
                        // but here we are modifying value of the key, which is fine.
                        // However, we want to REMOVE the key.
                        $temp = $section;
                        unset($temp['media']);
                        $section = $temp;
                    }
                }
            }
        }

        // Validate structure
        $this->validateStructure($value);

        // Encode as JSON for storage
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Validate JSON structure matches required format.
     *
     * @throws \InvalidArgumentException
     */
    protected function validateStructure(mixed $value): void
    {
        if (! is_array($value)) {
            throw new \InvalidArgumentException('Description must be an array or JSON object');
        }

        // Must have 'sections' key
        if (! isset($value['sections']) || ! is_array($value['sections'])) {
            throw new \InvalidArgumentException('Description must contain "sections" array');
        }

        // Sections cannot be empty
        if (empty($value['sections'])) {
            throw new \InvalidArgumentException('Description "sections" array cannot be empty');
        }

        // Validate each section
        foreach ($value['sections'] as $index => $section) {
            $this->validateSection($section, $index);
        }
    }

    /**
     * Validate individual section structure.
     *
     * @throws \InvalidArgumentException
     */
    protected function validateSection(mixed $section, int $index): void
    {
        if (! is_array($section)) {
            throw new \InvalidArgumentException("Section at index {$index} must be an object");
        }

        // Required fields keys (but values can be empty/null)
        $requiredKeys = ['key'];
        foreach ($requiredKeys as $field) {
            if (! isset($section[$field])) {
                throw new \InvalidArgumentException("Section at index {$index} missing required field: {$field}");
            }
        }
        
        // Title and content are optional, but if present must be string or null
        if (isset($section['title']) && ! is_string($section['title']) && ! is_null($section['title'])) {
             throw new \InvalidArgumentException("Section at index {$index} title must be string or null");
        }
        
        if (isset($section['content']) && ! is_string($section['content']) && ! is_null($section['content'])) {
             throw new \InvalidArgumentException("Section at index {$index} content must be string or null");
        }

        // Validate key format (should be slug-like: intro, feature, use, etc.)
        if (! preg_match('/^[a-z_]+$/', $section['key'])) {
            throw new \InvalidArgumentException("Section at index {$index} 'key' must be lowercase alphanumeric with underscores");
        }

        // Validate media if present
        if (isset($section['media'])) {
            $this->validateMedia($section['media'], $index);
        }
    }

    /**
     * Validate media object if present.
     *
     * @throws \InvalidArgumentException
     */
    protected function validateMedia(mixed $media, int $sectionIndex): void
    {
        if ($media === null) {
            return; // null is allowed
        }

        if (! is_array($media)) {
            throw new \InvalidArgumentException("Section at index {$sectionIndex} 'media' must be null or object");
        }

        // Required media fields
        if (! isset($media['type']) || ! isset($media['url'])) {
            throw new \InvalidArgumentException("Section at index {$sectionIndex} media must have 'type' and 'url'");
        }

        // Validate type
        $allowedTypes = ['image', 'video'];
        if (! in_array($media['type'], $allowedTypes)) {
            throw new \InvalidArgumentException("Section at index {$sectionIndex} media 'type' must be 'image' or 'video'");
        }

        // Validate URL is not empty
        if (empty($media['url'])) {
            throw new \InvalidArgumentException("Section at index {$sectionIndex} media 'url' cannot be empty");
        }
    }
}
