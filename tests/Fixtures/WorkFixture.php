<?php

namespace Mbsoft\OpenAlex\Tests\Fixtures;

class WorkFixture
{
    public static function get(string $id = 'W123', string $displayName = 'A Test Publication'): array
    {
        return [
            'id' => 'https://openalex.org/'.$id,
            'doi' => 'https://doi.org/10.1234/j.test.2023',
            'display_name' => $displayName,
            'publication_year' => 2023,
            'type' => 'journal-article',
            'cited_by_count' => 10,
            'authorships' => [
                ['author' => ['id' => 'A1', 'display_name' => 'John Doe', 'orcid' => null], 'institutions' => []],
                ['author' => ['id' => 'A2', 'display_name' => 'Jane Smith', 'orcid' => null], 'institutions' => []],
            ],
            // FIX: The structure now perfectly matches the real API and our DTOs.
            'primary_location' => [
                'is_oa' => false,
                'landing_page_url' => 'https://example.com',
                'pdf_url' => null,
                'license' => null,
                'version' => 'publishedVersion',
                'source' => [
                    'id' => 'S1',
                    'display_name' => 'Journal of Testing',
                    'issn_l' => '1234-5678',
                    'publisher' => 'Test Publisher',
                    'type' => 'journal',
                    'homepage_url' => 'https://example.com',
                    'works_count' => 100,
                    'cited_by_count' => 5000,
                ],
            ],
            'topics' => [],
            'referenced_works' => [],
            'abstract_inverted_index' => [
                'This' => [0],
                'is' => [1],
                'a' => [2],
                'test' => [3],
                'abstract.' => [4],
            ],
        ];
    }
}
