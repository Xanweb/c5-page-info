<?php

namespace Xanweb\PageInfo\Fetcher;

use Concrete\Core\Page\Page;
use Xanweb\Helper\Page as PageHelper;

/**
 * Class BlockPropertyFetcher
 * Requires xanweb/c5-helpers library.
 */
class BlockPropertyFetcher extends AbstractPropertyFetcher
{
    /**
     * List of area handles that will be excluded from fetching.
     *
     * @var array|null
     */
    private $excludeAreas;

    /**
     * List of area handles that will be included in fetching.
     *
     * @var array
     */
    private $includeAreas;

    /**
     * Function to validate fetched block.
     *
     * @var callable|null
     */
    private $dataValidator;

    /**
     * BlockPropertyFetcher constructor.
     *
     * @param string $btHandle block type handle
     * @param callable $fetchCallback function(BlockController $bcController)
     * @param callable|null $dataValidator myFunction(BlockController $bController): bool
     * @param array|null $excludeAreas List of area handles that will be excluded from fetching.
     * @param array $includeAreas List of area handles that will be included in fetching.
     */
    public function __construct(string $btHandle, callable $fetchCallback, ?callable $dataValidator = null, ?array $excludeAreas = null, array $includeAreas = [])
    {
        if (!class_exists(PageHelper::class)) {
            throw new \RuntimeException('Please install xanweb/c5-helpers to use BlockPropertyFetcher');
        }

        $this->handle = $btHandle;
        $this->fetchCallback = $fetchCallback;
        $this->excludeAreas = $excludeAreas;
        $this->dataValidator = $dataValidator;
        $this->includeAreas = $includeAreas;
    }

    /**
     * Fetch block from page.
     *
     * @param Page $page
     *
     * @return mixed|null
     */
    public function fetch(Page $page)
    {
        $block = (new PageHelper($page, $this->excludeAreas, $this->includeAreas))->getBlock($this->getHandle(), $this->dataValidator);

        return $block !== null ? ($this->fetchCallback)($block) : null;
    }
}
