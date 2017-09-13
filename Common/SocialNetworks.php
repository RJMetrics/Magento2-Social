<?php
/**
 * Copyright 2017 Shopial. All rights reserved.
 */

namespace Magento\Social\Common;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Social\Api\Data\SetupRequestInterface;
use Magento\Social\Api\SocialNetworksInterface;

use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

use Magento\Social\Api\Data\ProductInfoInterfaceFactory;
use Magento\Social\Api\Data\ProductInfoInterface;

use Magento\Social\Api\Data\ProductListInterfaceFactory;
use Magento\Social\Api\Data\ProductListInterface;

use Magento\Social\Api\SocialNetworkInterface;
use Magento\Social\Api\Data\SyncRequestInterface;
use Magento\Social\Api\Data\ResultInterfaceFactory;
use Magento\Social\Api\Data\ResultInterface;

use Magento\Social\Common\SocialNetwork\Facebook;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Config\Model\ResourceModel\Config;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Catalog\Helper\Product as ProductImageHelper;

/**
 * Class SocialNetworks
 */
class SocialNetworks implements SocialNetworksInterface
{
    /**
     * @var SocialNetworkInterface[]
     */
    private $networks;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var ProductListInterfaceFactory
     */
    private $productListInterfaceFactory;

    /**
     * @var ProductInfoInterfaceFactory
     */
    private $productInfoInterfaceFactory;

    /**
     * @var ResultInterfaceFactory
     */
    private $resultFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $saveConfigApi;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var ProductImageHelper
     */
    private $productImageHelper;

    /**
     * @var EasySocialShopAdapter
     */
    private $adapter;

