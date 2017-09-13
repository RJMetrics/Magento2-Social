<?php
/**
 * Copyright 2017 Shopial. All rights reserved.
 */

namespace Magento\Social\Api\Data;

/**
 * Interface ResultInterface
 */
interface ResultInterface
{
    /**
     * Successful call result status code
     */
    const STATUS_SUCCESS = 'success';

    /**
     * Failed call result status code
     */
    const STATUS_ERROR = 'error';

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @return string
     */
    public function getErrorMessage();

    /**
     * @param string $status
     * @return bool
     */
    public function setStatus($status);

    /**
     * @param string $message
     * @return bool
     */
    public function setErrorMessage($message);
}
