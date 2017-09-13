<?php
/**
 * Copyright 2017 Shopial. All rights reserved.
 */

namespace Magento\Social\Block\Adminhtml\Facebook;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Integration\Model\Integration;
use Magento\Social\Api\SocialNetworksInterface;
use Magento\Social\Common\SocialNetwork\Facebook;
use Magento\Integration\Api\IntegrationServiceInterface;

/**
 * Class Manage
 */
class Manage extends Template
{
    /**
     * @var SocialNetworksInterface
     */
    private $socialNetworks;

    /**
     * @var IntegrationServiceInterface
     */
    private $integrationService;

    /**
     * @var array
     */
    private $sampleProducts;

    /**
     * @param Context $context
     * @param SocialNetworksInterface $socialNetworks
     * @param IntegrationServiceInterface $integrationService
     * @param array $data
     */
    public function __construct(
        Context $context,
        SocialNetworksInterface $socialNetworks,
        IntegrationServiceInterface $integrationService,
        array $data = []
    ) {
        $this->socialNetworks = $socialNetworks;
        $this->integrationService = $integrationService;
        parent::__construct($context, $data);
    }

    /**
     * Prepare layout
     *
     * @return Template
     */
    protected function _prepareLayout()
    {
        if (!$this->isIntegrated()) {
            $url = $this->getUrl('adminhtml/integration');
            $this->getToolbar()->addChild(
                'integration_button',
                'Magento\Social\Block\Adminhtml\Widget\Action',
                [
                    'label' => __('Integration page'),
                    'class' => 'action action-secondary social-action-integrate',
                    'onclick' => "openSocialIntPopup('{$url}')"
                ]
            );
        } else {
            if ($this->getPixelId()) {
                $url = "https://www.facebook.com/";
                $this->getToolbar()->addChild(
                    'go_to_shop_button',
                    'Magento\Social\Block\Adminhtml\Widget\Action',
                    [
                        'label' => __('Go To Shop'),
                        'class' => 'social-facebook-go-to-shop',
                        'onclick' => "openSocialShopPopup('{$url}')"
                    ]
                );

                if (!$this->isUpgraded()) {
                    $url = "https://fbapp.ezsocialshop.com/facebook/index.php/magento2/loginEmailPermissioned?store_url=" .
                           urlencode($this->getBaseUrl());
                    $this->getToolbar()->addChild(
                        'upgrade_button',
                        'Magento\Social\Block\Adminhtml\Widget\Action',
                        [
                            'label' => __('Upgrade'),
                            'class' => 'action action-secondary social-action-upgrade',
                            'target' => '_blank',
                            'onclick' => "openSocialUpgradePopup('{$url}')"
                        ]
                    );
                }

                $url = "https://www.facebook.com/ads/manager/creation?store_url=" .
                       urlencode($this->getBaseUrl());
                $this->getToolbar()->addChild(
                    'create_ad_button',
                    'Magento\Social\Block\Adminhtml\Widget\Action',
                    [
                        'label' => __('Create Ad'),
                        'class' => 'action action-primary social-facebook-create-ad',
                        'onclick' => "openSocialAdPopup('{$url}')"
                    ]
                );
            } else {
                $this->getToolbar()->addChild(
                    'connect_button',
                    'Magento\Social\Block\Adminhtml\Widget\Action',
                    [
                        'label' => __('Connect To Facebook'),
                        'class' => 'action action-primary social-facebook-action-connect',
                        'onclick' => "openSocialFbPopup()"
                    ]
                );
            }
        }

        return parent::_prepareLayout();
    }

