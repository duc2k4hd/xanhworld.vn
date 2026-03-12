<?php

namespace App\Helpers;

use App\Models\Category;
use Illuminate\Support\Str;

class CategoryHelper
{
    /**
     * Generate unique slug for category (globally unique, not just within parent level)
     */
    public static function generateUniqueSlugGlobal(string $baseSlug, ?int $excludeId = null): string
    {
        $slug = $baseSlug;
        $counter = 1;

        while (self::slugExistsGlobal($slug, $excludeId)) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Generate unique slug for category (legacy method - checks within parent level)
     *
     * @deprecated Use generateUniqueSlugGlobal instead
     */
    public static function generateUniqueSlug(string $name, ?int $parentId = null, ?int $excludeId = null): string
    {
        $baseSlug = Str::slug($name);

        return self::generateUniqueSlugGlobal($baseSlug, $excludeId);
    }

    /**
     * Check if slug exists globally (not just within parent level)
     */
    public static function slugExistsGlobal(string $slug, ?int $excludeId = null): bool
    {
        $query = Category::where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Check if slug exists in same parent level
     */
    public static function slugExists(string $slug, ?int $parentId, ?int $excludeId = null): bool
    {
        $query = Category::where('slug', $slug)
            ->where('parent_id', $parentId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Build category tree structure (Optimized: Single Query)
     */
    public static function buildTree(?int $parentId = null, bool $includeInactive = false): array
    {
        $allCategories = Category::when(! $includeInactive, fn ($q) => $q->active())
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        return self::buildTreeFromCollection($allCategories, $parentId);
    }

    /**
     * Helper to build tree from a pre-loaded collection
     */
    public static function buildTreeFromCollection($collection, ?int $parentId = null): array
    {
        return $collection->where('parent_id', $parentId)->map(function ($category) use ($collection) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'parent_id' => $category->parent_id,
                'image' => $category->image,
                'order' => $category->order,
                'is_active' => $category->is_active,
                'children' => self::buildTreeFromCollection($collection, $category->id),
            ];
        })->values()->toArray();
    }

    /**
     * Get all descendants of a category (including itself) (Optimized: Single Query)
     */
    public static function getDescendants(int $categoryId): array
    {
        $allCategories = Category::active()->select('id', 'parent_id')->get();
        $descendants = [$categoryId];

        return self::collectDescendantIds($allCategories, $categoryId, $descendants);
    }

    /**
     * Recurse through collection to find descendant IDs
     */
    protected static function collectDescendantIds($collection, $parentId, &$descendants): array
    {
        $children = $collection->where('parent_id', $parentId)->pluck('id')->toArray();

        foreach ($children as $childId) {
            $descendants[] = $childId;
            self::collectDescendantIds($collection, $childId, $descendants);
        }

        return array_values(array_unique($descendants));
    }

    /**
     * Check if category can be moved to new parent (prevent circular reference)
     */
    public static function canMoveToParent(int $categoryId, ?int $newParentId): bool
    {
        if ($newParentId === null) {
            return true;
        }

        // Cannot move to itself
        if ($categoryId === $newParentId) {
            return false;
        }

        // Cannot move to its own descendant
        $descendants = self::getDescendants($categoryId);

        return ! in_array($newParentId, $descendants);
    }

    /**
     * Get breadcrumb path for category
     */
    public static function getBreadcrumb(Category $category): array
    {
        $breadcrumb = [];
        $current = $category;

        while ($current) {
            array_unshift($breadcrumb, [
                'id' => $current->id,
                'name' => $current->name,
                'slug' => $current->slug,
            ]);

            $current = $current->parent;
        }

        return $breadcrumb;
    }

    /**
     * Get category path string (e.g., "Parent > Child > Grandchild")
     */
    public static function getPathString(Category $category): string
    {
        $breadcrumb = self::getBreadcrumb($category);

        return implode(' > ', array_column($breadcrumb, 'name'));
    }

    /**
     * Get all categories for dropdown (flat list with indentation)
     */
    public static function getDropdownOptions(?int $excludeId = null, ?int $parentId = null, int $level = 0): array
    {
        $options = [];

        // Build query for categories at this level
        $query = Category::where('parent_id', $parentId)
            ->orderBy('order')
            ->orderBy('name');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $categories = $query->get();

        foreach ($categories as $category) {
            // Skip if this is the category being excluded
            if ($excludeId && $category->id === $excludeId) {
                continue;
            }

            $prefix = str_repeat('— ', $level);
            $statusIcon = $category->is_active ? '✅' : '❌';
            $options[] = [
                'value' => $category->id,
                'label' => $prefix.$statusIcon.' '.$category->name,
                'category' => $category,
            ];

            // Recursively get children (but exclude the current category to prevent circular reference)
            $children = self::getDropdownOptions($excludeId, $category->id, $level + 1);
            $options = array_merge($options, $children);
        }

        return $options;
    }
}
