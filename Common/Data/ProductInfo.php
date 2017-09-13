<?php
/**
 * Copyright 2017 Shopial. All rights reserved.
 */

namespace Magento\Social\Common\Data;

use Magento\Framework\DataObject;
use Magento\Social\Api\Data\ProductInfoInterface;

/**
 * Interface ProductInfoInterface
 */
class ProductInfo extends DataObject implements ProductInfoInterface
{
    public function getSku()
    {
        return $this->_getData('sku');
    }

    public function getTitle()
    {
        return $this->_getData('title');
    }

    public function getDescription()
    {
        return $this->_getData('description');
    }

    public function getLink()
    {
        return $this->_getData('link');
    }

    public function getImageLink()
    {
        return $this->_getData('image_link');
    }

    public function getAvailability()
    {
        return $this->_getData('availability');
    }

    public function getPrice()
    {
        return $this->_getData('price');
    }

    public function setSku($sku)
    {
        $this->setData('sku', $sku);
        return true;
    }

    public function setTitle($title)
    {
        $this->setData('title', $title);
        return true;
    }

    public function setDescription($description)
    {
        $this->setData('description', $description);
        return true;
    }

    public function setLink($link)
    {
        $this->setData('link', $link);
        return true;
    }

    public function setImageLink($imageLink)
    {
        $this->setData('image_link', $imageLink);
        return true;
    }

    public function setAvailability($availability)
    {
        $this->setData('availability', $availability);
        return true;
    }

    public function setPrice($price)
    {
        $this->setData('price', $price);
        return true;
    }
}
