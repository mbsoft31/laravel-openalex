<?php

namespace Mbsoft\OpenAlex;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Mbsoft\OpenAlex\Exceptions\OpenAlexException;

class Builder
{
    private array $filters = [];
    private array $sortBy = [];
    private ?string $searchQuery = null;
    private array $select = [];

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

    public function find(string $openAlexId): object
    {
        $url = config('openalex.base_url')."/{$this->entity}/{$openAlexId}";
        $response = $this->httpClient()->get($url);

        if ($response->failed()) {
            throw new OpenAlexException($response->reason(), $response->status());
        }

        return $this->mapToDto($response->json());
    }

    /**
     * @throws ConnectionException
     * @throws OpenAlexException
     */
    public function get(): Collection
    {
        $response = $this->httpClient()->get(
            config('openalex.base_url')."/{$this->entity}",
            $this->buildQueryPayload()
        );

        if ($response->failed()) {
            throw new OpenAlexException($response->reason(), $response->status());
        }

        return collect($response->json('results', []))
            ->map(fn ($item) => $this->mapToDto($item));
    }

    public function __call(string $name, array $arguments): self
    {
        if (str_starts_with($name, 'where')) {
            $filterKey = Str::snake(substr($name, 5));
            $value = $arguments[0];

            return $this->where($filterKey, $value);
        }

        throw new \BadMethodCallException("Method {$name} does not exist.");
    }

    protected function buildQueryPayload(): array
    {
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

    protected function httpClient(): PendingRequest
    {
        $client = Http::acceptJson();

        if ($email = config('openalex.email')) {
            $client->withHeaders(['User-Agent' => "mailto:{$email}"]);
        }

        return $client;
    }

    protected function mapToDto(array $item): object
    {
        $dtoClass = 'Mbsoft\\OpenAlex\\DTOs\\'.Str::studly(Str::singular($this->entity));

        if (class_exists($dtoClass)) {
            return $dtoClass::from($item);
        }

        return (object) $item;
    }
}
