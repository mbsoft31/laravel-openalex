<?php

use Illuminate\Http\Client\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\LazyCollection;
use Mbsoft\OpenAlex\DTOs\Work;
use Mbsoft\OpenAlex\Exceptions\OpenAlexException;
use Mbsoft\OpenAlex\Facades\OpenAlex;
use Mbsoft\OpenAlex\Tests\Fixtures\WorkFixture;

// Hook to prevent stray HTTP requests before each test in this file
beforeEach(function () {
    Http::preventStrayRequests();
});

test('it builds a full query url correctly', function () {
    $url = OpenAlex::works()
        ->where('type', 'journal-article')
        ->whereIn('language', ['en', 'de'])
        ->whereHas('concepts.id', 'C123')
        ->search('machine learning')
        ->sortBy('publication_year', 'asc')
        ->select('id', 'display_name')
        ->toUrl();

    // Decode the URL before asserting to handle encoded characters
    $decodedUrl = urldecode($url);

    expect($decodedUrl)
        ->toContain('filter=type:journal-article,language:en|de,concepts.id:C123')
        ->toContain('search=machine learning')
        ->toContain('sort=publication_year:asc')
        ->toContain('select=id,display_name');
});

test('magic where method builds correct filter', function () {
    $url = OpenAlex::works()->wherePublicationYear(2022)->toUrl();

    // Decode the URL before asserting
    $decodedUrl = urldecode($url);
    expect($decodedUrl)->toContain('filter=publication_year:2022');
});

test('get method returns a collection of dtos', function () {
    Http::fake(['*' => Http::response(['results' => [WorkFixture::get('W1'), WorkFixture::get('W2')]])]);

    $works = OpenAlex::works()->get();

    expect($works)->toBeInstanceOf(Collection::class)
        ->toHaveCount(2)
        ->and($works->first())->toBeInstanceOf(Work::class);
});

test('find method returns a single dto', function () {
    Http::fake(['https://api.openalex.org/works/W123' => Http::response(WorkFixture::get('W123'))]);

    $work = OpenAlex::works()->find('W123');

    expect($work)->toBeInstanceOf(Work::class)
        ->id->toBe('https://openalex.org/W123');
});

test('find returns null on 404', function () {
    Http::fake(['*' => Http::response(null, 404)]);
    $work = OpenAlex::works()->find('W-NONEXISTENT');
    expect($work)->toBeNull();
});

test('find by doi builds correct url', function () {
    Http::fake(['*' => Http::response(WorkFixture::get())]);
    OpenAlex::works()->findByDoi('10.123/test');

    Http::assertSent(function (Request $request) {
        return $request->url() === 'https://api.openalex.org/works/doi:10.123/test';
    });
});

test('it throws exception on http error', function () {
    Http::fake(['*' => Http::response('Server Error', 500)]);
    OpenAlex::works()->get();
})->throws(OpenAlexException::class);

test('it paginates results', function () {
    $response = [
        'meta' => ['count' => 100, 'per_page' => 2],
        'results' => [WorkFixture::get('W1'), WorkFixture::get('W2')],
    ];
    Http::fake(['*' => Http::response($response)]);

    $paginator = OpenAlex::works()->paginate(perPage: 2, page: 3);

    expect($paginator)->toBeInstanceOf(LengthAwarePaginator::class)
        ->total()->toBe(100)
        ->currentPage()->toBe(3)
        ->and($paginator->items())->toHaveCount(2);

    Http::assertSent(function (Request $request) {
        return $request['per-page'] == 2 && $request['page'] == 3;
    });
});

test('cursor iterates through all pages', function () {
    Http::fakeSequence()
        ->push(['meta' => ['count' => 4], 'results' => [WorkFixture::get('W1'), WorkFixture::get('W2')]])
        ->push(['meta' => ['count' => 4], 'results' => [WorkFixture::get('W3'), WorkFixture::get('W4')]])
        ->push(['results' => []]); // Last page is empty

    $cursor = OpenAlex::works()->cursor();
    expect($cursor)->toBeInstanceOf(LazyCollection::class);

    $results = $cursor->collect();

    expect($results)->toHaveCount(4)
        ->and($results->last()->id)->toBe('https://openalex.org/W4');

    Http::assertSentCount(3);
});

test('it caches requests with cache for', function () {
    Http::fake(['*' => Http::response(['results' => [WorkFixture::get()]])]);

    // First call - should hit the API and cache the result
    $works1 = OpenAlex::works()->where('test', '1')->cacheFor(60)->get();

    // Second call - should get data from cache, not the API
    $works2 = OpenAlex::works()->where('test', '1')->cacheFor(60)->get();

    expect($works1)->toEqual($works2);
    Http::assertSentCount(1);
});

test('it caches requests with cache forever', function () {
    Http::fake(['*' => Http::response(['results' => [WorkFixture::get()]])]);

    OpenAlex::works()->where('test', 'forever')->cacheForever()->get();
    OpenAlex::works()->where('test', 'forever')->cacheForever()->get();

    Http::assertSentCount(1);
});

test('cache is unique per query', function () {
    Http::fake(['*' => Http::response(['results' => [WorkFixture::get()]])]);

    OpenAlex::works()->where('param', 'A')->cacheFor(60)->get();
    OpenAlex::works()->where('param', 'B')->cacheFor(60)->get();

    Http::assertSentCount(2);
});

test('it retries failed requests', function () {
    Http::fakeSequence()
        ->push('Server Error', 503) // Fail once
        ->push(['results' => [WorkFixture::get()]]); // Succeed on second attempt

    $works = OpenAlex::works()->get();

    expect($works)->toHaveCount(1);
    Http::assertSentCount(2); // Should have sent two requests
});
