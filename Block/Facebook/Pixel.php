<?php
/**
 * Copyright 2017 Shopial. All rights reserved.
 */

namespace Magento\Social\Block\Facebook;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Module\PackageInfo;
use Magento\Framework\View\Element\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Social\Api\SocialNetworksInterface;
use Magento\Framework\Registry;
use Magento\Search\Helper\Data;

/**
 * Class Pixel
 */
class Pixel extends Template
{
    /**
     * @var SocialNetworksInterface
     */
    private $socialNetworks;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var PackageInfo
     */
    private $packageInfo;

    /**
     * @var Data
     */
    private $searchHelper;

    /**
     * @param Context $context
     * @param SocialNetworksInterface $socialNetworks
     * @param Registry $registry
     * @param PackageInfo $packageInfo
     * @param Data $searchHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        SocialNetworksInterface $socialNetworks,
        Registry $registry,
        PackageInfo $packageInfo,
        Data $searchHelper,
        array $data = []
    ) {
        $this->socialNetworks = $socialNetworks;
        $this->registry = $registry;
        $this->packageInfo = $packageInfo;
        $this->searchHelper = $searchHelper;
        parent::__construct($context, $data);
    }

    protected function _beforeToHtml()
    {
        if ($this->getPixelId()) {
            return parent::_beforeToHtml();
        } else {
            return '';
        }
    }

    /**
     * @return string
     */
    public function getMagentoVersion()
    {
        return '2.2.x';
    }

    /**
     * @return string
     */
    public function getPluginVersion()
    {
        return $this->packageInfo->getVersion('Magento_Social');
    }

    /**
     * @return string
     */
    public function getFacebookAgentVersion()
    {
        return 'exmagento' . '-' . $this->getMagentoVersion() . '-' . $this->getPluginVersion();
    }

    /**
     * @return string
     */
    public function getPixelId()
    {
        return $this->_storeManager->getDefaultStoreView()->getConfig('social/facebook/pixel_id');
    }

    public function getProductIds()
    {
        $products[] = $this->registry->registry('current_product');

        return $this->prepareArray($products);
    }

    /**
     * @return string
     */
    public function getCurrentProductName()
    {
        return $this->getCurrentProduct() ? $this->getCurrentProduct()->getName() : '';
    }

    /**
     * @return string
     */
    public function getCurrentProductPrice()
    {
        return $this->getCurrentProduct() ? $this->getCurrentProduct()->getPrice() : '';
    }

    /**
     * @return string
     */
    public function getCurrentCategoryName()
    {
        return $this->getCurrentCategory() ? $this->getCurrentCategory()->getName() : '';
    }

    /**
     * @return bool
     */
    public function isAddToCartBtnAvailable()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getBaseCurrency()
    {
        return $this->_storeManager->getStore()->getBaseCurrencyCode();
    }

    /**
     * @return string
     */
    private function prepareArray($a)
    {
        return implode(',', array_map(function ($i) {
            return '"' . $i . '"';
        }, $a));
    }

    /**
     * @return ProductInterface
     */
    private function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * @return CategoryInterface
     */
    private function getCurrentCategory()
    {
        return $this->registry->registry('current_category');
    }

    /**
     * @return string
     */
    public function getProductPixelJson()
    {
        $data = [
            'source' => 'magento',
            'version' => $this->getMagentoVersion(),
            'pluginVersion' => $this->getPluginVersion(),

            'content_type' => 'product',
            'content_ids' => $this->getProductIds(),
            'content_name' => $this->getCurrentProductName(),
            'content_category' => $this->getCurrentCategoryName()
        ];
        if ($this->getCurrentProductPrice() && $this->getBaseCurrency()) {
            $data['value'] = $this->getCurrentProductPrice();
            $data['currency'] = $this->getBaseCurrency();
        }

        return json_encode($data);
    }

    /**
     * @return string
     */
    public function getCategoryPixelJson()
    {
        $data = [
            'source' => 'magento',
            'version' => $this->getMagentoVersion(),
            'pluginVersion' => $this->getPluginVersion(),

            'content_category' => $this->getCurrentCategoryName()
        ];

        return json_encode($data);
    }

    /**
     * @return string
     */
    public function getCheckoutPixelJson()
    {
        $data = [
            'source' => 'magento',
            'version' => $this->getMagentoVersion(),
            'pluginVersion' => $this->getPluginVersion(),

            'content_type' => 'product',
            'content_ids' => $this->getProductIds()
        ];
        if ($this->getCurrentProductPrice() && $this->getBaseCurrency()) {
            $data['value'] = $this->getCurrentProductPrice();
            $data['currency'] = $this->getBaseCurrency();
        }

        return json_encode($data);
    }

    /**
     * @return string
     */
    public function getSearchPixelJson()
    {
        $data = [
            'source' => 'magento',
            'version' => $this->getMagentoVersion(),
            'pluginVersion' => $this->getPluginVersion(),

            'query' => $this->searchHelper->getEscapedQueryText()
        ];

        return json_encode($data);
    }
}
