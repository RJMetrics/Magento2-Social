<?php
/**
 * Copyright 2017 Shopial. All rights reserved.
 */

namespace Magento\Social\Common\Data;

use Magento\Framework\DataObject;
use Magento\Social\Api\Data\IntegrationStatusInterface;

/**
 * Class IntegrationStatus
 */
class IntegrationStatus extends DataObject implements IntegrationStatusInterface
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->_getData('name');
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return (int) $this->_getData('status');
    }

    /**
     * @inheritdoc
     */
    public function getEndpoint()
    {
        return $this->_getData('endpoint');
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->setData('name', $name);
        return true;
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
    public function setEndpoint($endpoint)
    {
        $this->setData('endpoint', $endpoint);
        return true;
    }
}
