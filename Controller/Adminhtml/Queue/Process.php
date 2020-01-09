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

use Magenest\CacheWarmer\Block\Adminhtml\Button;
use Magenest\CacheWarmer\Helper\Config;
use Magenest\CacheWarmer\Model\Queue;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magenest\CacheWarmer\Block\Adminhtml\Information;

class Process extends Action
{
    /**
     * @var Queue
     */
    private $queue;
    protected $config;
    protected $output;
    protected $resultJsonFactory;

    /**
     * Enqueue constructor.
     * @param Context $context
     * @param Queue $queue
     * @param Config $config
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        Queue $queue,
        Config $config,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    )
    {
        parent::__construct($context);
        $this->queue = $queue;
        $this->config = $config;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $data = null;
        if ($this->getRequest()->isAjax()) {
            if ($this->config->isModuleEnabled()) {
                if ($this->getRequest()->getParams()['action'] == Button::DEQUEUE_ACTION) {
                    //@codingStandardsIgnoreStart
                    shell_exec('bin/magento ' . Information::DEQUEUE_COMMAND);
                    //@codingStandardsIgnoreEnd
                } else if ($this->getRequest()->getParams()['action'] == Button::ENQUEUE_ACTION) {
                    //@codingStandardsIgnoreStart
                    shell_exec('bin/magento ' . Information::ENQUEUE_COMMAND);
                    //@codingStandardsIgnoreEnd
                } else if ($this->getRequest()->getParams()['action'] == Button::STOP_ACTION) {
                    $this->queue->setStopFlag();
                }
            } else {
                $data['status'] = Information::MODULE_DISABLED;
            }
            return $resultJson->setData($data);
        }
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_CacheWarmer::url_queue');
    }
}
