<?php
/**
 * Copyright 2017 Shopial. All rights reserved.
 */

namespace Magento\Social\Common\SocialNetwork;

use Magento\Social\Api\SocialNetworkInterface;

use Magento\Social\Api\Data\SetupRequestInterface;
use Magento\Social\Api\Data\SyncRequestInterface;
use Magento\Social\Api\Data\ResultInterfaceFactory;
use Magento\Social\Api\Data\ResultInterface;
use Magento\Social\Api\Data\SubscriptionStatusInterfaceFactory;
use Magento\Social\Api\Data\SubscriptionStatusInterface;
use Magento\Social\Api\Data\IntegrationStatusInterfaceFactory;
use Magento\Social\Api\Data\IntegrationStatusInterface;

use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Social\Api\SocialNetworksInterface;
use Magento\Social\Common\EasySocialShopAdapter;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Config\Model\ResourceModel\Config;

/**
 * Class FacebookConnect
 */
class Facebook implements SocialNetworkInterface
{
    /**
     * Social network unique code
     */
    const CODE = 'facebook';

    /**
     * Upgraded subsription status reference code.
     */
    const STATUS_UPGRADED = 'upgraded';

    /**
     * @var array
     */
    private $settings = ['setting_id', 'pixel_id', 'catalog_id', 'page_access_token'];

    /**
     * @var ResultInterfaceFactory
     */
    private $resultFactory;

    /**
     * @var SubscriptionStatusInterfaceFactory
     */
    private $subscriptionStatusFactory;

    /**
     * @var IntegrationStatusInterfaceFactory
     */
    private $integrationStatusFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $saveConfigApi;

    /**
     * @var IntegrationServiceInterface
     */
    private $integrationService;

    /**
     * @var EasySocialShopAdapter
     */
    private $adapter;

    /**
     * @var SubscriptionStatusInterface
     */
    private $subscriptionStatus;

    /**
     * @var IntegrationStatusInterface
     */
    private $integrationStatus;

    /**
     * Facebook constructor
     *
     * @param ResultInterfaceFactory $resultFactory
     * @param SubscriptionStatusInterfaceFactory $subscriptionStatusFactory
     * @param IntegrationStatusInterfaceFactory $integrationStatusFactory
     * @param StoreManagerInterface $storeManager
     * @param Config $saveConfigApi
     * @param IntegrationServiceInterface $integrationService
     * @param EasySocialShopAdapter $adapter
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        SubscriptionStatusInterfaceFactory $subscriptionStatusFactory,
        IntegrationStatusInterfaceFactory $integrationStatusFactory,
        StoreManagerInterface $storeManager,
        Config $saveConfigApi,
        IntegrationServiceInterface $integrationService,
        EasySocialShopAdapter $adapter
    ) {
        $this->resultFactory = $resultFactory;
        $this->subscriptionStatusFactory = $subscriptionStatusFactory;
        $this->integrationStatusFactory = $integrationStatusFactory;
        $this->storeManager = $storeManager;
        $this->saveConfigApi = $saveConfigApi;
        $this->integrationService = $integrationService;
        $this->adapter = $adapter;
    }

    /**
     * @inheritdoc
     */
    public function getSubscriptionStatus()
    {
        if ($this->subscriptionStatus === null) {
            $this->subscriptionStatus = $this->subscriptionStatusFactory->create();

            $storeCode = $this->storeManager->getDefaultStoreView()->getConfig('social/facebook/store_code')
                ?: $this->storeManager->getDefaultStoreView()->getCode();

            $payload = [
                'store_url' => $this->storeManager->getStore($storeCode)->getBaseUrl()
            ];

            $result = $this->adapter->call('status', $payload);

            if (!isset($result['error']) && isset($result['is_upgrade'])) {
                $status = ($result['is_upgrade']) ? Facebook::STATUS_UPGRADED : Facebook::STATUS_UNKNOWN;
                $this->subscriptionStatus->setStatus($status);
                $this->subscriptionStatus->setSubscriptionDate( $result['update_time']);
                $this->subscriptionStatus->setExpirationDate( $result['end_time']);
                $this->subscriptionStatus->setDuration( $result['duration']);
            }
        }
        return $this->subscriptionStatus;
    }

