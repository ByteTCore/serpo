<?php

namespace ByteTCore\Serpo\Traits;

use Illuminate\Cache\RedisStore;
use Illuminate\Cache\TagSet;
use Illuminate\Support\Facades\Cache;

trait Cacheable
{
    protected ?string $pendingCacheKey = null;

    protected ?int $pendingCacheTtl = null;

    /**
     * Mark the next terminal query method for caching.
     *
     * Does not execute the query — only stores the cache configuration.
     * The actual caching happens when a terminal method (get, paginate, first, count, ...)
     * is called afterward.
     *
     * @param string|null $key Custom cache key. If null, auto-generated from table + filters.
     * @param int|null    $ttl Cache lifetime in seconds. If null, uses config default.
     */
    public function cache(?string $key = null, ?int $ttl = null): static
    {
        $this->pendingCacheKey = $key;
        $this->pendingCacheTtl = $ttl;

        return $this;
    }

    /**
     * Delete cached entries matching a custom key prefix.
     *
     * Since the full cache key includes method + params hash, this scans and
     * deletes all entries under the tag namespace that start with the given key.
     */
    public function forget(string $key): bool
    {
        $driver = config('serpo.cache.driver', 'redis');
        $store = Cache::store($driver)->getStore();

        if (! $store instanceof RedisStore) {
            return false;
        }

        $pattern = $this->tagNamespace() . ':' . $key . ':*';
        $redis = $store->connection();

        $cursor = null;
        do {
            [$cursor, $keys] = $redis->scan($cursor ?? '0', ['match' => $pattern, 'count' => 100]);

            if (! empty($keys)) {
                $redis->del($keys);
            }
        } while ($cursor !== '0');

        return true;
    }

    /**
     * Flush all cached queries for this repository's model.
     *
     * Clears every cached entry under the prefix + table tag combination,
     * including both custom-keyed and auto-hashed entries.
     */
    public function flush(): bool
    {
        $driver = config('serpo.cache.driver', 'redis');

        return Cache::store($driver)->tags($this->tags())->flush();
    }

    /**
     * Check the cache for a pending query before execution.
     *
     * Called from DelegatesToBuilder::__call before forwarding to the query builder.
     * Returns the cached value on hit, or a sentinel (false) on miss to distinguish
     * from legitimately null results.
     */
    protected function getCachedResult(string $method, array $parameters): mixed
    {
        if ($this->pendingCacheKey === null && $this->pendingCacheTtl === null) {
            return false;
        }

        if (! config('serpo.cache.enabled', false)) {
            $this->pendingCacheKey = null;
            $this->pendingCacheTtl = null;

            return false;
        }

        $cacheKey = $this->buildCacheKey($this->pendingCacheKey, $method, $parameters);
        $driver = config('serpo.cache.driver', 'redis');

        if (Cache::store($driver)->tags($this->tags())->has($cacheKey)) {
            $this->pendingCacheKey = null;
            $this->pendingCacheTtl = null;

            return Cache::store($driver)->tags($this->tags())->get($cacheKey);
        }

        return false;
    }

    /**
     * Store the query result in cache after execution.
     *
     * Called from DelegatesToBuilder::__call after a terminal method returns.
     */
    protected function putCacheResult(mixed $result, string $method, array $parameters): mixed
    {
        $key = $this->pendingCacheKey;
        $ttl = $this->pendingCacheTtl;

        $this->pendingCacheKey = null;
        $this->pendingCacheTtl = null;

        if ($key === null && $ttl === null) {
            return $result;
        }

        if (! config('serpo.cache.enabled', false)) {
            return $result;
        }

        $cacheKey = $this->buildCacheKey($key, $method, $parameters);
        $ttl ??= (int) config('serpo.cache.ttl', 900);
        $driver = config('serpo.cache.driver', 'redis');

        if ($ttl > 0) {
            Cache::store($driver)->tags($this->tags())->put($cacheKey, $result, $ttl);
        } else {
            Cache::store($driver)->tags($this->tags())->forever($cacheKey, $result);
        }

        return $result;
    }

    /**
     * Build the full cache key including method signature.
     *
     * For paginate methods, the current page from the request is appended to
     * avoid returning the wrong page from cache.
     */
    protected function buildCacheKey(?string $key, string $method, array $parameters): string
    {
        $base = $key ?? hash('sha256', $this->model->getTable() . '|' . json_encode($this->appliedFilters ?? []));
        $signature = $method . ':' . md5(json_encode($parameters));

        if ($method === 'cursorPaginate') {
            $cursor = $parameters[3] ?? null;
            $signature .= ':cursor=' . md5(json_encode($cursor));
        } elseif (in_array($method, ['paginate', 'simplePaginate'])) {
            $signature .= ':page=' . $this->resolvePage($parameters);
        }

        return $base . ':' . $signature;
    }

    /**
     * Get the cache tags for this repository.
     *
     * All entries for a model share the same [prefix, table] tags so that
     * flush() clears everything and forget() targets individual keys.
     */
    protected function tags(): array
    {
        $prefix = config('serpo.cache.prefix', 'serpo');

        return [$prefix, $this->model->getTable()];
    }

    /**
     * Resolve the current page number for paginate cache key.
     *
     * Checks the explicit page parameter first, then falls back to the request
     * using the configured page name.
     */
    protected function resolvePage(array $parameters): string
    {
        if (isset($parameters[3])) {
            return (string) $parameters[3];
        }

        $pageName = $parameters[2] ?? 'page';

        try {
            return (string) request()->input($pageName, '1');
        } catch (\RuntimeException) {
            return '1';
        }
    }

    /**
     * Get the Redis tag namespace for this repository's tag set.
     */
    protected function tagNamespace(): string
    {
        $store = Cache::store(config('serpo.cache.driver', 'redis'))->getStore();

        $tagSet = new TagSet($store, $this->tags());

        return $tagSet->getNamespace();
    }
}
