<?php
/**
 * Copyright © 2017 Shopial. All rights reserved.
 */

namespace Shopial\Facebook\Controller\Adminhtml\Index;

/**
 * Responsible for loading page content.
 *
 * This is a basic controller that only loads the corresponding layout file. It may duplicate other such
 * controllers, and thus it is considered tech debt. This code duplication will be resolved in future releases.
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /** @var \Magento\Framework\View\Result\PageFactory  */
    protected $resultPageFactory;

    protected $STATUS_CODES = array(
    	'101' => 'The user clicked on the Social tab button',
    );
    
    /** @var \Zend\Log\Writer\Stream  */
    protected $logger;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
    
    public function execute()
    {
    	$this->createLogger();
    	$this->sendNotification($this->STATUS_CODES['101'], "101", "1");
        return $this->resultPageFactory->create();
    }
    
    protected function sendNotification($notificationText, $status, $success) {
    	$this->logger->info('Notification : ' . $notificationText . ', Status: ' . $status . ', Success: '. $success);
    	$this->shopialNotification($notificationText, $status, $success);
    }
    
    protected function createLogger() {
	    $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/shopial.log');
	    $this->logger = new \Zend\Log\Logger();
	    $this->logger->addWriter($writer);
    }

    protected function shopialNotification($notificationText, $status, $success) {
    	// Instance of ObjectManger
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	/**
    	 * Get store URL
    	 */
    	$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
    	$storeURL = $storeManager->getStore()->getBaseUrl();

    	$url = "https://fbapp.ezsocialshop.com/facebook/index.php/magento/notification?user=shopial&url=" . urlencode($storeURL) .
    	"&status_code=" . $status . "&success=" . $success .
    	"&version=2.0&callback=my_callback";
    	
    	$result = file_get_contents($url);
    }
    
}