<?php

namespace App\Services;

use Illuminate\Support\Str;

class ProductDescriptionService
{
    /**
     * Create a standardized description structure.
     *
     * @param  array  $data  Array of data
     * @return array
     */
    public static function createDescription(array $data): array
    {
        return [
            'description' => $data['description'] ?? '',
            'instruction' => $data['instruction'] ?? '',
            'specifications' => [
                'height' => $data['specifications']['height'] ?? '',
                'foliage' => $data['specifications']['foliage'] ?? '',
                'light' => $data['specifications']['light'] ?? '',
                'water' => $data['specifications']['water'] ?? '',
                'fengshui' => $data['specifications']['fengshui'] ?? '',
                'position' => $data['specifications']['position'] ?? '',
                'scientific_name' => $data['specifications']['scientific_name'] ?? '',
                'keywords' => $data['specifications']['keywords'] ?? [],
            ],
            'general' => $data['general'] ?? [],
            'highlights' => $data['highlights'] ?? []
        ];
    }

    /**
     * Generate a clean slug for custom keys.
     */
    public static function generateKeySlug(string $name): string
    {
        return Str::snake(Str::slug($name, '_'));
    }

    /**
     * Get a specific field.
     */
    public static function getField(?array $description, string $key, $default = null)
    {
        if (!$description) return $default;
        return $description[$key] ?? $default;
    }

    /**
     * Get a specific specification.
     */
    public static function getSpec(?array $description, string $key, $default = null)
    {
        if (!$description || !isset($description['specifications'])) return $default;
        return $description['specifications'][$key] ?? $default;
    }

    /**
     * Get all specifications.
     */
    public static function getSpecs(?array $description): array
    {
        return $description['specifications'] ?? [];
    }

    /**
     * Convert legacy text description to new JSON format.
     *
     * @param  string|null  $legacyText
     * @return array|null
     */
    public static function migrateFromText(?string $legacyText): ?array
    {
        if (! $legacyText) {
            return null;
        }

        return [
            'description' => $legacyText,
            'instruction' => '',
            'specifications' => [],
        ];
    }

    /**
     * Export description to HTML format.
     *
     * @param  array|null  $description
     * @return string
     */
    public static function toHtml(?array $description): string
    {
        if (! $description) {
            return '';
        }

        return $description['description'] ?? '';
    }

    /**
     * Parse HTML content into structured data (Legacy support or simple parse).
     *
     * @param string|null $html
     * @return array
     */
    public static function parseHtmlToSections(?string $html): array
    {
        return [
            'description' => $html ?? '',
            'instruction' => '',
            'specifications' => [],
        ];
    }
}

