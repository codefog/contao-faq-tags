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

use Symfony\Component\HttpKernel\Bundle\Bundle;

class CodefogFaqTagsBundle extends Bundle
{
    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}
