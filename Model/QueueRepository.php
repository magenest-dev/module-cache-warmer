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

use Magenest\CacheWarmer\Api\Data\QueueRepositoryInterface;
use Magento\Framework\Message\ManagerInterface;

class QueueRepository implements QueueRepositoryInterface
{

    private $queueResource;
    protected $queueFactory;
    protected $messageManager;

    /**
     * QueueRepository constructor.
     * @param ResourceModel\Queue $queueResource
     * @param QueueFactory $queueFactory
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        \Magenest\CacheWarmer\Model\ResourceModel\Queue $queueResource,
        \Magenest\CacheWarmer\Model\QueueFactory $queueFactory,
        ManagerInterface $messageManager
    )
    {
        $this->queueFactory = $queueFactory;
        $this->queueResource = $queueResource;
        $this->messageManager = $messageManager;
    }

    public function add($url)
    {
        try {
            $queueItem = $this->queueFactory->create();
            $queueItem->setData('url', $url);
            $queueItem->save();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }

    public function delete($url)
    {
        try {
            $queueItem = $this->queueFactory->create();
            $queueItem->load($url, 'url')->delete();
            $queueItem->save();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }

    public function clear()
    {
        $this->queueResource->truncate();
    }
}