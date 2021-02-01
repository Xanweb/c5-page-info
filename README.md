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
$pageInfoFactory = new Xanweb\PageInfo\Factory($myConfig); // We can pass our own config (Check `Config Management` section), otherwise default config will be used.
foreach ($pages as $page):
    $pageInfo = $pageInfoFactory->build($page);
    $pageName = $pageInfo->fetchPageName(); // Page name with htmlentites applied
    $pageDescription = $pageInfo->fetchPageDescription($truncateChars); // $truncateChars: an optional argument can be passed to truncate description
    $formattedPublishDate = $pageInfo->getPublishDate($format); // Optionally you can pass format argument ('full', 'long', 'medium' or 'short') or a php custom format 
    $thumbnail = $pageInfo->fetchThumbnail($defaultThumbnail); // By default uses 'thumbnail' attribute.
    $linkTag = \HtmlObject\Link::create($pageInfo->getURL(), $pageName, ['target' => $pageInfo->getTarget()]);
}
```

## Config Management
You can register your own config to fetch page information
```php
use Xanweb\PageInfo\Fetcher\AttributePropertyFetcher;
use Xanweb\PageInfo\Fetcher\BlockPropertyFetcher;
use Xanweb\PageInfo\Fetcher\PagePropertyFetcher;

// Order of registering fetchers is important.
// The first registered will be firstly fetched. 
$config = $app->make(Xanweb\PageInfo\Config::class);

// if display_name attribute is filled for the page then it will be used otherwise the page name will be used
$config->registerPageNameFetcher(new AttributePropertyFetcher('display_name'));  
$config->registerPageNameFetcher(new PagePropertyFetcher(PagePropertyFetcher::PAGE_NAME));
$config->registerPageDescriptionFetcher(new PagePropertyFetcher(PagePropertyFetcher::PAGE_DESCRIPTION));

// Fetch thumbnail from a custom attribute
$config->registerThumbnailFetcher(new AttributePropertyFetcher('my_thumbnail_ak'));
// Fetch thumbnail from a block within the page. (requires installing "xanweb/c5-helpers" library)
$config->registerThumbnailFetcher(new BlockPropertyFetcher(
    'image', // Block Type handle 
    function ($bController) { // Method will be called to return thumbnail file if the block is found.
        return $bController->getFileObject();
    },
    // In case we have more than a block in the page, we may need to refine the fetching by making some checks
    // for the found block 
    function ($bController) { 
        return $bController->getFileObject() !== null;
    },
    // More refining also can be done by excluding some areas from fetching, example:
    ['Right Sidebar', 'Left Sidebar', 'Footer']
    )
);

$cfgManager = Xanweb\PageInfo\ConfigManager::get();
$cfgManager->register('my_cfg_key', $config);

$myConfig = $cfgManager->getConfig('my_cfg_key');
```

## Predefined Configs
1. *DEFAULT:* <br>
   * <b>Page Name:</b> [Page Name Property]
   * <b>Page Description:</b> [Page Description Property]
   * <b>Thumbnail:</b> ['thumbnail' attribute]
2. *BASIC:* <br>
    * <b>Page Name:</b> [Page Name Property]
    * <b>Page Description:</b> [Page Description Property]
    * <b>Thumbnail:</b> ['thumbnail' attribute, Image Block]    
3. *ADVANCED:* <br>
    * <b>Page Name:</b> [Page Title Block, Page Name Property]
    * <b>Page Description:</b> [Page Description Property]
    * <b>Thumbnail:</b> ['thumbnail' attribute, Image Block]    
*If 'page_heading' block (Custom block by Xanweb) is installed, then it will be:*
   * <b>Page Name:</b> [Page Heading Block, Page Name Property]
   * <b>Page Description:</b> [Page Heading Block, Page Description Property]
   * <b>Thumbnail:</b> ['thumbnail' attribute, Image Block]   
   
Example of using predefined config:
```php
$myConfig = Xanweb\PageInfo\ConfigManager::getBasic();
$pageInfoFactory = new Xanweb\PageInfo\Factory($myConfig);
```
