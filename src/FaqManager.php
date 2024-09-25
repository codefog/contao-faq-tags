<?php

declare(strict_types=1);

/*
 * FAQ Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2020, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\FaqTagsBundle;

use Codefog\TagsBundle\Manager\DefaultManager;
use Codefog\TagsBundle\Tag;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\Input;
use Contao\PageModel;

class FaqManager
{
    /**
     * Sorting order.
     */
    public const ORDER_NAME_ASC = 'name_asc';
    public const ORDER_NAME_DESC = 'name_desc';
    public const ORDER_COUNT_ASC = 'count_asc';
    public const ORDER_COUNT_DESC = 'count_desc';

    /**
     * URL parameter.
     */
    public const URL_PARAMETER = 'tag';

    public function __construct(
        private readonly ContentUrlGenerator $contentUrlGenerator,
        private readonly ContaoFramework $framework,
        private readonly DefaultManager $tagsManager,
    )
    {
    }

    /**
     * Generate multiple tags.
     */
    public function generateTags(array $tags, int $pageId = null): array
    {
        if (0 === \count($tags)) {
            return [];
        }

        $return = [];

        /** @var Tag $tag */
        foreach ($tags as $tag) {
            $return[] = $this->generateTag($tag, $pageId);
        }

        return $return;
    }

    /**
     * Generate a single tag.
     */
    public function generateTag(Tag $tag, int $pageId = null): array
    {
        /** @var Input $inputAdapter */
        $inputAdapter = $this->framework->getAdapter(Input::class);

        $data = [
            'name' => $tag->getName(),
            'isActive' => $inputAdapter->get(self::URL_PARAMETER) === $tag->getData()['alias'],
        ];

        // Add the URL, if any
        if ($pageId && ($pageModel = PageModel::findByPk($pageId)) !== null) {
            $data['url'] = $this->contentUrlGenerator->generate($pageModel, ['parameters' => sprintf('/%s/%s', self::URL_PARAMETER, $tag->getData()['alias'])]);
        }

        $tagData = $tag->getData();

        // Add the tag count data, if available
        if (isset($tagData['count'])) {
            $data['count'] = $tagData['count'];
        }

        return $data;
    }

    /**
     * Get the FAQ tags.
     */
    public function getFaqTags(int $faqId): array
    {
        return $this->tagsManager->getTagFinder()->findMultiple($this->tagsManager->createTagCriteria()->setSourceIds([$faqId]));
    }

    /**
     * Sort the tags.
     */
    public function sortTags(array $tags, string $order): array
    {
        switch ($order) {
            case self::ORDER_NAME_ASC:
                usort($tags, static function (Tag $a, Tag $b): int {
                    return strnatcasecmp($a->getName(), $b->getName());
                });
                break;

            case self::ORDER_NAME_DESC:
                usort($tags, static function (Tag $a, Tag $b): int {
                    return -strnatcasecmp($a->getName(), $b->getName());
                });
                break;

            case self::ORDER_COUNT_ASC:
                usort($tags, static function (Tag $a, Tag $b): int {
                    $diff = $a->getData()['count'] - $b->getData()['count'];

                    // Sort the same value records alphabetically
                    if (0 === $diff) {
                        return strnatcasecmp($a->getName(), $b->getName());
                    }

                    return $diff;
                });
                break;

            case self::ORDER_COUNT_DESC:
                usort($tags, static function (Tag $a, Tag $b): int {
                    $diff = $b->getData()['count'] - $a->getData()['count'];

                    // Sort the same value records alphabetically
                    if (0 === $diff) {
                        return strnatcasecmp($a->getName(), $b->getName());
                    }

                    return $diff;
                });
                break;
        }

        return $tags;
    }
}