    /**
     * @inheritdoc
     */
    public function getIntegrationStatus()
    {
        if ($this->integrationStatus === null) {
            $integration = $this->integrationService
                ->findByName(SocialNetworksInterface::ESS_CONSUMER_NAME);
            if ($integration) {
                $data = [
                    'name' => $integration->getName(),
                    'status' => $integration->getStatus()
                ];
            } else {
                $data = [];
            }

            $this->integrationStatus = $this->integrationStatusFactory->create(
                [
                    'data' => $data
                ]
            );
        }
        return $this->integrationStatus;
    }

    /**
     * @inheritdoc
     */
    public function setup(SetupRequestInterface $request)
    {
        /** @var ResultInterface $setupResult */
        $setupResult = $this->resultFactory->create();
        $errorMessage = null;
        $status = ResultInterface::STATUS_SUCCESS;

        $storeCode = $this->storeManager->getDefaultStoreView()->getConfig('social/facebook/store_code')
            ?: $this->storeManager->getDefaultStoreView()->getCode();
        $store = $this->storeManager->getStore($storeCode);

        try {
            $data = [
                'setting_id' => $request->getSettingId(),
                'pixel_id' => $request->getPixelId(),
                'page_id' => $request->getPageId(),
                'catalog_id' => $request->getCatalogId(),
                'page_access_token' => $request->getPageAccessToken()
            ];

            foreach ($data as $key => $value) {
                if (isset($values[$key])) {
                    $this->saveConfigApi->saveConfig(
                        'social/facebook/' . $key,
                        $value,
                        ScopeInterface::SCOPE_STORE,
                        $store->getId()
                    );
                }
            }
        } catch (\Exception $e) {
            $status = ResultInterface::STATUS_ERROR;
            $errorMessage = __('Something went wrong while sending Facebook integration settings to Easy shop gateway.');
        }

        if (!empty($data['page_access_token'])) {
            try {
                $payload = [
                    'token' => $request->getPageAccessToken(),
                    'catalog_id' => $request->getCatalogId(),
                    'page_id' => $request->getPageId(),
                    'store_url' => $store->getBaseUrl()
                ];
                $result = $this->adapter->call('setup', $payload);
                if (isset($result['error'])) {
                    $status = ResultInterface::STATUS_ERROR;
                    $errorMessage = __('Easy Social Shop setup call failure.');
                }
            } catch (\Exception $e) {
                $status = ResultInterface::STATUS_ERROR;
                $errorMessage = __('Something went wrong while sending Facebook token to Easy Social Shop.');
            }
        }

        $setupResult->setStatus($status);
        if ($errorMessage) {
            $setupResult->setErrorMessage($errorMessage);
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

        try {
            $storeCode = $this->storeManager->getDefaultStoreView()->getConfig('social/facebook/store_code')
                ?: $this->storeManager->getDefaultStoreView()->getCode();

            $payload = [
                'action' => $request->getAction(),
                'scope' => Facebook::CODE,
                'skus' => $request->getSkus(),
                'store_url' => $this->storeManager->getStore($storeCode)->getBaseUrl()
            ];

            $result = $this->adapter->call('sync', $payload);

            if (isset($result['error'])) {
                $syncResult->setStatus(ResultInterface::STATUS_ERROR);
                $syncResult->setErrorMessage(__('Synchronization call failure on Facebook social network adapter'));
            } else {
                $syncResult->setStatus(ResultInterface::STATUS_SUCCESS);
            }
        } catch (\Exception $exception) {
            $syncResult->setStatus(ResultInterface::STATUS_ERROR);
            $syncResult->setErrorMessage(__('Synchronization call failure.'));
        }

        return $syncResult;
    }

    /**
     * @inheritdoc
     */
    public function reset()
    {
        try {
            $storeCode = $this->storeManager->getDefaultStoreView()->getConfig('social/facebook/store_code')
                ?: $this->storeManager->getDefaultStoreView()->getCode();
            $store = $this->storeManager->getStore($storeCode);

            foreach ($this->settings as $key => $value) {
                $this->saveConfigApi->deleteConfig(
                    'social/facebook/' . $key,
                    ScopeInterface::SCOPE_STORE,
                    $store->getId()
                );
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
