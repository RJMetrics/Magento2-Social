<?php
/**
 * Copyright 2017 Shopial. All rights reserved.
 */

namespace Magento\Social\Api\Data;

/**
 * Interface SetupRequestInterface
 */
interface SetupRequestInterface
{
    /**
     * @return string
     */
    public function getSocialNetworkCode();

    /**
     * @return string
     */
    public function getStoreUrl();

    /**
     * @return string
     */
    public function getStoreCode();

    /**
     * @return string
     */
    public function getSettingId();

    /**
     * @return string
     */
    public function getPixelId();

    /**
     * @return string
     */
    public function getPageId();

    /**
     * @return string
     */
    public function getCatalogId();

    /**
     * @return string
     */
    public function getPageAccessToken();

    /**
     * @return string[]
     */
    public function getAdditionalData();

    /**
     * @param string $code
     * @return bool
     */
    public function setSocialNetworkCode($code);

    /**
     * @param string $storeUrl
     * @return bool
     */
    public function setStoreUrl($storeUrl);

    /**
     * @param string $storeCode
     * @return bool
     */
    public function setStoreCode($storeCode);

    /**
     * @param string $settingId
     * @return string
     */
    public function setSettingId($settingId);

    /**
     * @param string $pixelId
     * @return string
     */
    public function setPixelId($pixelId);

    /**
     * @param string $pageId
     * @return string
     */
    public function setPageId($pageId);

    /**
     * @param string $catalogId
     * @return string
     */
    public function setCatalogId($catalogId);

    /**
     * @param string $pageAccessToken
     * @return string
     */
    public function setPageAccessToken($pageAccessToken);

    /**
     * @param string[] $data
     *
     * @return bool
     */
    public function setAdditionalData(array $data);
}
