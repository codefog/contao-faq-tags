<?php

declare(strict_types=1);

/*
 * FAQ Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2020, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\FaqTagsBundle\Controller\FrontendModule;

use Codefog\FaqTagsBundle\FaqManager;
use Codefog\TagsBundle\Manager\DefaultManager;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\ModuleModel;
use Contao\StringUtil;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule('faq_tag_list', category: 'faq', template: 'mod_faq_tag_list')]
class FaqTagListModule extends AbstractFrontendModuleController
{
    public function __construct(
        private readonly Connection $connection,
        private readonly FaqManager $faqManager,
        private readonly DefaultManager $tagsManager,
        private readonly TokenChecker $tokenChecker,
    )
    {
    }

    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        if (0 === \count($tags = $this->findTags($model))) {
            return new Response();
        }

        $template->tags = $this->faqManager->generateTags($tags, (int) $model->faq_tagsTargetPage);

        return $template->getResponse();
    }

    /**
     * Find the tags.
     */
    protected function findTags(ModuleModel $model): array
    {
        $faqCategories = StringUtil::deserialize($model->faq_categories, true);

        if (!\is_array($faqCategories) || 0 === \count($faqCategories)) {
            return [];
        }

        $isPreviewMode = $this->tokenChecker->isPreviewMode();

        $faqIds = $this->connection->fetchAllAssociative(
            'SELECT id FROM tl_faq WHERE pid IN (?)'.(!$isPreviewMode ? ' AND published=?' : ''),
            $isPreviewMode ? [$faqCategories] : [$faqCategories, 1],
            [ArrayParameterType::INTEGER]
        );

        if (0 === \count($faqIds)) {
            return [];
        }

        $criteria = $this->tagsManager
            ->createTagCriteria()
            ->setSourceIds(array_column($faqIds, 'id'))
            ->setUsedOnly(true)
        ;

        $limit = $model->numberOfItems ? (int) $model->numberOfItems : null;

        if (0 === \count($tags = $this->tagsManager->getTagFinder()->getTopTags($criteria, $limit, true))) {
            return [];
        }

        return $this->faqManager->sortTags($tags, $model->faq_tagsOrder);
    }
}
