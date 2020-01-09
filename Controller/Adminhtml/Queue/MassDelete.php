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
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;

class MassDelete extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Ui\Component\MassAction\Filter $filter
     */
    private $filter;

    protected $_collectionFactory;

    protected $config;

    public function __construct(
        Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magenest\CacheWarmer\Model\ResourceModel\Queue\CollectionFactory $collectionFactory,
        Config $config
    )
    {
        parent::__construct($context);
        $this->filter = $filter;
        $this->_collectionFactory = $collectionFactory;
        $this->config = $config;
    }

    public function execute()
    {
        if ($this->config->isModuleEnabled()) {
            try {
                $logCollection = $this->filter->getCollection($this->_collectionFactory->create());
                $itemsDeleted = 0;
                foreach ($logCollection as $item) {
                    $item->delete();
                    $itemsDeleted++;
                }
                $this->messageManager->addSuccess(__('A total of %1 record(s) were deleted.', $itemsDeleted));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        } else {
            $this->messageManager->addErrorMessage(__('Magenest Cache Warmer is currently disabled!!!'));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_Cachewarmer::url_queue');
    }
}