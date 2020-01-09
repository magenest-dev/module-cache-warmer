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

namespace Magenest\CacheWarmer\Model;

use Magenest\CacheWarmer\Helper\Config;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\CacheInterface;

class CronJob
{
    /**
     * @var Queue
     */
    private $queue;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var CacheInterface
     */
    private $_cache;

    public function __construct(
        Queue $queue,
        Config $config,
        CacheInterface $cache,
        LoggerInterface $logger
    )
    {
        $this->_cache = $cache;
        $this->queue = $queue;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function enqueue()
    {
        if (!$this->config->isModuleEnabled() || !$this->config->isScheduleEnabled()) {
            return;
        }
        try {
            list($result, $generatedUrls) = $this->queue->enqueue();
            if (!$result) {
                $this->logger->info(__($generatedUrls));
            }
        } catch (\Exception $e) {
            $this->logger->info(__($e->getMessage()));
        }
    }

    public function dequeue()
    {
        if (!$this->config->isModuleEnabled() || !$this->config->isScheduleEnabled()) {
            return;
        }
        try {
            $batchSize = $this->config->getScheduledBatchSize();
            list($result, $processedUrls, $missedUrls) = $this->queue->dequeue($batchSize);
            if (!$result) {
                $this->logger->info(__($missedUrls));
            }
        } catch (\Exception $e) {
            $this->logger->info(__($e->getMessage()));
        }
    }
}
