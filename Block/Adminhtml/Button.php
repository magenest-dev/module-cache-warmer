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

use Magenest\CacheWarmer\Helper\Config;
use Magenest\CacheWarmer\Model\Queue;
use Magento\Backend\Block\Template;

class Button extends Template
{
    const ENQUEUE_ACTION = 'enqueue_action';
    const DEQUEUE_ACTION = 'dequeue_action';
    const STOP_ACTION = 'stop_action';

    protected $queue;
    protected $config;

    public function __construct(
        Template\Context $context,
        Queue $queue,
        Config $config,
        array $data = []
    )
    {
        $this->config = $config;
        $this->queue = $queue;
        parent::__construct($context, $data);
    }

    public function getQueueProcessUrl()
    {
        return $this->getUrl('magenestcw/queue/process');
    }

    public function getIsModuleEnabled()
    {
        return $this->config->isModuleEnabled();
    }
}
