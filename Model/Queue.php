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
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;
use Magento\Framework\App\CacheInterface;
use Magenest\CacheWarmer\Setup\InstallSchema;

class Queue extends \Magento\Framework\Model\AbstractModel
{
    const OK = 200;
    const PAGE_SIZE = 160;
    const CACHE_DATA = 'In Operation';
    const CACHE_KEY = 'magenest_cache_warmer';
    const CACHE_TAG = 'magenest_cache_warmer';
    const STOP_CACHE_DATA = 'Operation Halt';
    const STOP_CACHE_KEY = 'magenest_cache_stop';
    const STOP_CACHE_TAG = 'magenest_cache_stop';
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var UrlRewriteCollectionFactory
     */
    protected $urlCollection;
    /**
     * @var QueueFactory
     */
    protected $queueFactory;
    /**
     * @var \Magenest\CacheWarmer\Api\Data\QueueRepositoryInterface
     */
    protected $queueRepository;
    /**
     * @var \Magenest\CacheWarmer\Api\ParallelCurlInterface
     */
    protected $parallelCurl;
    /**
     * @var \Magenest\CacheWarmer\Model\ResourceModel\Queue\CollectionFactory
     */
    protected $queueCollectionFactory;
    /**
     * @var Config
     */
    protected $config;
    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $_cache;
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * Queue constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param UrlRewriteCollectionFactory $urlRewriteCollectionFactory
     * @param QueueFactory $queueFactory
     * @param \Magenest\CacheWarmer\Api\Data\QueueRepositoryInterface $queueRepository
     * @param \Magenest\CacheWarmer\Api\ParallelCurlInterface $parallelCurl
     * @param ResourceModel\Queue\CollectionFactory $queueCollectionFactory
     * @param Config $config
     * @param CacheInterface $cache
     * @param ResourceConnection $resourceConnection
     * @param AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        UrlRewriteCollectionFactory $urlRewriteCollectionFactory,
        \Magenest\CacheWarmer\Model\QueueFactory $queueFactory,
        \Magenest\CacheWarmer\Api\Data\QueueRepositoryInterface $queueRepository,
        \Magenest\CacheWarmer\Api\ParallelCurlInterface $parallelCurl,
        \Magenest\CacheWarmer\Model\ResourceModel\Queue\CollectionFactory $queueCollectionFactory,
        Config $config,
        CacheInterface $cache,
        ResourceConnection $resourceConnection,
        AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->resourceConnection = $resourceConnection;
        $this->_cache = $cache;
        $this->_storeManager = $storeManager;
        $this->queueRepository = $queueRepository;
        $this->queueFactory = $queueFactory;
        $this->urlCollection = $urlRewriteCollectionFactory;
        $this->parallelCurl = $parallelCurl;
        $this->queueCollectionFactory = $queueCollectionFactory;
        $this->config = $config;
        parent::__construct($context, $registry, $resource,
            $resourceCollection, $data);
    }

    public function _construct()
    {
        $this->_init
        ('Magenest\CacheWarmer\Model\ResourceModel\Queue');
    }

    protected function lock()
    {
        if ($this->_cache->load(self::CACHE_KEY)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Another Operation is already running. Please wait.'));
        }
        $this->_cache->save(self::CACHE_DATA, self::CACHE_KEY, [self::CACHE_TAG], 360);
    }

    protected function unlock()
    {
        $this->_cache->clean([self::CACHE_TAG]);
    }

    public function setStopFlag()
    {
        $this->_cache->save(self::STOP_CACHE_DATA, self::STOP_CACHE_KEY, [self::STOP_CACHE_TAG], 190);
    }

    private function getStopFlag()
    {
        $flag = $this->_cache->load(self::STOP_CACHE_KEY);
        return isset($flag) && !empty($flag);
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
    }

    /**
     * @return string
     */
    protected function getAdditionalUrls()
    {
        $additionalUrlsConfig = $this->config->getAdditionalUrls();
        $additionalUrlsString = '';
        if (!empty($additionalUrlsConfig)) {
            $additionalUrlsArray = explode("\r\n", $additionalUrlsConfig);
            foreach($additionalUrlsArray as $additionalUrl){
                $additionalUrlsString .= "('".$additionalUrl."'),";
            }
            $additionalUrlsString = rtrim($additionalUrlsString, ", ");
        }
        return $additionalUrlsString;
    }

    /**
     * @param $item
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function customEnqueue($item)
    {
        try {
            $this->queueRepository->add($item);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function enqueue()
    {
        $this->lock();
        try {
            $generatedUrls = 0;
            $additionalUrls = $this->getAdditionalUrls();
            $baseUrl = $this->getBaseUrl();
            try {
                $this->queueRepository->clear();
            } catch (\Exception $e) {
                return [false, $generatedUrls];
            }
            $connection  = $this->resourceConnection->getConnection();
            $collection = $this->queueCollectionFactory->create();
            $urlRewriteTable = $collection->getTable('url_rewrite');
            $queueTable = $collection->getTable(InstallSchema::TABLE_NAME);
            if(!empty($additionalUrls)) {
                $sql = "INSERT INTO " . $queueTable . " (url) VALUES " . $additionalUrls;
                $result = $connection->query($sql);
                $generatedUrls += $result->rowCount();
            }
            $sql = "INSERT INTO " .$queueTable. " (url) SELECT CONCAT ('" .$baseUrl. "', request_path) FROM " .$urlRewriteTable. " WHERE `redirect_type` = 0 GROUP BY `request_path`";
            $result = $connection->query($sql);
            $generatedUrls += $result->rowCount();
        } catch (\Exception $e) {
            $this->unlock();
            return [false, $e->getMessage()];
        }
        $this->unlock();
        return [true, $generatedUrls];
    }

    public function customDequeue($item)
    {
        try {
            $result = $this->parallelCurl->sendSingleCurl($item);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return $result;
    }

    /**
     * @param null $batchSize
     * @return array
     */
    public function dequeue($batchSize = null)
    {
        try {
            $missedUrls = 0;
            $processedUrls = 0;
            $offset = 0;
            $maxRequests = $this->config->getMaxRequests();
            $connection  = $this->resourceConnection->getConnection();
            $collection = $this->queueCollectionFactory->create();
            $queueTable = $collection->getTable(InstallSchema::TABLE_NAME);
            if (empty($batchSize)){
                $batchSize = $this->getQueueSize();
            }
            if ($batchSize < self::PAGE_SIZE) {
                $pageSize = $batchSize;
            } else {
                $pageSize = self::PAGE_SIZE;
            }
            do {
                $sql = "SELECT `url` FROM " .$queueTable. " LIMIT ".$pageSize. " OFFSET ".$offset;
                $data = $connection->fetchAll($sql);
                if (!empty($data)) {
                    $result = $this->parallelCurl->sendMultipleCurl($data, $maxRequests);
                    for ($d = 0; $d < count($result); $d++) {
                        if ($result[$d] == ParallelCurl::OK) {
                             if (isset($data[$d])) {
                                $this->queueRepository->delete($data[$d]);
                            }
                            $processedUrls++;
                            if ($processedUrls >= $batchSize) {
                                break 2;
                            }
                        } else {
                            $missedUrls++;
                            $offset++;
                        }
                    }
                }
            } while ($processedUrls <= $batchSize && !$this->getStopFlag());
        } catch (\Exception $e) {
            return [false, $processedUrls, $e->getMessage()];
        }
        return [true, $processedUrls, $missedUrls];
    }

    public function getQueueSize()
    {
        return $this->queueCollectionFactory->create()->getSize();
    }
}