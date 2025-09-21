<?php

namespace Mbsoft\OpenAlex;

use BadMethodCallException;
use Carbon\CarbonInterface;
use DateInterval;
use Illuminate\Cache\Repository;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache as CacheFacade;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use Mbsoft\OpenAlex\Exceptions\OpenAlexException;

class Builder
{
    private array $filters = [];
    private array $sortBy = [];
    private ?string $searchQuery = null;
    private array $select = [];
    private DateInterval|Carbon|int|null $cacheTtl = null;
    private bool $cacheForever = false;

    public function __construct(private string $entity)
    {
    }

    public function where(string $key, string $value): self
    {
        $this->filters[] = "{$key}:{$value}";

        return $this;
    }

    public function whereIn(string $key, array $values): self
    {
        $this->filters[] = "{$key}:".implode('|', $values);

        return $this;
    }

    public function search(string $query): self
    {
        $this->searchQuery = $query;

        return $this;
    }

    public function sortBy(string $key, string $direction = 'desc'): self
    {
        $this->sortBy[$key] = $direction;

        return $this;
    }

    public function sortByRelevance(): self
    {
        return $this->sortBy('relevance_score');
    }

    public function select(array|string $fields): self
    {
        $this->select = is_array($fields) ? $fields : func_get_args();

        return $this;
    }

    public function find(string $openAlexId): ?object
    {
        $url = config('openalex.base_url')."/{$this->entity}/{$openAlexId}";

        $callback = fn() => $this->makeRequest($url);

        $response = $this->executeCacheable($url, $callback);

        if (is_null($response)) {
            return null;
        }

        return $this->mapToDto($response);
    }

    /**
     * @throws OpenAlexException
     */
    public function get(): Collection
    {
        $url = $this->buildUrl();
        $callback = fn() => $this->makeRequest($url, $this->buildQueryPayload());
        $response = $this->executeCacheable($url, $callback);

        return collect($response['results'] ?? [])
            ->map(fn ($item) => $this->mapToDto($item));
    }

    // Filter by relationship
    public function whereHas(string $relation, string $value): self
    {
        $this->filters[] = "{$relation}:{$value}";

        return $this;
    }

    // Find a single entity by a specific identifier (e.g., DOI, ROR, ORCID)
    public function findBy(string $idType, string $idValue): ?object
    {
        $url = config('openalex.base_url')."/{$this->entity}/{$idType}:{$idValue}";

        /**
         * @throws OpenAlexException
         */
        $callback = fn() => $this->makeRequest($url);

        $response = $this->executeCacheable($url, $callback);

        if (is_null($response)) {
            return null;
        }

        return $this->mapToDto($response);
    }

    // Shortcut for findBy('doi', $doi)
    public function findByDoi(string $doi): ?object
    {
        return $this->findBy('doi', $doi);
    }

    // Shortcut for findBy('orcid', $orcid)
    public function findByOrcid(string $orcid): ?object
    {
        return $this->findBy('orcid', $orcid);
    }

    // Caching methods
    public function cacheFor(DateInterval|int $ttl): self
    {
        $this->cacheTtl = is_int($ttl) ? now()->addSeconds($ttl) : $ttl;

        return $this;
    }

    public function cacheForever(): self
    {
        $this->cacheForever = true;

        return $this;
    }

    public function disableCache(): self
    {
        $this->cacheTtl = null;
        $this->cacheForever = false;

        return $this;
    }

    // Automatic Pagination
    public function paginate(int $perPage = 25, int $page = 1): LengthAwarePaginator
    {
        $payload = array_merge($this->buildQueryPayload(), [
            'per-page' => $perPage,
            'page' => $page,
        ]);

        $url = $this->buildUrl($payload);
        /**
         * @throws OpenAlexException
         */
        $callback = fn() => $this->makeRequest($url, $payload);
        $response = $this->executeCacheable($url, $callback);

        $items = collect($response['results'] ?? [])
            ->map(fn ($item) => $this->mapToDto($item));

        $total = $response['meta']['count'] ?? 0;

        return new LengthAwarePaginator($items, $total, $perPage, $page);
    }

