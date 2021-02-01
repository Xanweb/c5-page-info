<?php

namespace Xanweb\PageInfo;

use Concrete\Core\Page\Page;
use Concrete\Core\Url\Resolver\PageUrlResolver;
use Xanweb\Common\Traits\ApplicationTrait;

class Factory
{
    use ApplicationTrait;

    /**
     * @var PageUrlResolver
     */
    private $urlResolver;

    /**
     * @var \Concrete\Core\Localization\Service\Date
     */
    private $dh;

    /**
     * @var \Concrete\Core\Utility\Service\Text
     */
    private $th;

    /**
     * @var Config
     */
    private $config;

    /**
     * Factory constructor.
     *
     * @param Config|null $config
     */
    public function __construct(?Config $config = null)
    {
        $app = $this->app();
        $this->urlResolver = $app->make(PageUrlResolver::class);
        $this->dh = $app->make('date');
        $this->th = $app->make('helper/text');
        $this->config = $config ?? ConfigManager::getDefault();
    }

    /**
     * @return Config
     */
    final public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @param Config $config
     * @return Factory
     */
    final public function setConfig(Config $config): Factory
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Build PageInfo Fetcher.
     *
     * @param Page $page
     *
     * @return PageInfo|null Return PageInfo object or Null if page has COLLECTION_NOT_FOUND Error
     */
    public function build(Page $page): ?PageInfo
    {
        $pageInfo = null;
        if ($page->getError() !== COLLECTION_NOT_FOUND) {
            $pageInfo = new PageInfo($page, $this->urlResolver, $this->th, $this->dh, $this->config);
        }

        return $pageInfo;
    }
}
