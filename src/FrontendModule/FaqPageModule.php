<?php

declare(strict_types=1);

/*
 * FAQ Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2020, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\FaqTagsBundle\FrontendModule;

use Contao\Date;
use Contao\Environment;
use Contao\FaqCategoryModel;
use Contao\FaqModel;
use Contao\FilesModel;
use Contao\ModuleFaq;
use Contao\ModuleFaqPage;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Contao\UserModel;

class FaqPageModule extends ModuleFaqPage
{
    use TagsTrait;

    /**
     * {@inheritDoc}
     */
    protected function compile(): void
    {
        // Filter items by tag
        if ($this->faq_allowTagFiltering && ($tag = $this->getCurrentTag()) !== null) {
            $objFaqs = $this->getFaqItemsByTag($tag, $this->faq_categories);
            $this->Template->tagsHeadline = sprintf($GLOBALS['TL_LANG']['MSC']['faqTagsHeadline'], $tag->getName());
        } else {
            $objFaqs = FaqModel::findPublishedByPids($this->faq_categories);
        }

        if (null === $objFaqs) {
            $this->Template->faq = [];

            return;
        }

        /* @var PageModel $objPage */
        global $objPage;

        $tags = [];
        $arrFaqs = array_fill_keys($this->faq_categories, []);

        // Add FAQs
        foreach ($objFaqs as $objFaq)
        {
            /** @var FaqModel $objFaq */
            $objTemp = (object) $objFaq->row();

            $objTemp->answer = StringUtil::encodeEmail($objFaq->answer);
            $objTemp->addImage = false;
            $objTemp->addBefore = false;

            // Add an image
            if ($objFaq->addImage)
            {
                $figure = System::getContainer()
                    ->get('contao.image.studio')
                    ->createFigureBuilder()
                    ->from($objFaq->singleSRC)
                    ->setSize($objFaq->size)
                    ->setOverwriteMetadata($objFaq->getOverwriteMetadata())
                    ->setLightboxGroupIdentifier('lightbox[' . substr(md5('mod_faqpage_' . $objFaq->id), 0, 6) . ']')
                    ->enableLightbox((bool) $objFaq->fullsize)
                    ->buildIfResourceExists();

                if (null !== $figure)
                {
                    $figure->applyLegacyTemplateData($objTemp, $objFaq->imagemargin, $objFaq->floating);
                }
            }

            $objTemp->enclosure = array();

            // Add enclosure
            if ($objFaq->addEnclosure)
            {
                $this->addEnclosuresToTemplate($objTemp, $objFaq->row());
            }

            $strAuthor = '';

            /** @var UserModel $objAuthor */
            if (($objAuthor = $objFaq->getRelated('author')) instanceof UserModel)
            {
                $strAuthor = $objAuthor->name;
            }

            $objTemp->info = sprintf($GLOBALS['TL_LANG']['MSC']['faqCreatedBy'], Date::parse($objPage->dateFormat, $objFaq->tstamp), $strAuthor);

            // Add the tags
            if ($this->faq_showTags) {
                $objTemp->tags = $this->getFaqTags($objFaq->current(), (int) $this->faq_tagsTargetPage);
            }

            /** @var FaqCategoryModel $objPid */
            $objPid = $objFaq->getRelated('pid');

            if (empty($arrFaqs[$objFaq->pid]))
            {
                $arrFaqs[$objFaq->pid] = $objPid->row();
            }

            $arrFaqs[$objFaq->pid]['items'][] = $objTemp;

            $tags[] = 'contao.db.tl_faq.' . $objFaq->id;
        }

        // Tag the FAQs (see #2137)
        if (System::getContainer()->has('fos_http_cache.http.symfony_response_tagger'))
        {
            $responseTagger = System::getContainer()->get('fos_http_cache.http.symfony_response_tagger');
            $responseTagger->addTags($tags);
        }

        $arrFaqs = array_values(array_filter($arrFaqs));
        $limit_i = \count($arrFaqs) - 1;

        // Add classes first, last, even and odd
        for ($i = 0; $i <= $limit_i; ++$i) {
            $class = ((0 === $i) ? 'first ' : '').(($i === $limit_i) ? 'last ' : '').((0 === $i % 2) ? 'even' : 'odd');
            $arrFaqs[$i]['class'] = trim($class);
            $limit_j = \count($arrFaqs[$i]['items']) - 1;

            for ($j = 0; $j <= $limit_j; ++$j) {
                $class = ((0 === $j) ? 'first ' : '').(($j === $limit_j) ? 'last ' : '').((0 === $j % 2) ? 'even' : 'odd');
                $arrFaqs[$i]['items'][$j]->class = trim($class);
            }
        }

        $this->Template->faq = $arrFaqs;
        $this->Template->request = Environment::get('indexFreeRequest');
        $this->Template->topLink = $GLOBALS['TL_LANG']['MSC']['backToTop'];

        $this->Template->getSchemaOrgData = function () use ($objFaqs)
        {
            return ModuleFaq::getSchemaOrgData($objFaqs, '#/schema/faq/' . $this->id);
        };
    }
}
