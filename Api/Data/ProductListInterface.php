<?php
/**
 * Copyright 2017 Shopial. All rights reserved.
 */

namespace Magento\Social\Api\Data;

/**
 * Interface ProductListInterface
 */
interface ProductListInterface
{
    /**
     * @return ProductInfoInterface[] $items
     */
    public function getItems();

    /**
     * @return int
     */
    public function getTotalCount();

    /**
     * @param ProductInfoInterface[] $items
     *
     * @return bool
     */
    public function setItems(array $items);

    /**
     * @param int $count
     *
     * @return bool
     */
    public function setTotalCount($count);
}
