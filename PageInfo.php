<?php

namespace Xanweb\PageInfo;

use Concrete\Core\Entity\File\File;
use Concrete\Core\Localization\Service\Date;
use Concrete\Core\Page\Page;
use Concrete\Core\Url\Resolver\PageUrlResolver;
use Concrete\Core\Utility\Service\Text;
use League\URL\URLInterface;

class PageInfo
{
    /**
     * @var Page
     */
    private $page;

    /**
     * @var PageUrlResolver
     */
    private $urlResolver;

    /**
     * @var Text
     */
    private $th;

    /**
     * @var Date
     */
    private $dh;

    /**
     * @var Config
     */
    private $config;

    public function __construct(Page $page, PageUrlResolver $urlResolver, Text $th, Date $dh, Config $config)
    {
        $this->page = $page;
        $this->th = $th;
        $this->dh = $dh;
        $this->urlResolver = $urlResolver;
        $this->config = $config;
    }

    /**
     * Get Page Name after applying htmlentities().
     *
     * @return string
     */
    public function fetchPageName(): string
    {
        $pageName = '';
        foreach ($this->config->getPageNameFetchers() as $fetcher) {
            $pageName = $fetcher->fetch($this->page);
            if (!empty($pageName)) {
                break;
            }
        }

        return $this->th->entities($pageName);
    }

    /**
     * Get Page Description.
     *
     * @param int|null $truncateChars
     *
     * @return string
     */
    public function fetchPageDescription(?int $truncateChars = null): string
    {
        $description = '';
        foreach ($this->config->getPageDescriptionFetchers() as $fetcher) {
            $description = $fetcher->fetch($this->page);
            if (!empty($description)) {
                break;
            }
        }

        return $truncateChars ? $this->th->shortenTextWord($description, $truncateChars) : $description;
    }

    /**
     * Get Page URL.
     *
     * @return URLInterface
     */
    public function getURL(): ?URLInterface
    {
        return $this->urlResolver->resolve([$this->page]);
    }

    /**
     * Get Navigation Target.
     *
     * @return string
     */
    public function getTarget(): string
    {
        $akNavTarget = $this->config->getNavTargetAttributeKey();
        $target = ($this->page->getCollectionPointerExternalLink() !== '' && $this->page->openCollectionPointerExternalLinkInNewWindow()) ? '_blank' : $this->page->getAttribute($akNavTarget);

        return empty($target) ? '_self' : $target;
    }

    /**
     * Get Publish Date.
     *
     * @param string|null $format can be 'full', 'long', 'medium' or 'short' (see \Localization\Service\Date::formatDate()#$format)
     * or a custom format (see http://www.php.net/manual/en/function.date.php for applicable formats)
     *
     * @return string
     * @noinspection PhpDocMissingThrowsInspection
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function getPublishDate(?string $format = null): string
    {
        $datePublic = $this->page->getCollectionDatePublic();
        if ($format === null || in_array($format, ['full', 'long', 'medium', 'short'])) {
            return $this->dh->formatDate($datePublic, $format ?? 'short');
        }

        return $this->dh->formatCustom($format, $datePublic);
    }

    /**
     * Get Publish DateTime.
     *
     * @param bool $longDate Set to true for the long date format (eg 'December 31, 2000 at ...'), false (default) for the short format (eg '12/31/2000 at ...')
     * @param bool $withSeconds Set to true to include seconds (eg '... at 11:59:59 PM'), false (default) otherwise (eg '... at 11:59 PM');
     *
     * @return string
     * @noinspection PhpDocMissingThrowsInspection
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function getPublishDateTime(bool $longDate = false, bool $withSeconds = false): string
    {
        $datePublic = $this->page->getCollectionDatePublic();

        return $this->dh->formatDateTime($datePublic, $longDate, $withSeconds);
    }

    /**
     * Get Main Page Thumbnail.
     *
     * @param File|null $fallbackThumbnail
     * @return File|null
     */
    public function fetchThumbnail(?File $fallbackThumbnail = null): ?File
    {
        $thumbnail = null;
        foreach ($this->config->getThumbnailFetchers() as $fetcher) {
            $thumbnail = $fetcher->fetch($this->page);
            if ($thumbnail !== null) {
                break;
            }
        }

        return $thumbnail ?? $fallbackThumbnail;
    }
}