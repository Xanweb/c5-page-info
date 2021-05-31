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
     * @var string
     */
    private $pageInfoClass;

    /**
     * Factory constructor.
     *
     * @param Config|null $config
     * @param string $pageInfoClass
     */
    public function __construct(?Config $config = null, string $pageInfoClass = PageInfo::class)
    {
        if ($pageInfoClass !== PageInfo::class && !is_subclass_of($pageInfoClass, PageInfo::class)) {
            throw new \InvalidArgumentException(t('%s:%s - `%s` should be a subclass of `%s`', static::class, '__construct', $pageInfoClass, PageInfo::class));
        }

        $app = $this->app();
        $this->urlResolver = $app->make(PageUrlResolver::class);
        $this->dh = $app->make('date');
        $this->th = $app->make('helper/text');
        $this->config = $config ?? ConfigManager::getDefault();
        $this->pageInfoClass = $pageInfoClass;
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
     *
     * @return Factory
     */
    final public function setConfig(Config $config): self
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Clone Current factory with defined config.
     *
     * @param string $configKey
     *
     * @return $this
     */
    final public function withConfig(string $configKey): self
    {
        $factory = clone $this;
        $factory->setConfig(ConfigManager::config($configKey));

        return $factory;
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
            $pageInfo = new $this->pageInfoClass($page, $this->urlResolver, $this->th, $this->dh, $this->config);
        }

        return $pageInfo;
    }
}
