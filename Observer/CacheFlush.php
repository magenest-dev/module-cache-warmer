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

namespace Magenest\CacheWarmer\Observer;

use Magenest\CacheWarmer\Helper\Config;
use Magenest\CacheWarmer\Model\Queue;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class CacheFlush implements ObserverInterface
{
    protected $queue;
    protected $config;
    protected $logger;

    /**
     * CacheFlush constructor.
     * @param LoggerInterface $logger
     * @param Queue $queue
     * @param Config $config
     */
    public function __construct(
        LoggerInterface $logger,
        Queue $queue,
        Config $config
    )
    {
        $this->logger = $logger;
        $this->queue = $queue;
        $this->config = $config;
    }

    public function execute(Observer $observer)
    {
        if (!$this->config->isModuleEnabled() || !$this->config->isCacheFlushEnabled()) {
            return;
        }
        $this->logger->info(__('Generating queue...'));
        list($result, $generatedUrls) = $this->queue->enqueue();
        if ($result) {
            $this->logger->info(__('%1 pages are generated successfully.', $generatedUrls));
        } else {
            $this->logger->info(__($generatedUrls));
        }
    }
}