    /**
     * @return bool
     */
    public function isIntegrated()
    {
        $integration = $this->integrationService->findByName(SocialNetworksInterface::ESS_CONSUMER_NAME);
        if ($integration) {
            return $integration->getStatus() !== Integration::STATUS_INACTIVE;
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    public function isUpgraded()
    {
        $network = $this->socialNetworks->getSocialNetwork(Facebook::CODE);

        return $network ? $network->getSubscriptionStatus()->getStatus() == Facebook::STATUS_UPGRADED : false;
    }

    /**
     * @return string
     */
    public function getStoreCode()
    {
        return $this->_storeManager->getDefaultStoreView()->getConfig('social/facebook/store_code')
            ?: $this->_storeManager->getDefaultStoreView()->getCode();
    }

    /**
     * @return string
     */
    public function getStoreType()
    {
        return $this->isUpgraded() ? __('Unlimited') : __('Free');
    }

    /**
     * @return int
     */
    public function getConnectedProductsCount()
    {
        return $this->isUpgraded()
            ? $this->getTotalVisibleProducts()
            : min($this->getTotalVisibleProducts(), SocialNetworksInterface::DEFAULT_PRODUCTS_LIMIT);
    }

    /**
     * @return mixed
     */
    public function getBaseCurrency()
    {
        return $this->_storeManager->getStore()->getBaseCurrencyCode();
    }

    /**
     * @return string
     */
    public function getStoreName()
    {
        return $this->_storeManager->getStore()->getName();
    }

    /**
     * @return int|string
     * @todo implement mapping to https://developers.facebook.com/docs/marketing-api/reference/ad-account/timezone-ids
     */
    public function getStoreTimezone()
    {
        return 0;
    }

    /**
     * @return string
     */
    public function getSaveSettingsUrl()
    {
        return $this->getUrl('social/facebook_setup/settings');
    }

    /**
     * @return string
     */
    public function getResetUrl()
    {
        return $this->getUrl('social/facebook_setup/reset');
    }

    /**
     * @return string
     */
    public function getSendProductFeedUrl()
    {
        return $this->getUrl('social/facebook_setup/feed');
    }

    /**
     * @return string
     */
    public function getIntegrationStatusUrl()
    {
        return $this->getUrl('social/facebook_status/integration');
    }

    /**
     * @return string
     */
    public function getSubscriptionStatusUrl()
    {
        return $this->getUrl('social/facebook_status/subscription');
    }

    /**
     * @return mixed
     */
    public function getSettingId()
    {
        return $this->_storeManager->getDefaultStoreView()->getConfig('social/facebook/setting_id');
    }

    /**
     * @return mixed
     */
    public function getPixelId()
    {
        return $this->_storeManager->getDefaultStoreView()->getConfig('social/facebook/pixel_id');
    }

    /**
     * @return mixed
     */
    public function getLastSyncDate()
    {
        $time = $this->formatTime(
            $this->_storeManager->getDefaultStoreView()->getConfig('social/facebook/last_sync_date'),
            \IntlDateFormatter::SHORT
        );
        $date = $this->formatDate(
            $this->_storeManager->getDefaultStoreView()->getConfig('social/facebook/last_sync_date'),
            \IntlDateFormatter::LONG
        );
        return $time . ', ' . $date;
    }

    /**
     * @return string
     */
    public function getMagentoVersion()
    {
        return '2.2.x';
    }

    /**
     * @return int
     */
    public function getTotalVisibleProducts()
    {
        return count($this->getSampleProducts());
    }

    /**
     * @return array|null
     */
    public function getSampleProducts()
    {
        if ($this->sampleProducts === null) {
            $this->sampleProducts = [];

            $initialProducts = $this->socialNetworks->getInitialProducts();
            foreach ($initialProducts->getItems() as $product) {
                $this->sampleProducts[] = [
                    'id' => $product->getSku(),
                    'title' => $product->getTitle(),
                    'description' => $product->getDescription(),
                    'link' => $product->getLink(),
                    'image_link' => $product->getImageLink(),
                    'availability' => $product->getAvailability(),
                    'price' => $product->getPrice()
                ];
            }
        }

        return $this->sampleProducts;
    }

    /**
     * @return string
     */
    public function getFbConfigurationJson()
    {
        $config = [
            'fbConfig' => [
                'popupOrigin' => 'https://www.facebook.com/ads/dia',
                'platform' => 'Magento 2',
                'pixel' => [
                    'pixelId' => $this->getPixelId(),
                    'advanced_matching_supported' => true
                ],
                'store' => [
                    'baseUrl' => $this->getBaseUrl(),
                    'baseCurrency' => $this->getBaseCurrency(),
                    'timezoneId' => $this->getStoreTimezone(),
                    'storeName' => $this->getStoreName(),
                    'version' => $this->getMagentoVersion()
                ],
                'feed' => [
                    'totalVisibleProducts' => $this->getTotalVisibleProducts()
                ],
                'feedPrepared' => [
                    'samples' => $this->getSampleProducts()
                ]
            ],
            'localConfig' => [
                'saveSettingsUrl' => $this->getSaveSettingsUrl(),
                'resetUrl' => $this->getResetUrl(),
                'integrationStatusUrl' => $this->getIntegrationStatusUrl(),
                'subscriptionStatusUrl' => $this->getSubscriptionStatusUrl(),
            ]
        ];

        if ($this->getSettingId()) {
            $config['diaSettingId'] = $this->getSettingId();
        }

        return json_encode($config);
    }
}
