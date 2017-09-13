<?php
/**
 * Copyright 2017 Shopial. All rights reserved.
 */

namespace Magento\Social\Common\Data;

use Magento\Framework\DataObject;
use Magento\Social\Api\Data\SyncRequestInterface;

/**
 * Interface SyncRequestInterface
 */
class SyncRequest extends DataObject implements SyncRequestInterface
{
    public function getStoreCode()
    {
        return $this->_getData('store_code');
    }

    public function getStoreUrl()
    {
        return $this->_getData('store_url');
    }

    public function getAction()
    {
        return $this->_getData('action');
    }

    public function getSkus()
    {
        return $this->_getData('skus');
    }

    public function setStoreCode($storeCode)
    {
        $this->setData('store_code', $storeCode);
        return true;
    }

    public function setStoreUrl($storeUrl)
    {
        $this->setData('store_url', $storeUrl);
        return true;
    }

    public function setAction($action)
    {
        $this->setData('action', $action);
        return true;
    }

    public function setSkus(array $skus)
    {
        $this->setData('skus', $skus);
        return true;
    }
}
