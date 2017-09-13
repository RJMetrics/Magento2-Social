<?php
/**
 * Copyright 2017 Shopial. All rights reserved.
 */

namespace Magento\Social\Api\Data;

/**
 * Interface SubscriptionStatusInterface
 */
interface SubscriptionStatusInterface
{
    /**
     * @return string
     */
    public function getStatus();

    /**
     * @return string
     */
    public function getSubscriptionDate();

    /**
     * @return string
     */
    public function getExpirationDate();

    /**
     * @return string
     */
    public function getDuration();

    /**
     * @param string $status
     * @return bool
     */
    public function setStatus($status);

    /**
     * @param string $subscriptionDate
     * @return bool
     */
    public function setSubscriptionDate($subscriptionDate);

    /**
     * @param string $expirationDate
     * @return bool
     */
    public function setExpirationDate($expirationDate);

    /**
     * @param string $duration
     * @return bool
     */
    public function setDuration($duration);
}
