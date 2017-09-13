<?php
/**
 * Copyright 2017 Shopial. All rights reserved.
 */

namespace Magento\Social\Api\Data;

/**
 * Interface ProductInfoInterface
 */
interface ProductInfoInterface
{
    /**
     * @return string
     */
    public function getSku();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return string
     */
    public function getLink();

    /**
     * @return string
     */
    public function getImageLink();

    /**
     * @return string
     */
    public function getAvailability();

    /**
     * @return string
     */
    public function getPrice();

    /**
     * @param string $sku
     * @return bool
     */
    public function setSku($sku);

    /**
     * @param string $title
     * @return bool
     */
    public function setTitle($title);

    /**
     * @param string $description
     * @return bool
     */
    public function setDescription($description);

    /**
     * @param string $link
     * @return bool
     */
    public function setLink($link);

    /**
     * @param string $imageLink
     * @return bool
     */
    public function setImageLink($imageLink);

    /**
     * @param string $availability
     * @return bool
     */
    public function setAvailability($availability);

    /**
     * @param string $price
     * @return bool
     */
    public function setPrice($price);
}
