<?php
/**
 * Copyright 2017 Shopial. All rights reserved.
 */

namespace Magento\Social\Controller\Adminhtml\Facebook\Status;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

use Magento\Social\Api\SocialNetworksInterface;
use Magento\Social\Common\SocialNetwork\Facebook;

/**
 * Get subscription status.
 */
class Subscription extends AbstractAction
{
    /**
     * Authorization level name.
     */
    const SOCIAL_RESOURCE = 'Magento_Social::status';

    /**
     * @var SocialNetworksInterface
     */
    private $socialNetworks;

    /**
     * @var JsonFactory
     */
    private $jsonResultFactory;

    /**
     * @inheritdoc
     * @param SocialNetworksInterface $socialNetworks
     * @param JsonFactory $resultFactory
     */
    public function __construct(
        Context $context,
        SocialNetworksInterface $socialNetworks,
        JsonFactory $resultFactory
    ) {
        $this->socialNetworks = $socialNetworks;
        $this->jsonResultFactory = $resultFactory;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        try {
            $fb = $this->socialNetworks->getSocialNetwork(Facebook::CODE);
            if ($fb) {
                $status = $fb->getSubscriptionStatus();
                $result = [
                    'status' => $status->getStatus()
                ];
            } else {
                $result = [
                    'error' => true,
                    'message' =>  __('Facebook integration adapter is not available.')
                ];
            }
        } catch (\Exception $e) {
            $result = [
                'error' => true,
                'message' =>  __('Something went wrong while getting Facebook subscription status.')
            ];
        }
        $jsonResult = $this->jsonResultFactory->create();
        $jsonResult->setData($result);

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
