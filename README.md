# **Laravel OpenAlex API Wrapper**

### A fluent, elegant, and modern wrapper for the OpenAlex API, built for Laravel.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mbsoft31/laravel-openalex.svg?style=flat-square)](https://packagist.org/packages/mbsoft31/laravel-openalex)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/mbsoft31/laravel-openalex/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/mbsoft31/laravel-openalex/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/mbsoft31/laravel-openalex/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/mbsoft31/laravel-openalex/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/mbsoft31/laravel-openalex.svg?style=flat-square)](https://packagist.org/packages/mbsoft31/laravel-openalex)


## **Installation**

```bash
composer require mbsoft31/laravel-openalex
```

Next, publish the configuration file:

```bash
php artisan vendor:publish \--provider="Mbsoft\\OpenAlex\\OpenAlexServiceProvider" \--tag="config"
```

It is highly recommended to add your email to [config/openalex.php](config/openalex.php).

## **Usage**

### **Basic Queries**

```php
    use Mbsoft\\OpenAlex\\Facades\\OpenAlex;
    
    // Find a work by its OpenAlex ID  
    $work = OpenAlex::works()->find('W2741809807');
    
    // Find an author by their ORCID  
    $author = OpenAlex::authors()->findByOrcid('0000-0002-1825-0097');
    
    // Get recent, highly-cited works about AI from a specific institution  
    $works = OpenAlex::works()  
        ->whereInstitutionRor('042nb2s44') // MIT  
        ->where('publication_year', '>2024')  
        ->search('artificial intelligence')  
        ->sortBy('cited_by_count')  
        ->get();
```

### **Automatic Pagination**

The `paginate` method returns a standard Laravel `LengthAwarePaginator` instance.

```php
$paginatedWorks = OpenAlex::works()  
    ->whereHas('concepts.id', 'C15744967') // AI  
    ->paginate(perPage: 50, page: 2);

echo "Total AI papers: " . $paginatedWorks->total();
```

### **Cursors for Large Datasets**

Use `cursor()` to iterate over a large result set without consuming much memory. It fetches pages on-demand in the background.

```php
$allWorksFromJournal = OpenAlex::works()  
    ->where('primary_location.source.id', 'S4306520188')  
    ->cursor();

foreach ($allWorksFromJournal as $work) {  
    // Process millions of records safely  
}
```

### **Caching**

Drastically improve performance by caching API responses.

```php
// Cache for 10 minutes  
$works = OpenAlex::works()->where('publication_year', 2025)->cacheFor(600)->get();

// Cache forever  
$source = OpenAlex::sources()->find('S139121223')->cacheForever()->get();
```
### **DTO Utilities**

The returned Data Transfer Objects are equipped with helpful methods.

```php
$work = OpenAlex::works()->find('W2741809807');

// Get plain text abstract  
$abstract = $work->getAbstract();

// Get BibTeX citation  
$bibtex = $work->toBibTeX();  
```
