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

class Enqueue extends Command
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
        $this->setName(Information::ENQUEUE_COMMAND);
        $this->setDescription('Enqueue pages for cache warming.');

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
            $output->writeln(__('<info>Generating queue...</info>'));
            $this->logger->info(__('Generating queue...'));
            try {
                list($result, $generatedUrls) = $this->queue->enqueue();
                $endTime = microtime(true);
                if ($result) {
                    $output->writeln(__('<info>%1 pages are generated successfully.</info>', $generatedUrls));
                    $this->logger->info(__('%1 pages are generated successfully.', $generatedUrls));
                    $resultTime = $endTime - $startTime;
                    $output->writeln('<info>Done in ' . gmdate('H:i:s', $resultTime) . '</info>');
                    $this->logger->info(__('Done in %1', gmdate('H:i:s', $resultTime)));
                } else {
                    $output->writeln(__('<error>' . $generatedUrls . '</error>'));
                }
            } catch (\Exception $e) {
                $output->writeln(__('<error>' . $e->getMessage() . '</error>'));
            }
        } else {
            $output->writeln(__('<info>Magenest Cache Warmer is currently disabled!</info>'));
            $output->writeln(__('<info>Please enable the extension in Stores Configuration.</info>'));
        }
    }
}