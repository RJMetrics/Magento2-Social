<?php
/**
 * Copyright 2017 Shopial. All rights reserved.
 */

namespace Magento\Social\Api;

use Magento\Social\Api\Data\SetupRequestInterface;
use Magento\Social\Api\Data\SyncRequestInterface;
use Magento\Social\Api\Data\ResultInterface;
use Magento\Social\Api\Data\SubscriptionStatusInterface;
use Magento\Social\Api\Data\IntegrationStatusInterface;

/**
 * Interface SocialNetworkInterface
 */
interface SocialNetworkInterface
{
    /**
     * Unknown subscription status reference code.
     */
    const STATUS_UNKNOWN = 'unknown';

    /**
     * @return SubscriptionStatusInterface
     */
    public function getSubscriptionStatus();

    /**
     * @return IntegrationStatusInterface
     */
    public function getIntegrationStatus();

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
     * @return bool
     */
    public function reset();
}
