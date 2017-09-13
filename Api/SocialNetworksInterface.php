<?php
/**
 * Copyright 2017 Shopial. All rights reserved.
 */

namespace Magento\Social\Api;

use Magento\Social\Api\Data\ProductListInterface;
use Magento\Social\Api\Data\SetupRequestInterface;
use Magento\Social\Api\Data\SubscriptionStatusInterface;
use Magento\Social\Api\Data\IntegrationStatusInterface;
use Magento\Social\Api\Data\SyncRequestInterface;
use Magento\Social\Api\Data\ResultInterface;

/**
 * Interface SocialConnectInterface
 */
interface SocialNetworksInterface
{
    /**
     * Save action reference code
     */
    const ACTION_SAVE = 'save';

    /**
     * Delete action reference code
     */
    const ACTION_DELETE = 'delete';

    /**
     * Setup action reference code
     */
    const ACTION_SETUP = 'setup';

    /**
     * Status action reference code
     */
    const ACTION_STATUS = 'status';

    /**
     * oAuth integration consumer name
     */
    const ESS_CONSUMER_NAME = 'Magento Social';

    /**
     * Number of products synced to Facebook for free
     */
    const DEFAULT_PRODUCTS_LIMIT = 9;

    /**
     * @param string $code
     *
     * @return IntegrationStatusInterface
     */
    public function getIntegrationStatus($code);

    /**
     * @param string $code
     *
     * @return SubscriptionStatusInterface
     */
    public function getSubscriptionStatus($code);

    /**
     * @param SetupRequestInterface $request
     *
     * @return ResultInterface
     */
    public function setup(SetupRequestInterface $request);

    /**
     * @param SyncRequestInterface $request
     *
     * @return ResultInterface
     */
    public function sync(SyncRequestInterface $request);

    /**
     * @param string $storeCode
     *
     * @return ProductListInterface
     */
    public function getInitialProducts($storeCode = null);

    /**
     * @param array $skus
     * @param string $storeCode
     *
     * @return ProductListInterface
     */
    public function getProducts(array $skus, $storeCode = null);

    /**
     * @return SocialNetworkInterface[]
     */
    public function getSocialNetworks();

    /**
     * @param string $code
     *
     * @return SocialNetworkInterface|null
     */
    public function getSocialNetwork($code);
}
