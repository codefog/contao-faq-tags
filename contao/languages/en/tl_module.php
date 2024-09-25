<?php

declare(strict_types=1);

/*
 * FAQ Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2020, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

/*
 * Fields
 */
$GLOBALS['TL_LANG']['tl_module']['faq_allowTagFiltering'] = ['Allow filtering FAQ by tags', 'Allow to filter the FAQ items by a tag provided in the URL.'];
$GLOBALS['TL_LANG']['tl_module']['faq_showTags'] = ['Show FAQ tags', 'Show tags assigned to each FAQ item. Be sure to select the module template that supports displaying tags (e.g. <em>mod_faqpage_tags</em>).'];
$GLOBALS['TL_LANG']['tl_module']['faq_tagsOrder'] = ['FAQ tags order', 'Here you can choose the FAQ tags order.'];
$GLOBALS['TL_LANG']['tl_module']['faq_tagsTargetPage'] = ['FAQ tags target page', 'Here you can choose the tags target page which will be used to generate a tag link. Usually, you want to set this to a page that contains a FAQ listing module that can be filtered.'];

/*
 * Reference
 */
$GLOBALS['TL_LANG']['tl_module']['faq_tagsOrderRef'] = [
    \Codefog\FaqTagsBundle\FaqManager::ORDER_NAME_ASC => 'Name ascending',
    \Codefog\FaqTagsBundle\FaqManager::ORDER_NAME_DESC => 'Name descending',
    \Codefog\FaqTagsBundle\FaqManager::ORDER_COUNT_ASC => 'Count ascending',
    \Codefog\FaqTagsBundle\FaqManager::ORDER_COUNT_DESC => 'Count descending',
];
