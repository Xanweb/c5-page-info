<?php

namespace Xanweb\PageInfo;

use Concrete\Core\Entity\File\File;
use Concrete\Core\Localization\Service\Date;
use Concrete\Core\Page\Page;
use Concrete\Core\Support\Facade\UserInfo;
use Concrete\Core\Url\Resolver\PageUrlResolver;
use Concrete\Core\Utility\Service\Text;
use League\URL\URLInterface;

class PageInfo
{
    /**
     * @var Page
     */
    protected $page;

    /**
     * @var PageUrlResolver
     */
    protected $urlResolver;

    /**
     * @var Text
     */
    protected $th;

    /**
     * @var Date
     */
    protected $dh;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var URLInterface
     */
    private $url;

    /**
     * @var \Concrete\Core\User\UserInfo
     */
    private $pageAuthor;

    /**
     * @var \Concrete\Core\User\UserInfo
     */
    private $lastEditor;

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
     * @param int|null $truncateChars
     * @param string $tail
     *
     * @return string
     */
    public function fetchPageName(?int $truncateChars = null, string $tail = '…'): string
    {
        $pageName = '';
        foreach ($this->config->getPageNameFetchers() as $fetcher) {
            $pageName = (string) $fetcher->fetch($this->page);
            if (!empty($pageName)) {
                $pageName = $this->th->entities($pageName);
                if ($truncateChars) {
                    $pageName = $this->th->shortenTextWord($pageName, $truncateChars, $tail);
                }

                break;
            }
        }

        return $pageName;
    }

    /**
     * Get Page Description.
     *
     * @param int|null $truncateChars
     * @param string $tail
     *
     * @return string
     */
    public function fetchPageDescription(?int $truncateChars = null, string $tail = '…'): string
    {
        $description = '';
        foreach ($this->config->getPageDescriptionFetchers() as $fetcher) {
            $description = (string) $fetcher->fetch($this->page);
            if (!empty($description)) {
                break;
            }
        }

        return $truncateChars ? $this->th->shortenTextWord($description, $truncateChars, $tail) : $description;
    }

    /**
     * Get Page Author.
     *
     * @return \Concrete\Core\User\UserInfo|null
     */
    public function getAuthor(): ?\Concrete\Core\User\UserInfo
    {
        return $this->pageAuthor ?? $this->pageAuthor = UserInfo::getByID((int) $this->page->getCollectionUserID());
    }

    /**
     * Get Latest Version Author.
     *
     * @return \Concrete\Core\User\UserInfo|null
     */
    public function getLastEditor(): ?\Concrete\Core\User\UserInfo
    {
        if (!isset($this->lastEditor)) {
            $cvAuthorID = (int) $this->page->getVersionObject()->getVersionAuthorUserID();
            $this->lastEditor = UserInfo::getByID($cvAuthorID);
        }

        return $this->lastEditor;
    }

    /**
     * Get Latest Version Author Name.
     *
     * @return string
     */
    public function getLastEditorUserName(): string
    {
        $ui = $this->getLastEditor();

        return $ui !== null ? $ui->getUserDisplayName() : '';
    }

    /**
     * Get Page URL.
     *
     * @return URLInterface
     */
    public function getURL(): ?URLInterface
    {
        return $this->url ?? $this->url = $this->urlResolver->resolve([$this->page]);
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
     * Get Page Tags.
     *
     * @return \Concrete\Core\Entity\Attribute\Value\Value\SelectValueOption[]
     */
    public function getTags(): array
    {
        /** @var \Concrete\Core\Entity\Attribute\Value\Value\SelectValue $tags */
        $tags = $this->page->getAttribute($this->config->getTagsAttributeKey());

        return $tags !== null ? $tags->getSelectedOptions()->toArray() : [];
    }

    /**
     * Get Main Page Thumbnail.
     *
     * @param File|null $fallbackThumbnail
     *
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
