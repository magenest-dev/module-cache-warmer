<?php

namespace Magenest\CacheWarmer\Model\ResourceModel;

class Queue extends
    \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function _construct()
    {
        $this->_init('magenest_cachewarmer_queue',
            'id');
    }

    public function truncate()
    {
        $this->getConnection()->truncateTable($this->getMainTable());
    }
}