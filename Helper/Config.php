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

namespace Magenest\CacheWarmer\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Config extends AbstractHelper
{
    const GENERAL = 'magenest_cachewarmer/general/';
    const PERFORMANCE = 'magenest_cachewarmer/performance_settings/';

    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    public function isModuleEnabled()
    {
        return $this->scopeConfig->getValue(self::GENERAL . 'enabled');
    }

    public function isGenerateProductSaveEnabled()
    {
        return $this->scopeConfig->getValue(self::GENERAL . 'auto_update');
    }

    public function isHitProductSaveEnabled()
    {
        return $this->scopeConfig->getValue(self::GENERAL . 'auto_hit');
    }

    public function isCacheFlushEnabled()
    {
        return $this->scopeConfig->getValue(self::GENERAL . 'auto_flush');
    }

    public function getAdditionalUrls()
    {
        return $this->scopeConfig->getValue(self::GENERAL . 'custom_urls');
    }

    public function isScheduleEnabled()
    {
        return $this->scopeConfig->getValue(self::PERFORMANCE . 'enable_schedule');
    }

    public function getMaxRequests()
    {
        return $this->scopeConfig->getValue(self::PERFORMANCE . 'max_requests');
    }

    public function getScheduledBatchSize()
    {
        return $this->scopeConfig->getValue(self::PERFORMANCE . 'scheduled_batch_size');
    }
}