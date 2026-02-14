<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductDescriptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'description' => 'required|array',
            'description.sections' => 'required|array|min:1',
            
            // Validate each section
            'description.sections.*.key' => 'required|string|regex:/^[a-z_]+$/',
            'description.sections.*.title' => 'required|string|min:1|max:255',
            'description.sections.*.content' => 'required|string|min:1',
            
            // Media validation (optional, can be null)
            'description.sections.*.media' => 'nullable|array',
            'description.sections.*.media.type' => 'required_with:description.sections.*.media|in:image,video',
            'description.sections.*.media.url' => 'required_with:description.sections.*.media|url|min:1',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'description.required' => 'Product description is required.',
            'description.array' => 'Product description must be a valid JSON object.',
            'description.sections.required' => 'Description must contain sections array.',
            'description.sections.array' => 'Sections must be an array.',
            'description.sections.min' => 'Description must have at least one section.',
            
            'description.sections.*.key.required' => 'Each section must have a key.',
            'description.sections.*.key.regex' => 'Section key must be lowercase with underscores only.',
            'description.sections.*.title.required' => 'Each section must have a title.',
            'description.sections.*.title.min' => 'Section title cannot be empty.',
            'description.sections.*.content.required' => 'Each section must have content.',
            'description.sections.*.content.min' => 'Section content cannot be empty.',
            
            'description.sections.*.media.media.array' => 'Media must be null or a valid object.',
            'description.sections.*.media.type.required_with' => 'Media type is required when media is provided.',
            'description.sections.*.media.type.in' => 'Media type must be either "image" or "video".',
            'description.sections.*.media.url.required_with' => 'Media URL is required when media is provided.',
            'description.sections.*.media.url.url' => 'Media URL must be a valid URL.',
        ];
    }
}
