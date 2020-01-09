<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_CacheWarmer extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package Magenest_CacheWarmer
 */

namespace Magenest\CacheWarmer\Block\Adminhtml;

use Magento\Backend\Block\Template;

class Information extends Template
{
    const ENQUEUE_COMMAND = 'magenest:cachewarmer:generate';
    const DEQUEUE_COMMAND = 'magenest:cachewarmer:warm';
    const MODULE_DISABLED = 0;

    public function getText()
    {
        return __(
            '<pre>CLI commands that can be run to execute:' .
            '<br/>Queue Generation:    \'bin/magento ' . self::ENQUEUE_COMMAND . '\'' .
            '<br/>Pages Warming Up:    \'bin/magento ' . self::DEQUEUE_COMMAND . '\'</pre>'
        );
    }
}