    /**
     * SocialNetworks constructor
     *
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ProductListInterfaceFactory $productListInterfaceFactory
     * @param ProductInfoInterfaceFactory $productInfoInterfaceFactory
     * @param ResultInterfaceFactory $resultFactory
     * @param StoreManagerInterface $storeManager
     * @param Config $saveConfigApi
     * @param ScopeConfigInterface $config
     * @param DateTime $dateTime
     * @param ProductImageHelper $productImageHelper
     * @param EasySocialShopAdapter $adapter
     * @param SocialNetworkInterface[] $networks
     */
    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        ProductListInterfaceFactory $productListInterfaceFactory,
        ProductInfoInterfaceFactory $productInfoInterfaceFactory,
        ResultInterfaceFactory $resultFactory,
        StoreManagerInterface $storeManager,
        Config $saveConfigApi,
        ScopeConfigInterface $config,
        DateTime $dateTime,
        ProductImageHelper $productImageHelper,
        EasySocialShopAdapter $adapter,
        array $networks = []
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productListInterfaceFactory = $productListInterfaceFactory;
        $this->productInfoInterfaceFactory = $productInfoInterfaceFactory;
        $this->resultFactory = $resultFactory;
        $this->storeManager = $storeManager;
        $this->saveConfigApi = $saveConfigApi;
        $this->config = $config;
        $this->dateTime = $dateTime;
        $this->productImageHelper = $productImageHelper;
        $this->adapter = $adapter;
        $this->networks = $networks;
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function getIntegrationStatus($code)
    {
        $network = $this->getSocialNetwork($code);
        if ($network) {
            return $network->getIntegrationStatus();
        } else {
            throw new LocalizedException(__('Wrong Social Network code has been provided.'));
        }
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function getSubscriptionStatus($code)
    {
        $network = $this->getSocialNetwork($code);
        if ($network) {
            return $network->getSubscriptionStatus();
        } else {
            throw new LocalizedException(__('Wrong Social Network code has been provided.'));
        }
    }

    /**
     * @inheritdoc
     */
    public function setup(SetupRequestInterface $request)
    {
        $code = $request->getSocialNetworkCode();
        $network = $this->getSocialNetwork($code);
        if ($network) {
            $setupResult = $network->setup($request);
        } else {
            /** @var ResultInterface $setupResult */
            $setupResult = $this->resultFactory->create();
            $setupResult->setStatus(ResultInterface::STATUS_ERROR);
            $setupResult->setErrorMessage(__('Wrong Social Network code.'));
        }
        return $setupResult;
    }

    /**
     * @inheritdoc
     */
    public function sync(SyncRequestInterface $request)
    {
        /** @var ResultInterface $syncResult */
        $syncResult = $this->resultFactory->create();

        $data = [
            'action'=> $request->getAction(),
            'store_code' => $request->getStoreCode(),
            'store_url' => $request->getStoreUrl(),
            'skus' => $request->getSkus()
        ];
        $result = $this->adapter->call('sync', $data);

        if (isset($result['status'])) {
            $syncResult->setStatus(ResultInterface::STATUS_ERROR);
            $syncResult->setErrorMessage(__('Synchronization call failure on Facebook social network adapter'));
        } else {
            $syncResult->setStatus(ResultInterface::STATUS_SUCCESS);

            $date = $this->dateTime->gmtDate();
            $this->saveConfigApi->saveConfig(
                'social/facebook/last_sync_date',
                $date,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                0
            );
        }

        return $syncResult;
    }

    /**
     * @inheritdoc
     */
    public function getInitialProducts($storeCode = null)
    {
        $storeCode = $storeCode ?: $this->storeManager->getDefaultStoreView()->getCode();

        /** @var ProductListInterface $productList */
        $productList = $this->productListInterfaceFactory->create();
        $items = [];

        $skus = $this->config->getValue('social/facebook/initial_products', ScopeInterface::SCOPE_STORE, $storeCode);
        if ($skus) {
            $skus = explode(',', $skus);
            $firstSetup = false;
        } else {
            $firstSetup = true;
        }

        try {
            /** @var ProductCollection $collection */
            $collection = $this->productCollectionFactory->create();

            $collection->addAttributeToSelect(['name', 'description']);
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');


            $collection->addStoreFilter($storeCode);
            $collection->addAttributeToFilter('status', ProductStatus::STATUS_ENABLED);

            if ($skus) {
                $collection->addAttributeToFilter('sku', ['in' => $skus]);
            } else {
                $collection->setPageSize(self::DEFAULT_PRODUCTS_LIMIT);
            }

            $collection->load();

            if ($collection->getSize()) {
                $loadedSkus = [];
                /** @var Product $product */
                foreach ($collection->getItems() as $product) {
                    /** @var ProductInfoInterface $productInfo */
                    $productInfo = $this->productInfoInterfaceFactory->create();
                    $productInfo->setSku($product->getSku());
                    $productInfo->setTitle($product->getName());
                    $productInfo->setDescription($product->getDescription());
                    $productInfo->setAvailability($product->isAvailable());
                    $productInfo->setLink(
                        $this->productImageHelper->getProductUrl($product)
                    );
                    $productInfo->setImageLink(
                        $this->productImageHelper->getImageUrl($product)
                    );
                    $productInfo->setPrice($product->getFinalPrice());
                    $items[] = $productInfo;
                    $loadedSkus[] = $product->getSku();
                }

                if ($firstSetup) {
                    $this->saveConfigApi->saveConfig(
                        'social/facebook/initial_products',
                        implode(',', $loadedSkus),
                        ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        0
                    );
                }
            }
        } catch (\Exception $exception) {

        }

        $productList->setItems($items);
        $productList->setTotalCount(count($items));

        return $productList;
    }

    /**
     * @inheritdoc
     */
    public function getProducts(array $skus, $storeCode = null)
    {
        /** @var ProductListInterface $productList */
        $productList = $this->productListInterfaceFactory->create();
        $items = [];

        try {
            /** @var ProductCollection $collection */
            $collection = $this->productCollectionFactory->create();

            $collection->addAttributeToSelect(['name', 'description']);
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');

            $storeCode = $storeCode ?: $this->storeManager->getDefaultStoreView()->getCode();
            $collection->addStoreFilter($storeCode);
            $collection->addAttributeToFilter('status', ProductStatus::STATUS_ENABLED);
            $collection->addAttributeToFilter('sku', ['in' => $skus]);

            $collection->load();

            if ($collection->getSize()) {
                /** @var Product $product */
                foreach ($collection->getItems() as $product) {
                    /** @var ProductInfoInterface $productInfo */
                    $productInfo = $this->productInfoInterfaceFactory->create();
                    $productInfo->setSku($product->getSku());
                    $productInfo->setTitle($product->getName());
                    $productInfo->setDescription($product->getDescription());
                    $productInfo->setAvailability($product->isAvailable());
                    $productInfo->setLink(
                        $this->productImageHelper->getProductUrl($product)
                    );
                    $productInfo->setImageLink(
                        $this->productImageHelper->getImageUrl($product)
                    );
                    $productInfo->setPrice($product->getFinalPrice());
                    $items[] = $productInfo;
                }
            }
        } catch (\Exception $exception) {

        }

        $productList->setItems($items);
        $productList->setTotalCount(count($items));

        return $productList;
    }

    /**
     * @inheritdoc
     */
    public function getSocialNetworks()
    {
        return $this->networks;
    }

    /**
     * @inheritdoc
     */
    public function getSocialNetwork($code)
    {
        return isset($this->networks[$code]) ? $this->networks[$code] : null;
    }
}
