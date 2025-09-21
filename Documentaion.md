Of course. My apologies for the confusion with the download link. Here is the complete documentation content directly in the chat for you to copy.

-----

# Laravel OpenAlex SDK

\<p align="center"\>
\<img src="[https://i.imgur.com/8e6j4V4.png](https://www.google.com/search?q=https://i.imgur.com/8e6j4V4.png)" width="300" alt="Laravel OpenAlex Logo"\>
\</p\>

## Introduction

Welcome to the Laravel OpenAlex SDK\! This package provides a fluent, expressive, and modern PHP interface for the [OpenAlex API](https://docs.openalex.org/). It's designed to feel right at home in any Laravel application, allowing you to build powerful research tools with clean, readable code.

Instead of manually building URLs and parsing complex JSON, you can write code like this:

```php
use Mbsoft\OpenAlex\Facades\OpenAlex;

$works = OpenAlex::works()
    ->whereInstitutionRor('042nb2s44') // MIT
    ->where('publication_year', '>2022')
    ->search('artificial intelligence')
    ->sortByRelevance()
    ->get();
```

## Installation

You can install the package via Composer:

```bash
composer require mbsoft31/laravel-openalex
```

Next, you should publish the configuration file using the `vendor:publish` Artisan command. This will place a `config/openalex.php` file in your application's config directory.

```bash
php artisan vendor:publish --provider="Mbsoft\OpenAlex\OpenAlexServiceProvider" --tag="config"
```

## Configuration

After publishing the configuration file, open `config/openalex.php`. The most important setting is the `email` option. OpenAlex strongly recommends providing an email address to be added to their "polite pool" of API users, which grants faster and more reliable access.

```php
// config/openalex.php

return [
    /*
    |--------------------------------------------------------------------------
    | OpenAlex API Base URL
    |--------------------------------------------------------------------------
    */
    'base_url' => 'https://api.openalex.org',

    /*
    |--------------------------------------------------------------------------
    | Polite Pool Email Address
    |--------------------------------------------------------------------------
    |
    | OpenAlex is a free service. Providing an email address identifies you
    | as a responsible user and grants you faster, more reliable access.
    |
    */
    'email' => env('OPENALEX_EMAIL', 'your-email@example.com'),
];
```

## Basic Usage

The primary way to use the SDK is through the `OpenAlex` facade. It provides a clean, static-like interface to start your queries. Each OpenAlex entity has a corresponding method:

* `OpenAlex::works()`
* `OpenAlex::authors()`
* `OpenAlex::sources()`
* `OpenAlex::institutions()`
* `OpenAlex::concepts()`
* `OpenAlex::topics()`
* `OpenAlex::publishers()`
* `OpenAlex::funders()`

### Filtering Results

You can filter your queries using a variety of `where` methods.

#### The `where` Method

For basic filtering, you can use the `where` method. The OpenAlex API uses a `key:value` syntax, which the builder handles for you.

```php
// filter=publication_year:2023
$works = OpenAlex::works()->where('publication_year', '2023')->get();

// filter=publication_year:>2022
$works = OpenAlex::works()->where('publication_year', '>2022')->get();
```

#### Magic `where` Methods

For convenience, you can also use "magic" where methods. The builder will automatically convert the camelCase method name to snake\_case for the API filter key.

```php
// filter=publication_year:2023
$works = OpenAlex::works()->wherePublicationYear('2023')->get();

// filter=has_doi:true
$works = OpenAlex::works()->whereHasDoi(true)->get();
```

#### The `whereIn` Method

To filter by multiple values for the same key, OpenAlex uses a pipe (`|`) separator. The `whereIn` method handles this for you.

```php
// filter=type:journal-article|book
$works = OpenAlex::works()->whereIn('type', ['journal-article', 'book'])->get();
```

#### Filtering Relationships with `whereHas`

For relationship filters, OpenAlex uses a dot-syntax key (e.g., `authorships.author.id`). The `whereHas` method provides a more readable way to construct these filters.

```php
// filter=authorships.author.id:A1969244433
$works = OpenAlex::works()->whereHas('authorships.author.id', 'A1969244433')->get();
```

### Full-Text Search

To perform a full-text search across an entity, use the `search` method.

```php
$works = OpenAlex::works()->search('precision agriculture')->get();
```

### Sorting Results

You can sort results using the `sortBy` method. It accepts the field to sort by and an optional direction (`asc` or `desc`). The default is `desc`.

```php
// sort=cited_by_count:asc
$works = OpenAlex::works()->sortBy('cited_by_count', 'asc')->get();
```

For the most common use case, you can use the `sortByRelevance` helper:

```php
// sort=relevance_score:desc
$works = OpenAlex::works()->search('AI')->sortByRelevance()->get();
```

### Selecting Specific Fields

To improve performance and reduce response size, you can specify exactly which fields you want the API to return using the `select` method.

```php
// select=id,display_name,publication_year
$works = OpenAlex::works()->select('id', 'display_name', 'publication_year')->get();
```

### Retrieving Results

Once you have constructed your query, you can retrieve the results using one of several methods.

#### `get()`

The `get` method executes the query and returns an `Illuminate\Support\Collection` of DTOs.

```php
$works = OpenAlex::works()->wherePublicationYear(2025)->get();

foreach ($works as $work) {
    echo $work->display_name;
}
```

#### `first()`

The `first` method returns a single DTO instance for the first result, or `null` if no results are found.

```php
$work = OpenAlex::works()->whereHasDoi(true)->first();
```

#### `find()` and `findBy...()`

To retrieve a single entity by its OpenAlex ID, use the `find` method.

```php
// GET /works/W2741809807
$work = OpenAlex::works()->find('W2741809807');
```

You can also find entities by other unique identifiers, such as a DOI or ORCID.

```php
// GET /works?filter=doi:10.1234/j.test.2023
$work = OpenAlex::works()->findByDoi('10.1234/j.test.2023');

// GET /authors?filter=orcid:0000-0002-1825-0097
$author = OpenAlex::authors()->findByOrcid('0000-0002-1825-0097');
```

The `find` methods will return `null` if the API returns a 404 Not Found response.

#### `toUrl()`

For debugging purposes, you can use the `toUrl` method to get the fully constructed URL that would be sent to the API.

```php
$url = OpenAlex::works()->wherePublicationYear(2025)->toUrl();
// https://api.openalex.org/works?filter=publication_year%3A2025&mailto=...
```

### Pagination

To paginate results, use the `paginate` method. It returns a standard `Illuminate\Pagination\LengthAwarePaginator` instance, which you can use in your views just like you would with Eloquent.

```php
// Controller
$paginatedWorks = OpenAlex::works()
    ->whereHas('concepts.id', 'C15744967') // AI
    ->paginate(perPage: 10, page: request()->get('page', 1));

return view('works.index', ['works' => $paginatedWorks]);

// Blade View
@foreach ($works as $work)
    <p>{{ $work->display_name }}</p>
@endforeach

{{ $works->links() }}
```

### Cursors

When working with very large result sets, the `paginate` or `get` methods might use too much memory. In these situations, use the `cursor` method. A cursor returns a `LazyCollection` that fetches pages from the API on-demand as you iterate over it.

```php
$allWorksFromJournal = OpenAlex::works()
    ->where('primary_location.source.id', 'S4306520188')
    ->cursor();

// This will only keep one page of results in memory at a time.
foreach ($allWorksFromJournal as $work) {
    // Process the work...
}
```

### Caching

To drastically improve the performance of your application, you can cache the results of API queries.

#### `cacheFor()`

Cache a request for a specific duration. You can pass an integer (seconds) or a `DateInterval` instance.

```php
// Cache the result for 10 minutes (600 seconds)
$works = OpenAlex::works()
    ->where('publication_year', 2025)
    ->cacheFor(600)
    ->get();
```

#### `cacheForever()`

If the underlying data is unlikely to change, you can cache it permanently.

```php
$source = OpenAlex::sources()->find('S139121223')->cacheForever()->get();
```

### Data Transfer Objects (DTOs)

All results are returned as strongly-typed Data Transfer Objects (DTOs) using the `spatie/laravel-data` package. This provides a wonderful developer experience with auto-completion in your IDE.

#### DTO Utilities

The `Work` DTO comes with a few helpful utility methods.

**`getAbstract()`**
This method reconstructs the plain-text abstract from the `abstract_inverted_index` provided by the API.

```php
$work = OpenAlex::works()->find('W2741809807');
$abstractText = $work->getAbstract();
// "A new method for determining nucleotide sequences..."
```

**`toBibTeX()`**
This method generates a BibTeX citation string for the work.

```php
$work = OpenAlex::works()->find('W2741809807');
$bibtex = $work->toBibTeX();

/*
@article{Sanger1977,
    author  = "Frederick Sanger and S. Nicklen and Alan Coulson",
    title   = "DNA sequencing with chain-terminating inhibitors",
    journal = "Proceedings of the National Academy of Sciences",
    year    = "1977",
    doi     = "10.1073/pnas.74.12.5463"
}
*/
```

### Error Handling

If the OpenAlex API returns an error (a 4xx or 5xx HTTP status code), the package will throw a `Mbsoft\OpenAlex\Exceptions\OpenAlexException`. You should wrap your API calls in a `try...catch` block to handle these situations gracefully.

```php
use Mbsoft\OpenAlex\Exceptions\OpenAlexException;
use Mbsoft\OpenAlex\Facades\OpenAlex;

try {
    $work = OpenAlex::works()->find('W_INVALID_ID');
} catch (OpenAlexException $e) {
    // Log the error or show a friendly message to the user
    Log::error($e->getMessage());
    abort(500, 'There was a problem communicating with the OpenAlex API.');
}
```

The package automatically retries failed requests (like a 503 or 429) a few times with exponential backoff before throwing the exception.
