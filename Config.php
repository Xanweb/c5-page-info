<?php

namespace Xanweb\PageInfo;

use Concrete\Core\Attribute\Category\PageCategory;
use Concrete\Core\Entity\Attribute\Key\Key as AttributeKey;
use Xanweb\PageInfo\Fetcher\PropertyFetcherInterface;

class Config
{
    /**
     * @var PageCategory
     */
    private $akc;

    /**
     * @var AttributeKey
     */
    private $akNavTarget;

    /**
     * @var AttributeKey
     */
    private $akTags;

    /**
     * @return PropertyFetcherInterface[]
     */
    private $pageNameFetchers = [];

    /**
     * @return PropertyFetcherInterface[]
     */
    private $pageDescriptionFetchers = [];

    /**
     * @return PropertyFetcherInterface[]
     */
    private $thumbnailFetchers = [];

    public function __construct(PageCategory $akc)
    {
        $this->akc = $akc;
    }

    /**
     * Get Nav Target Attribute Key.
     *
     * @return AttributeKey|null
     */
    public function getNavTargetAttributeKey(): ?AttributeKey
    {
        if (!$this->akNavTarget) {
            $this->setNavTargetAttributeKey('nav_target');
        }

        return $this->akNavTarget;
    }

    /**
     * Set Nav Target Attribute Key (defaulted to 'nav_target').
     *
     * @param string $akHandle
     */
    public function setNavTargetAttributeKey(string $akHandle): void
    {
        $this->akNavTarget = $this->akc->getAttributeKeyByHandle($akHandle);
    }

    /**
     * Get Tags attribute key.
     *
     * @return AttributeKey
     */
    public function getTagsAttributeKey(): AttributeKey
    {
        if (!$this->akTags) {
            $this->setTagsAttributeKey('tags');
        }

        return $this->akTags;
    }

    /**
     * Set Tags attribute key (defaulted to 'tags').
     *
     * @param string $akHandle
     *
     * @return Config
     */
    public function setTagsAttributeKey(string $akHandle): self
    {
        $this->akTags = $this->akc->getAttributeKeyByHandle($akHandle);

        return $this;
    }

    /**
     * Register Page Name Fetcher (Call Order is important).
     *
     * @param PropertyFetcherInterface $fetcher
     */
    public function registerPageNameFetcher(PropertyFetcherInterface $fetcher): void
    {
        $this->pageNameFetchers[] = $fetcher;
    }

    /**
     * Register Page Description Fetcher (Call Order is important).
     *
     * @param PropertyFetcherInterface $fetcher
     */
    public function registerPageDescriptionFetcher(PropertyFetcherInterface $fetcher): void
    {
        $this->pageDescriptionFetchers[] = $fetcher;
    }

    /**
     * Register Thumbnail Fetcher (Call Order is important).
     *
     * @param PropertyFetcherInterface $fetcher
     */
    public function registerThumbnailFetcher(PropertyFetcherInterface $fetcher): void
    {
        $this->thumbnailFetchers[] = $fetcher;
    }

    /**
     * @return PropertyFetcherInterface[]
     */
    public function getPageDescriptionFetchers(): array
    {
        return $this->pageDescriptionFetchers;
    }

    /**
     * @return PropertyFetcherInterface[]
     */
    public function getPageNameFetchers(): array
    {
        return $this->pageNameFetchers;
    }

    /**
     * @return PropertyFetcherInterface[]
     */
    public function getThumbnailFetchers(): array
    {
        return $this->thumbnailFetchers;
    }
}
