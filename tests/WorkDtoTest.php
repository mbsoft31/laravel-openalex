<?php

use Mbsoft\OpenAlex\DTOs\Work;
use Mbsoft\OpenAlex\Tests\Fixtures\WorkFixture;

test('it can reconstruct an abstract from the inverted index', function () {
    // Arrange: Get a mock API response for a work
    $work = Work::from(WorkFixture::get());

    // Act: Call the getAbstract method
    $abstract = $work->getAbstract();

    // Assert: Check if the abstract is correctly reconstructed
    expect($abstract)->toBe('This is a test abstract.');
});

test('get abstract returns null when the inverted index is null', function () {
    // Arrange: Create a DTO with a null abstract
    $workData = WorkFixture::get();
    $workData['abstract_inverted_index'] = null;
    $work = Work::from($workData);

    // Assert: The method should gracefully return null
    expect($work->getAbstract())->toBeNull();
});

test('it can generate a bibtex citation', function () {
    // Arrange
    $work = Work::from(WorkFixture::get());

    // Act
    $bibtex = $work->toBibTeX();

    // Assert: Check for key components in the output string
    expect($bibtex)
        ->toContain('@article{Doe2023,')
        ->toContain('author  = "John Doe and Jane Smith"')
        ->toContain('title   = "A Test Publication"')
        ->toContain('journal = "Journal of Testing"')
        ->toContain('year    = "2023"')
        ->toContain('doi     = "10.1234/j.test.2023"');
});

test('bibtex handles works with no authors', function () {
    // Arrange: Create a DTO with an empty authors collection
    $workData = WorkFixture::get();
    $workData['authorships'] = [];
    $work = Work::from($workData);

    // Act
    $bibtex = $work->toBibTeX();

    // Assert: It should use a sensible fallback for the citation key
    expect($bibtex)
        ->toContain('@article{Unknown2023,')
        ->toContain('author  = ""');
});

