# Concrete5 Page Info Fetcher
[![Latest Version on Packagist](https://img.shields.io/packagist/v/xanweb/c5-page-info.svg?maxAge=2592000&style=flat-square)](https://packagist.org/packages/xanweb/c5-page-info)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

Useful in page lists when using different templates, it helps to avoid redundant code.

## Installation

Include library to your composer.json
```bash
composer require xanweb/c5-page-info
```

## Simple usage example
```php 
    $pageInfoFactory = new \Xanweb\PageInfo\Factory();
    foreach ($pages as $page):
        $pageInfo = $pageInfoFactory->build($page);
        $pageName = $pageInfo->fetchPageName(); // Page name with htmlentites applied
        $pageDescription = $pageInfo->fetchPageDescription($truncateChars); // $truncateChars: an optional argument can be passed to truncate description
        $formattedPublishDate = $pageInfo->getPublishDate(); // Optionally you can pass format argument ('full', 'long', 'medium' or 'short') or a php custom format 
        $thumbnail = $pageInfo->fetchThumbnail($defaultThumbnail) // By default uses 'thumbnail' attribute
        $linkTag = \HtmlObject\Link::create($pageInfo->getURL(), $pageName, ['target' => $pageInfo->getTarget()]);
    }
```
