<?php

declare(strict_types=1);

/*
 * FAQ Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2020, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\FaqTagsBundle\Test;

use Codefog\FaqTagsBundle\FaqManager;
use Codefog\TagsBundle\Finder\TagCriteria;
use Codefog\TagsBundle\Finder\TagFinder;
use Codefog\TagsBundle\Manager\DefaultManager;
use Codefog\TagsBundle\Tag;
use Contao\Input;
use Contao\PageModel;
use Contao\TestCase\ContaoTestCase;

class FaqManagerTest extends ContaoTestCase
{
    public function testGenerateTags(): void
    {
        $pageModelAdapter = $this->mockConfiguredAdapter(['findPublishedById' => null]);
        $inputAdapter = $this->mockConfiguredAdapter(['get' => 'foobar']);

        $faqManager = new FaqManager(
            $this->mockContaoFramework([
                Input::class => $inputAdapter,
                PageModel::class => $pageModelAdapter,
            ]),
            $this->createMock(DefaultManager::class)
        );

        $this->assertCount(0, $faqManager->generateTags([]));

        $this->assertCount(2, $faqManager->generateTags([
            new Tag('123', 'Foobar', ['alias' => 'foobar', 'count' => 100]),
            new Tag('456', 'Foobaz', ['alias' => 'foobaz', 'count' => 200]),
        ]));
    }

    public function testGenerateTag(): void
    {
        $inputAdapter = $this->mockConfiguredAdapter(['get' => 'foobar']);

        $faqManager = new FaqManager(
            $this->mockContaoFramework([Input::class => $inputAdapter]),
            $this->createMock(DefaultManager::class)
        );

        $fullData = $faqManager->generateTag(new Tag('123', 'Foobar', ['alias' => 'foobar', 'count' => 100]), 'page-alias/tag/%s.html');

        $this->assertEquals('Foobar', $fullData['name']);
        $this->assertTrue($fullData['isActive']);
        $this->assertEquals('page-alias/tag/foobar.html', $fullData['url']);
        $this->assertEquals(100, $fullData['count']);

        $basicData = $faqManager->generateTag(new Tag('456', 'Foobaz', ['alias' => 'foobaz']));

        $this->assertEquals('Foobaz', $basicData['name']);
        $this->assertFalse($basicData['isActive']);
        $this->assertArrayNotHasKey('url', $basicData);
        $this->assertArrayNotHasKey('count', $basicData);
    }

    public function testGetFaqTags(): void
    {
        $tagFinder = $this->createMock(TagFinder::class);
        $tagFinder
            ->expects($this->once())
            ->method('findMultiple')
            ->willReturn([
                new Tag('1', 'foobar'),
                new Tag('2', 'foobaz'),
            ])
        ;

        $tagCriteria = $this->createMock(TagCriteria::class);
        $tagCriteria
            ->expects($this->once())
            ->method('setSourceIds')
            ->with($this->equalTo([123]))
        ;

        $tagsManager = $this->createConfiguredMock(DefaultManager::class, [
            'getTagFinder' => $tagFinder,
            'createTagCriteria' => $tagCriteria,
        ]);

        $faqManager = new FaqManager($this->mockContaoFramework(), $tagsManager);
        $tags = $faqManager->getFaqTags(123);

        $this->assertCount(2, $tags);
        $this->assertEquals('1', $tags[0]->getValue());
        $this->assertEquals('foobar', $tags[0]->getName());
        $this->assertEquals('2', $tags[1]->getValue());
        $this->assertEquals('foobaz', $tags[1]->getName());
    }

    /**
     * @dataProvider sortTagsDataProvider
     */
    public function testSortTags(array $tags, string $order, array $expectedOrderIds): void
    {
        $faqManager = new FaqManager($this->mockContaoFramework(), $this->createMock(DefaultManager::class));
        $sortedTagIds = [];

        /** @var Tag $tag */
        foreach ($faqManager->sortTags($tags, $order) as $tag) {
            $sortedTagIds[] = $tag->getValue();
        }

        $this->assertEquals($expectedOrderIds, $sortedTagIds);
    }

    public function sortTagsDataProvider(): array
    {
        return [
            'Name ascending' => [
                [
                    new Tag('2', 'B'),
                    new Tag('1', 'A'),
                    new Tag('3', 'C'),
                ],
                FaqManager::ORDER_NAME_ASC,
                ['1', '2', '3'],
            ],
            'Name descending' => [
                [
                    new Tag('2', 'B'),
                    new Tag('1', 'A'),
                    new Tag('3', 'C'),
                ],
                FaqManager::ORDER_NAME_DESC,
                ['3', '2', '1'],
            ],
            'Count ascending' => [
                [
                    new Tag('1', 'A', ['count' => 5]),
                    new Tag('2', 'B', ['count' => 2]),
                    new Tag('3', 'C', ['count' => 2]),
                    new Tag('4', 'D', ['count' => 10]),
                ],
                FaqManager::ORDER_COUNT_ASC,
                ['2', '3', '1', '4'],
            ],
            'Count descending' => [
                [
                    new Tag('1', 'A', ['count' => 5]),
                    new Tag('2', 'B', ['count' => 2]),
                    new Tag('3', 'C', ['count' => 2]),
                    new Tag('4', 'D', ['count' => 10]),
                ],
                FaqManager::ORDER_COUNT_DESC,
                ['4', '1', '2', '3'],
            ],
        ];
    }
}
