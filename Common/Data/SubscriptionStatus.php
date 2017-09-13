<?php
/**
 * Copyright 2017 Shopial. All rights reserved.
 */

namespace Magento\Social\Common\Data;

use Magento\Framework\DataObject;
use Magento\Social\Api\Data\SubscriptionStatusInterface;

/**
 * Interface SubscriptionStatusInterface
 */
class SubscriptionStatus extends DataObject implements SubscriptionStatusInterface
{
    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->_getData('status');
    }

    /**
     * @inheritdoc
     */
    public function getSubscriptionDate()
    {
        return $this->_getData('subscription_date');
    }

    /**
     * @inheritdoc
     */
    public function getExpirationDate()
    {
        return $this->_getData('expiration_date');
    }

    /**
     * @inheritdoc
     */
    public function getDuration()
    {
        return $this->_getData('duration');
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        $this->setData('status', $status);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function setSubscriptionDate($subscriptionDate)
    {
        $this->setData('subscription_date', $subscriptionDate);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function setExpirationDate($expirationDate)
    {
        $this->setData('expiration_date', $expirationDate);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function setDuration($duration)
    {
        $this->setData('duration', $duration);
        return true;
    }
}
