<?php

namespace Magenest\CacheWarmer\Model\ResourceModel\Queue;
/**
 * Subscription Collection
 */
class Collection extends
    \Magento\Framework\Model\ResourceModel\Db\Collection\
    AbstractCollection
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    protected $_idFieldName = 'id';

    public function _construct()
    {
        $this->_init('Magenest\CacheWarmer\Model\Queue',
            'Magenest\CacheWarmer\Model\ResourceModel\Queue');
    }
}