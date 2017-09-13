<?php
/**
 * Copyright 2017 Shopial. All rights reserved.
 */

namespace Magento\Social\Common\Data;

use Magento\Framework\DataObject;
use Magento\Social\Api\Data\SetupRequestInterface;

/**
 * Class SetupRequest
 */
class SetupRequest extends DataObject implements SetupRequestInterface
{
    public function getSocialNetworkCode()
    {
        return $this->_getData('code');
    }

    public function getStoreUrl()
    {
        return $this->_getData('store_url');
    }

    public function getStoreCode()
    {
        return $this->_getData('store_code');
    }

    public function getSettingId()
    {
        return $this->_getData('setting_id');
    }

    public function getPixelId()
    {
        return $this->_getData('pixel_id');
    }

    public function getPageId()
    {
        return $this->_getData('page_id');
    }

    public function getCatalogId()
    {
        return $this->_getData('catalog_id');
    }

    public function getPageAccessToken()
    {
        return $this->_getData('page_access_token');
    }

    public function getAdditionalData()
    {
        return $this->_getData('additional_data');
    }

    public function setSocialNetworkCode($code)
    {
        $this->setData('code', $code);
        return true;
    }

    public function setStoreUrl($storeUrl)
    {
        $this->setData('store_url', $storeUrl);
        return true;
    }

    public function setStoreCode($storeCode)
    {
        $this->setData('store_code', $storeCode);
        return true;
    }

    public function setSettingId($settingId)
    {
        $this->setData('setting_id', $settingId);
        return true;
    }

    public function setPixelId($pixelId)
    {
        $this->setData('pixel_id', $pixelId);
        return true;
    }

    public function setPageId($pageId)
    {
        $this->setData('pageId', $pageId);
        return true;
    }

    public function setCatalogId($catalogId)
    {
        $this->setData('catalog_id', $catalogId);
        return true;
    }

    public function setPageAccessToken($pageAccessToken)
    {
        $this->setData('page_access_token', $pageAccessToken);
        return true;
    }

    public function setAdditionalData(array $data)
    {
        $this->setData('additional_data', $data);
        return true;
    }
}
