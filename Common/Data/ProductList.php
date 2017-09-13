<?php
/**
 * Copyright 2017 Shopial. All rights reserved.
 */

namespace Magento\Social\Common\Data;

use Magento\Social\Api\Data\ProductInfoInterface;
use Magento\Social\Api\Data\ProductListInterface;

/**
 * Interface ProductList
 */
class ProductList implements ProductListInterface
{
    /**
     * @var ProductInfoInterface[]
     */
    private $items = [];

    /**
     * @var int
     */
    private $count = 0;

    /**
     * @inheritdoc
     */
    public function getItems()
    {
        return $this->items;
    }

    public function getTotalCount()
    {
        return $this->count;
    }

    /**
     * @param array $items
     * @return bool
     */
    public function setItems(array $items)
    {
        $this->items = $items;
        return true;
    }

    /**
     * @param int $count
     * @return bool
     */
    public function setTotalCount($count)
    {
        $this->count = $count;
        return true;
    }
}