    // Memory-efficient cursor for large result sets
    public function cursor(): LazyCollection
    {
        return new LazyCollection(function () {
            $page = 1;
            $perPage = 200; // Max per-page limit for cursor

            do {
                $payload = array_merge($this->buildQueryPayload(), [
                    'per-page' => $perPage,
                    'page' => $page,
                ]);

                $response = $this->makeRequest(config('openalex.base_url')."/{$this->entity}", $payload);
                $results = $response['results'] ?? [];

                foreach ($results as $result) {
                    yield $this->mapToDto($result);
                }

                $page++;
            } while (! empty($results));
        });
    }

    // ... (buildQueryPayload, httpClient, mapToDto methods are updated slightly) ...
    public function __call(string $name, array $arguments): self {
        if (str_starts_with($name, 'where')) {
            $filterKey = Str::snake(substr($name, 5));
            $value = $arguments[0]; return $this->where($filterKey, $value);
        }

        throw new BadMethodCallException("Method {$name} does not exist.");
    }

    protected function buildQueryPayload(): array {
        $queryParams = [];

        if (! empty($this->filters)) {
            $queryParams['filter'] = implode(',', $this->filters);
        }

        if ($this->searchQuery) {
            $queryParams['search'] = $this->searchQuery;
        }

        if (! empty($this->sortBy)) {
            $sortParams = [];

            foreach ($this->sortBy as $key => $direction) {
                $sortParams[] = "{$key}:{$direction}";
            }

            $queryParams['sort'] = implode(',', $sortParams);
        }

        if (! empty($this->select)) {
            $queryParams['select'] = implode(',', $this->select);
        }

        return $queryParams;
    }

    protected function mapToDto(array $item): object {
        $dtoClass = 'Mbsoft\\OpenAlex\\DTOs\\'.Str::studly(Str::singular($this->entity));

        if (class_exists($dtoClass)) {
            return $dtoClass::from($item);
        }

        return (object) $item;
    }

    // Now uses makeRequest
    public function toUrl(): string
    {
        return $this->buildUrl($this->buildQueryPayload());
    }

    private function buildUrl(array $payload = []): string
    {
        $payload = empty($payload) ? $this->buildQueryPayload() : $payload;
        return config('openalex.base_url')."/{$this->entity}?".http_build_query($payload);
    }

    // Centralized request method with retry logic

    /**
     * @throws ConnectionException
     * @throws OpenAlexException
     */
    protected function makeRequest(string $url, array $payload = []): ?array
    {
        $response = $this->httpClient()
            ->retry(3, 100, throw: false) // Retry 3 times, with 100ms initial delay
            ->get($url, $payload);

        if ($response->status() === 404) {
            return null; // For find() methods, 404 is not an exception
        }

        if ($response->failed()) {
            throw new OpenAlexException($response->reason(), $response->status());
        }

        return $response->json();
    }

    // Cache handling logic
    protected function executeCacheable(string $url, callable $callback): ?array
    {
        if (is_null($this->cacheTtl) && ! $this->cacheForever) {
            return $callback();
        }

        $cacheKey = 'openalex_'.sha1($url);
        $cache = $this->cache();

        if ($this->cacheForever) {
            return $cache->rememberForever($cacheKey, $callback);
        }

        return $cache->remember($cacheKey, $this->cacheTtl, $callback);
    }

    protected function httpClient(): PendingRequest {
        $client = Http::acceptJson();

        if ($email = config('openalex.email')) {
            $client->withHeaders(['User-Agent' => "mailto:{$email}"]);
        }

        return $client;
    }

    /**
     * Get the cache repository instance.
     */
    protected function cache(): \Illuminate\Contracts\Cache\Repository|Repository
    {
        return CacheFacade::store();
    }
}
