<?php

declare(strict_types=1);

/*
 * FAQ Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2020, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;

/*
 * Palettes
 */
PaletteManipulator::create()
    ->addField('tags', 'title_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_faq')
;

/*
 * Fields
 */
$GLOBALS['TL_DCA']['tl_faq']['fields']['tags'] = [
    'exclude' => true,
    'filter' => true,
    'inputType' => 'cfgTags',
    'eval' => ['tagsManager' => 'codefog_faq', 'tl_class' => 'clr'],
];
