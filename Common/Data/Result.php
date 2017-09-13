<?php
/**
 * Copyright 2017 Shopial. All rights reserved.
 */

namespace Magento\Social\Api\Data;

use Magento\Framework\DataObject;

/**
 * Class Result
 */
class Result extends DataObject implements ResultInterface
{
    public function getStatus()
    {
        return $this->_getData('status');
    }

    public function getErrorMessage()
    {
        return $this->_getData('error_message');
    }

    public function setStatus($status)
    {
        $this->setData('status', $status);
        return true;
    }

    public function setErrorMessage($message)
    {
        $this->setData('error_message', $message);
        return true;
    }
}
