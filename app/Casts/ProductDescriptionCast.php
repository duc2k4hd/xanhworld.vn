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

        // If valid JSON and has the new structure, return it
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && (isset($decoded['description']) || isset($decoded['instruction']) || isset($decoded['specifications']))) {
            return array_merge([
                'description' => '',
                'instruction' => '',
                'specifications' => [],
                'general' => [],
                'highlights' => []
            ], $decoded);
        }

        // BACKWARD COMPATIBILITY:
        // If it's the old 'sections' structure, map it to the new one
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && isset($decoded['sections'])) {
            $description = '';
            $instruction = '';
            $specs = [];

            foreach ($decoded['sections'] as $section) {
                $key = $section['key'] ?? '';
                $content = $section['content'] ?? '';
                
                if ($key === 'care' || $key === 'instruction') {
                    $instruction .= $content;
                } else {
                    $description .= $content;
                }
            }

            return [
                'description' => $description,
                'instruction' => $instruction,
                'specifications' => $specs,
                'general' => [],
                'highlights' => []
            ];
        }

        // If value is not valid JSON or unknown, treat as raw description
        return [
            'description' => $value,
            'instruction' => '',
            'specifications' => [],
            'general' => [],
            'highlights' => []
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

        // Cleanup empty specifications
        if (is_array($value) && isset($value['specifications']) && is_array($value['specifications'])) {
            $value['specifications'] = array_filter($value['specifications'], function($val) {
                return !is_null($val) && $val !== '';
            });
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

        // We expect at least one of these keys or an empty array
        $validKeys = ['description', 'instruction', 'specifications', 'general', 'highlights'];
        foreach ($value as $key => $v) {
            if (!in_array($key, $validKeys)) {
                // We allow it but maybe log it? For now just keep what's defined.
            }
        }
    }
}
