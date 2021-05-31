<?php

namespace Xanweb\PageInfo\Fetcher;

use Concrete\Block\Content\Controller as ContentBlockController;
use Concrete\Core\Entity\File\File;
use Concrete\Core\File\File as FileService;
use Sunra\PhpSimple\HtmlDomParser;

class ContentImageFetcher extends BlockPropertyFetcher
{
    /**
     * @var bool
     */
    protected static $isAdvancedImageCkEditorPluginActivated;

    /**
     * ContentImageFetcher constructor.
     *
     * @param array $includeAreas List of area handles that will be included in fetching.
     * @param array|null $excludeAreas List of area handles that will be excluded from fetching.
     */
    public function __construct(array $includeAreas = [], ?array $excludeAreas = null)
    {
        parent::__construct(
            'content',
            function ($blockController) {
                return $this->fetchImage($blockController);
            },
            function ($blockController) {
                return $this->isValidBlock($blockController);
            },
            $excludeAreas,
            $includeAreas
        );
    }

    protected function isValidBlock(ContentBlockController $blockController): bool
    {
        $content = $blockController->get('content');

        return $content !== null && (str_contains($content, '<concrete-picture') || str_contains($content, '<img '));
    }

    protected function fetchImage(ContentBlockController $blockController): ?File
    {
        $content = $blockController->get('content');
        $image = null;

        $r = HtmlDomParser::str_get_html($content, true, true, DEFAULT_TARGET_CHARSET, false);
        if (is_object($r)) {
            if (static::isAdvancedImageCkEditorPluginActivated()) {
                $imageTags = $r->find('img');
            } else {
                $imageTags = $r->find('concrete-picture');
            }

            foreach ($imageTags as $tag) {
                if (static::isAdvancedImageCkEditorPluginActivated()
                    && $tag->parentNode() !== null
                    && str_contains($tag->parentNode()->getAttribute('class'), 'imagewrapper')) {
                    if (str_contains($tag->src, 'CCM:FID_DL')) {
                        $fID = str_replace(['{CCM:FID_DL_', '}'], '', $tag->src);
                    } else {
                        continue;
                    }
                } else {
                    $fID = $tag->fid;
                }

                if (function_exists('uuid_is_valid') && uuid_is_valid($fID)) {
                    $image = FileService::getByUUID($fID);
                } else {
                    $image = FileService::getByID($fID);
                }

                if ($image !== null) {
                    break;
                }
            }
        }

        return $image;
    }

    protected static function isAdvancedImageCkEditorPluginActivated(): bool
    {
        if (!isset(static::$isAdvancedImageCkEditorPluginActivated)) {
            if (class_exists(\CkEditorPlugins::class) && \CkEditorPlugins::isInstalled()) {
                $pluginManager = app('editor')->getPluginManager();
                static::$isAdvancedImageCkEditorPluginActivated = $pluginManager->isSelected('advancedimage') || $pluginManager->isSelected('inlineimage');
            } else {
                static::$isAdvancedImageCkEditorPluginActivated = false;
            }
        }

        return static::$isAdvancedImageCkEditorPluginActivated;
    }
}
