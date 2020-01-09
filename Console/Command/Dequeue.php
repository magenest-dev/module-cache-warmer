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

namespace Magenest\CacheWarmer\Console\Command;

use Magenest\CacheWarmer\Helper\Config;
use Magenest\CacheWarmer\Model\Queue;
use Magenest\CacheWarmer\Block\Adminhtml\Information;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Log\LoggerInterface;

class Dequeue extends Command
{
    protected $queue;
    protected $config;
    protected $logger;

    public function __construct(
        Queue $queue,
        Config $config,
        LoggerInterface $logger
    )
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->queue = $queue;
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(Information::DEQUEUE_COMMAND);
        $this->setDescription('Dequeue pages for cache warming.');

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return null|int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->config->isModuleEnabled()) {
            $startTime = microtime(true);
            $output->writeln(__('<info>Warming up pages...</info>'));
            $this->logger->info(__("Warming up pages..."));
            $batchSize = $this->queue->getQueueSize();
            $output->writeln(__("<info>Batch size: $batchSize </info>"));
            $this->logger->info(__('Batch size: %1', $batchSize));
            list($result, $processedUrls, $missedUrls) = $this->queue->dequeue($batchSize);
            $endTime = microtime(true);
            if ($result) {
                $output->writeln(__('<info>A total of %1 record(s) were cache hit.</info>', $processedUrls));
                $this->logger->info(__('A total of %1 record(s) were cache hit.', $processedUrls));
                $resultTime = $endTime - $startTime;
                $output->writeln('<info>Done in ' . gmdate('H:i:s', $resultTime) . '</info>');
                $this->logger->info(__('Done in %1', gmdate('H:i:s', $resultTime)));
                if ($missedUrls > 0) {
                    $output->writeln(__('<info>A total of %1 record(s) were cache missed.</info>', $missedUrls));
                    $this->logger->info(__('A total of %1 record(s) were cache missed.', $missedUrls));
                }
                $size = $this->queue->getQueueSize();
                $output->writeln(__("<info>Record(s) left in queue: $size </info>"));
                $this->logger->info(__('Record(s) left in queue: %1', $size));
            } else {
                $output->writeln(__('<error>' . $missedUrls . '</error>'));
                $this->logger->info(__($missedUrls));
            }
        } else {
            $output->writeln(__('<info>Magenest Cache Warmer is currently disabled!</info>'));
            $output->writeln(__('<info>Please enable the extension in Store Configuration.</info>'));
            $this->logger->info(__('Magenest Cache Warmer is currently disabled!'));
        }
    }
}