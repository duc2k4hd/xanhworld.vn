<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait ClearsResponseCache
{
    public static function bootClearsResponseCache()
    {
        static::saved(function ($model) {
            $model->clearCaches();
        });

        static::deleted(function ($model) {
            $model->clearCaches();
        });
    }

    public function clearCaches()
    {
        $keys = $this->responseCacheKeys();

        foreach ($keys as $key) {
            // Support for wildcards or dynamic keys
            Cache::forget($key);
        }
        
        // Log for debugging (optional, can be removed in production)
        Log::info('Cache cleared for model: ' . static::class, ['keys' => $keys]);
    }

    /**
     * Return array of cache keys to clear.
     * Can use dynamic values from $this.
     *
     * @return array
     */
    abstract public function responseCacheKeys(): array;
}
