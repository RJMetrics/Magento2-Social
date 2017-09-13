<?php
/**
 * Copyright 2017 Shopial. All rights reserved.
 */

namespace Magento\Social\Common;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Store\Model\StoreManagerInterface;

use Magento\Social\Api\Data\SyncRequestInterfaceFactory;
use Magento\Social\Api\Data\SyncRequestInterface;

use Magento\Social\Api\SocialNetworksInterface;

/**
 * Class ProductSaveObserver
 */
class ProductSaveObserver implements ObserverInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SocialNetworksInterface
     */
    private $socialNetworksInterface;

    /**
     * @var SyncRequestInterfaceFactory
     */
    private $requestInterfaceFactory;

    /**
     * ProductUpdateObserver constructor
     *
     * @param StoreManagerInterface $storeManager
     * @param SocialNetworksInterface $socialNetworksInterface
     * @param SyncRequestInterfaceFactory $requestInterfaceFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        SocialNetworksInterface $socialNetworksInterface,
        SyncRequestInterfaceFactory $requestInterfaceFactory
    ) {
        $this->storeManager = $storeManager;
        $this->socialNetworksInterface = $socialNetworksInterface;
        $this->requestInterfaceFactory = $requestInterfaceFactory;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $storeCode = $this->storeManager->getDefaultStoreView()->getCode();
        $product = $observer->getEvent()->getProduct();

        try {
            /** @var SyncRequestInterface $request */
            $request = $this->requestInterfaceFactory->create();
            $request->setAction(SocialNetworksInterface::ACTION_SAVE);
            $request->setStoreCode($storeCode);
            $request->setStoreUrl($this->storeManager->getDefaultStoreView()->getBaseUrl());
            $request->setSkus([$product->getSku()]);

            $this->socialNetworksInterface->sync($request);
        } catch (\Exception $exception) {

        }
    }
}
