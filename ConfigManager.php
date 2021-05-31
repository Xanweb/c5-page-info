<?php

namespace Xanweb\PageInfo;

use Concrete\Core\Entity\Block\BlockType\BlockType;
use Doctrine\ORM\EntityManagerInterface;
use Xanweb\Common\Traits\ApplicationTrait;
use Xanweb\Common\Traits\SingletonTrait;
use Xanweb\PageInfo\Exception\UndefinedConfigException;
use Xanweb\PageInfo\Fetcher\AttributePropertyFetcher;
use Xanweb\PageInfo\Fetcher\BlockPropertyFetcher;
use Xanweb\PageInfo\Fetcher\PagePropertyFetcher;

class ConfigManager
{
    use ApplicationTrait;
    use SingletonTrait;

    public const DEFAULT = 'default';
    public const BASIC = 'basic';
    public const ADVANCED = 'advanced';

    /**
     * @var Config[]|callable[]
     */
    private $configs = [];

    /**
     * Register Config.
     *
     * @param string $configKey
     * @param Config|callable $config
     */
    public function register(string $configKey, $config): void
    {
        if ($config instanceof Config || is_callable($config)) {
            $this->configs[$configKey] = $config;
            return;
        }

        throw new \InvalidArgumentException(t(
            '%s:%s - `%s` should be an instance of `%s`',
            static::class,
            '__construct',
            '$config',
            Config::class . ' or callable'
        ));
    }

    /**
     * Check if config exists.
     *
     * @param string $configKey
     *
     * @return bool
     */
    public function has(string $configKey): bool
    {
        return isset($this->configs[$configKey]) || in_array($configKey, [self::DEFAULT, self::BASIC, self::ADVANCED]);
    }

    /**
     * @param string $configKey
     *
     * @return Config
     */
    public function getConfig(string $configKey): Config
    {
        if (isset($this->configs[$configKey])) {
            if (is_callable($config = $this->configs[$configKey])) {
                $this->configs[$configKey] = $config = $config();
            }

            return $config;
        }

        // Register predefined configs
        switch ($configKey) {
            case self::DEFAULT:
                $this->registerDefaultConfig();
                break;
            case self::BASIC:
                $this->registerBasicConfig();
                break;
            case self::ADVANCED:
                $this->registerAdvancedConfig();
                break;
            default:
                throw new UndefinedConfigException(t('Can\'t get config with key `%s`.', $configKey));
        }

        return $this->getConfig($configKey);
    }

    public static function getDefault(): Config
    {
        return static::get()->getConfig(self::DEFAULT);
    }

    public static function getBasic(): Config
    {
        return static::get()->getConfig(self::BASIC);
    }

    public static function getAdvanced(): Config
    {
        return static::get()->getConfig(self::ADVANCED);
    }

    protected function registerDefaultConfig(): void
    {
        $config = $this->app(Config::class);
        $config->registerPageNameFetcher(new PagePropertyFetcher(PagePropertyFetcher::PAGE_NAME));
        $config->registerPageDescriptionFetcher(new PagePropertyFetcher(PagePropertyFetcher::PAGE_DESCRIPTION));
        $config->registerThumbnailFetcher(new AttributePropertyFetcher('thumbnail'));

        $this->register(self::DEFAULT, $config);
    }

    protected function registerBasicConfig(): void
    {
        $config = $this->app(Config::class);
        $config->registerPageNameFetcher(new PagePropertyFetcher(PagePropertyFetcher::PAGE_NAME));
        $config->registerPageDescriptionFetcher(new PagePropertyFetcher(PagePropertyFetcher::PAGE_DESCRIPTION));
        $config->registerThumbnailFetcher(new AttributePropertyFetcher('thumbnail'));

        $repo = $this->app(EntityManagerInterface::class)->getRepository(BlockType::class);
        $btXanImage = $repo->findOneBy(['btHandle' => 'xan_image']);
        $config->registerThumbnailFetcher(new BlockPropertyFetcher(
            is_object($btXanImage) ? 'xan_image' : 'image', function ($bController) {
                return $bController->getFileObject();
            })
        );

        $this->register(self::BASIC, $config);
    }

    protected function registerAdvancedConfig(): void
    {
        $config = $this->app(Config::class);
        $repo = $this->app(EntityManagerInterface::class)->getRepository(BlockType::class);
        $pageHeadingBlock = $repo->findOneBy(['btHandle' => 'page_heading']);
        if (is_object($pageHeadingBlock)) {
            $config->registerPageNameFetcher(new BlockPropertyFetcher(
                'page_heading', function ($bController) {
                    return $bController->getPageHeading();
                })
            );

            $config->registerPageDescriptionFetcher(new BlockPropertyFetcher(
                'page_heading', function ($bController) {
                    return $bController->getTeaserText();
                })
            );
        } else {
            $config->registerPageNameFetcher(new BlockPropertyFetcher(
                'page_title', function ($bController) {
                    return $bController->getTitleText();
                })
            );
        }

        $config->registerPageNameFetcher(new PagePropertyFetcher(PagePropertyFetcher::PAGE_NAME));
        $config->registerPageDescriptionFetcher(new PagePropertyFetcher(PagePropertyFetcher::PAGE_DESCRIPTION));
        $config->registerThumbnailFetcher(new AttributePropertyFetcher('thumbnail'));

        $btXanImage = $repo->findOneBy(['btHandle' => 'xan_image']);
        $config->registerThumbnailFetcher(new BlockPropertyFetcher(
            is_object($btXanImage) ? 'xan_image' : 'image', function ($bController) {
                return $bController->getFileObject();
            })
        );

        $this->register(self::ADVANCED, $config);
    }

    /**
     * Static access for get config method.
     *
     * @param string $configKey
     *
     * @return Config
     */
    public static function config(string $configKey): Config
    {
        return static::get()->getConfig($configKey);
    }
}
