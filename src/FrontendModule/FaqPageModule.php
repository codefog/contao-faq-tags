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
            $objFaq = $this->getFaqItemsByTag($tag, $this->faq_categories);
            $this->Template->tagsHeadline = sprintf($GLOBALS['TL_LANG']['MSC']['faqTagsHeadline'], $tag->getName());
        } else {
            $objFaq = FaqModel::findPublishedByPids($this->faq_categories);
        }

        if (null === $objFaq) {
            $this->Template->faq = [];

            return;
        }

        /* @var PageModel $objPage */
        global $objPage;

        $arrFaqs = array_fill_keys($this->faq_categories, []);
        $projectDir = System::getContainer()->getParameter('kernel.project_dir');

        // Add FAQs
        while ($objFaq->next()) {
            /** @var FaqModel $objFaq */
            $objTemp = (object) $objFaq->row();

            // Clean the RTE output
            $objTemp->answer = StringUtil::toHtml5($objFaq->answer);

            $objTemp->answer = StringUtil::encodeEmail($objTemp->answer);
            $objTemp->addImage = false;

            // Add an image
            if ($objFaq->addImage && $objFaq->singleSRC) {
                $objModel = FilesModel::findByUuid($objFaq->singleSRC);

                if (null !== $objModel && is_file($projectDir.'/'.$objModel->path)) {
                    // Do not override the field now that we have a model registry (see #6303)
                    $arrFaq = $objFaq->row();
                    $arrFaq['singleSRC'] = $objModel->path;
                    $strLightboxId = 'lightbox['.substr(md5('mod_faqpage_'.$objFaq->id), 0, 6).']'; // see #5810

                    $this->addImageToTemplate($objTemp, $arrFaq, null, $strLightboxId, $objModel);
                }
            }

            $objTemp->enclosure = [];

            // Add enclosure
            if ($objFaq->addEnclosure) {
                $this->addEnclosuresToTemplate($objTemp, $objFaq->row());
            }

            // Add the tags
            if ($this->faq_showTags) {
                $objTemp->tags = $this->getFaqTags($objFaq->current(), (int) $this->faq_tagsTargetPage);
            }

            /** @var UserModel $objAuthor */
            $objAuthor = $objFaq->getRelated('author');
            $objTemp->info = sprintf($GLOBALS['TL_LANG']['MSC']['faqCreatedBy'], Date::parse($objPage->dateFormat, $objFaq->tstamp), $objAuthor->name);

            /** @var FaqCategoryModel $objPid */
            $objPid = $objFaq->getRelated('pid');

            // Order by PID
            $arrFaqs[$objFaq->pid]['items'][] = $objTemp;
            $arrFaqs[$objFaq->pid]['headline'] = $objPid->headline;
            $arrFaqs[$objFaq->pid]['title'] = $objPid->title;
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
    }
}
