<?php
/**
 * Copyright 2017 Shopial. All rights reserved.
 */

namespace Magento\Social\Controller\Adminhtml\Facebook\Setup;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Social\Api\SocialNetworksInterface;
use Magento\Social\Common\SocialNetwork\Facebook;

use Magento\Social\Api\Data\SetupRequestInterfaceFactory;
use Magento\Social\Api\Data\SetupRequestInterface;

use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Store Facebook integration settings to system configuration.
 */
class Settings extends \Magento\Backend\App\AbstractAction
{
    /**
     * Authorization level name.
     */
    const SOCIAL_RESOURCE = 'Magento_Social::manage_facebook';

    /**
     * @var Config
     */
    private $saveConfigApi;

    /**
     * @var SocialNetworksInterface
     */
    private $socialNetworks;

    /**
     * @var SetupRequestInterfaceFactory
     */
    private $requestInterfaceFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var JsonFactory
     */
    private $jsonResultFactory;

    /**
     * @inheritdoc
     * @param Config $saveConfigApi
     * @param SocialNetworksInterface $socialNetworks
     * @param SetupRequestInterfaceFactory $requestInterfaceFactory
     * @param StoreManagerInterface $storeManager
     * @param JsonFactory $resultFactory
     */
    public function __construct(
        Context $context,
        Config $saveConfigApi,
        SocialNetworksInterface $socialNetworks,
        SetupRequestInterfaceFactory $requestInterfaceFactory,
        StoreManagerInterface $storeManager,
        JsonFactory $resultFactory
    ) {
        $this->saveConfigApi = $saveConfigApi;
        $this->socialNetworks = $socialNetworks;
        $this->requestInterfaceFactory = $requestInterfaceFactory;
        $this->storeManager = $storeManager;
        $this->jsonResultFactory = $resultFactory;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $result = true;
        $message = '';
        $values = $this->getRequest()->getParams();

        try {
            $fb = $this->socialNetworks->getSocialNetwork(Facebook::CODE);
            if ($fb) {
                $storeCode = $this->storeManager->getDefaultStoreView()->getConfig('social/facebook/store_code')
                    ?: $this->storeManager->getDefaultStoreView()->getCode();
                $store = $this->storeManager->getStore($storeCode);

                /** @var SetupRequestInterface $request */
                $request =  $this->requestInterfaceFactory->create();
                $request->setStoreUrl($store->getBaseUrl());
                $request->setStoreCode($store->getCode());
                $request->setSettingId(
                    isset($values['setting_id'])
                        ? $values['setting_id']
                        : $store->getConfig('social/facebook/setting_id')
                );
                $request->setPixelId(
                    isset($values['pixel_id'])
                        ? $values['pixel_id']
                        : $store->getConfig('social/facebook/pixel_id')
                );
                $request->setPageId(
                    isset($values['page_id'])
                        ? $values['page_id']
                        : $store->getConfig('social/facebook/page_id')
                );
                $request->setCatalogId(
                    isset($values['catalog_id'])
                        ? $values['catalog_id']
                        : $store->getConfig('social/facebook/catalog_id')
                );
                $request->setPageAccessToken(
                    isset($values['page_access_token'])
                        ? $values['page_access_token']
                        : $store->getConfig('social/facebook/page_access_token')
                );
                $this->socialNetworks->setup($request);
            } else {
                $result = false;
                $message = __('Facebook integration adapter is not available.');
            }

        } catch (\Exception $e) {
            $result = false;
            $message = __('Something went wrong while saving Facebook integration settings.');
        }

        $jsonResult = $this->jsonResultFactory->create();
        $jsonResult->setData([
            'success' => $result,
            'message' => $message
        ]);

        return $jsonResult;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(static::SOCIAL_RESOURCE);
    }
}
