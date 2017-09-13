<?php
/**
 * Copyright 2017 Shopial. All rights reserved.
 */

namespace Magento\Social\Api\Data;

/**
 * Interface SyncRequestInterface
 */
interface SyncRequestInterface
{
    /**
     * @return string
     */
    public function getStoreCode();

    /**
     * @return string
     */
    public function getStoreUrl();

    /**
     * @return string
     */
    public function getAction();

    /**
     * @return string[]
     */
    public function getSkus();

    /**
     * @param string $storeCode
     * @return bool
     */
    public function setStoreCode($storeCode);

    /**
     * @param string $storeUrl
     * @return bool
     */
    public function setStoreUrl($storeUrl);

    /**
     * @param string $action
     * @return bool
     */
    public function setAction($action);

    /**
     * @param string[] $skus
     * @return bool
     */
    public function setSkus(array $skus);
}
