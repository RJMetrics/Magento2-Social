<?php
/**
 * Copyright 2017 Shopial. All rights reserved.
 */

namespace Magento\Social\Controller\Adminhtml\Facebook\Setup;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

use Magento\Social\Api\SocialNetworksInterface;
use Magento\Social\Common\SocialNetwork\Facebook;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Reset Facebook integration settings.
 */
class Reset extends AbstractAction
{
    /**
     * Authorization level name.
     */
    const SOCIAL_RESOURCE = 'Magento_Social::manage_facebook';

    /**
     * @var SocialNetworksInterface
     */
    private $socialNetworks;

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
     * @param SocialNetworksInterface $socialNetworks
     * @param StoreManagerInterface $storeManager
     * @param JsonFactory $resultFactory
     */
    public function __construct(
        Context $context,
        SocialNetworksInterface $socialNetworks,
        StoreManagerInterface $storeManager,
        JsonFactory $resultFactory
    ) {
        $this->socialNetworks = $socialNetworks;
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
        try {
            $fb = $this->socialNetworks->getSocialNetwork(Facebook::CODE);
            if ($fb) {
                $fb->reset();
            } else {
                $result = false;
                $message = __('Facebook integration adapter is not available.');
            }
        } catch (\Exception $e) {
            $result = false;
            $message = __('Something went wrong while deleting Facebook integration settings.');
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
