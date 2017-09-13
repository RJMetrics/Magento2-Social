<?php
/**
 * Copyright 2017 Shopial. All rights reserved.
 */

namespace Magento\Social\Controller\Adminhtml\Facebook;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Social\Api\SocialNetworksInterface;
use Magento\Social\Common\SocialNetwork\Facebook;

/**
 * Responsible for loading Facebook Shop management page content.
 */
class Manage extends \Magento\Backend\App\AbstractAction
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
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * @inheritdoc
     * @param SocialNetworksInterface $socialNetworks
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        SocialNetworksInterface $socialNetworks,
        PageFactory $resultPageFactory
    ) {
        $this->socialNetworks = $socialNetworks;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $fb = $this->socialNetworks->getSocialNetwork(Facebook::CODE);

        if (
            $fb->getIntegrationStatus()->getStatus() &&
            (!$fb || $fb->getSubscriptionStatus()->getStatus() !== Facebook::STATUS_UPGRADED)
        ) {
            $this->getMessageManager()->addNoticeMessage(
                __('Free plan - Not all of your products are being displayed in your Facebook shop.')
            );
        }

        return $this->resultPageFactory->create();
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(static::SOCIAL_RESOURCE);
    }
}
