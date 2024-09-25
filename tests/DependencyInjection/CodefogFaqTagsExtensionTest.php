<?php

declare(strict_types=1);

/*
 * FAQ Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2020, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\FaqTagsBundle\Test\DependencyInjection;

use Codefog\FaqTagsBundle\DependencyInjection\CodefogFaqTagsExtension;
use Codefog\FaqTagsBundle\FaqManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CodefogFaqTagsExtensionTest extends TestCase
{
    public function testLoad(): void
    {
        $container = new ContainerBuilder();
        $extension = new CodefogFaqTagsExtension();

        $extension->load([], $container);

        $this->assertTrue($container->hasDefinition(FaqManager::class));
        $this->assertTrue($container->hasDefinition('codefog_faq_tags.faq_tag_list_module'));
    }
}
