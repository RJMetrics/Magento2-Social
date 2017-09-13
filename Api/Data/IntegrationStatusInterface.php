<?php
/**
 * Copyright 2017 Shopial. All rights reserved.
 */

namespace Magento\Social\Api\Data;

/**
 * Interface IntegrationStatusInterface
 */
interface IntegrationStatusInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @return string
     */
    public function getEndpoint();

    /**
     * @param string $name
     * @return bool
     */
    public function setName($name);

    /**
     * @param string $status
     * @return bool
     */
    public function setStatus($status);

    /**
     * @param string $endpoint
     * @return bool
     */
    public function setEndpoint($endpoint);
}
