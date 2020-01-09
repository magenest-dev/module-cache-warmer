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

namespace Magenest\CacheWarmer\Controller\Adminhtml\Queue;

use Magenest\CacheWarmer\Helper\Config;
use Magenest\CacheWarmer\Model\ParallelCurl;
use Magenest\CacheWarmer\Model\Queue;
use Magento\Backend\App\Action;

class MassHit extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Ui\Component\MassAction\Filter $filter
     */
    private $filter;

    protected $_collectionFactory;
    protected $queue;
    protected $config;

    public function __construct(
        Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magenest\CacheWarmer\Model\ResourceModel\Queue\CollectionFactory $collectionFactory,
        Queue $queue,
        Config $config
    )
    {
        parent::__construct($context);
        $this->filter = $filter;
        $this->_collectionFactory = $collectionFactory;
        $this->queue = $queue;
        $this->config = $config;
    }

    public function execute()
    {
        if ($this->config->isModuleEnabled()) {
            try {
                $logCollection = $this->filter->getCollection($this->_collectionFactory->create());
                $itemsMissed = 0;
                $itemsHit = 0;
                $i = 0;
                foreach ($logCollection as $item) {
                    $result = $this->queue->customDequeue($item->getUrl());
                    if ($result[$i] == ParallelCurl::OK) {
                        $item->delete();
                        $itemsHit++;
                    } else {
                        $itemsMissed++;
                    }
                    $i++;
                }
                $this->messageManager->addSuccess(__('A total of %1 record(s) were cache hit.', $itemsHit));
                if ($itemsMissed > 0) {
                    $this->messageManager->addErrorMessage(__('A total of %1 record(s) were cache missed.', $itemsMissed));
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        } else {
            $this->messageManager->addErrorMessage(__('Magenest Cache Warmer is currently disabled!'));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_Cachewarmer::url_queue');
    }
}