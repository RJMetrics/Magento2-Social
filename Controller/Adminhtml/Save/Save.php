<?php
/**
 * Copyright © 2017 Shopial. All rights reserved.
 */

namespace Shopial\Facebook\Controller\Adminhtml\Save;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Authorization\Model\Role\Interceptor;

class Save extends \Magento\Framework\App\Action\Action
{
    /** @var \Magento\Framework\View\Result\PageFactory  */
    protected $resultPageFactory;
	
    protected $STATUS_CODES = array(
    	'101' => 'The user clicked on the Social tab button', 
    	'102' => 'Connect Store button was clicked',
		'103' => 'The user was created in Magento database', 
    	'104' => 'The role was created in Magento database', 
    	'105' => 'The user was saved in Shopial database'
    );
    
    /** @var \Zend\Log\Writer\Stream  */
    protected $logger;
    
    /** @var The name of the user  */
    protected $userName;
    
    /** @var The url of the user  */
    protected $storeURL;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
    
    public function execute()
    {
    	$this->userName = "shopial";
    	// Instance of ObjectManger
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	/**
    	 * Get store URL
    	 */
    	$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
    	$this->storeURL = $storeManager->getStore()->getBaseUrl();
    	
    	// Loading the logger
    	$this->createLogger();
    	// Sent notification to magento server and to shopial
    	$this->sendNotification($this->STATUS_CODES['102'], "102", "1");
    	
    	$userModel = $objectManager->create('Magento\User\Model\User');
    	$userModel->loadByUsername($this->userName);
    	
    	if ($userModel->getData('password') == NULL) {
			try {
	    		/**
	    		 * Create new user called shopial
	    		 */
	    		$userModel->setUserName($this->userName);
	    		$userModel->setFirstName($this->userName);
	    		$userModel->setLastName($this->userName);
	    		$userModel->setEmail("support@magento.com");
	    		$randPassword = $this->generateRandomString();
	    		$userModel->setPassword($randPassword);
	    		$userModel->save();	    		
	    		if($userModel->getId() > 0) {
	    			$result = $this->saveUserToShopial($this->userName, $randPassword);
	    			$this->sendNotification($this->STATUS_CODES['103'], "103", "1");
	    		} else {
	    			$this->sendNotification($this->STATUS_CODES['103'], "103", "0");
	    		}	    		
    		} catch (Exception $e) {
    			$this->sendNotification($this->STATUS_CODES['103'], "103", "0");
    		}
    	} else {
    		$result = "Success"; 		
    	}
		
    	// Check the roles for our new user
    	$role_for_selected_user = $userModel->loadByUsername($this->userName)->getRoles();
    	
    	if (empty($role_for_selected_user)) {
    		try {
	    		/**
	    		 * Create shopial role
	    		 */
	    		$roleAuthModel = $objectManager->create('\Magento\Authorization\Model\Role');
	    		
	    		$roleAuthModel->setName($this->userName)
	    		->setPid("0")
	    		->setRoleType("G")
	    		->setUserType(UserContextInterface::USER_TYPE_ADMIN);
	    		$roleAuthModel->save();
	    		
	    		$resource=['Magento_Backend::admin',
	    		'Magento_Rma::magento_rma',
	    		'Magento_Sales::actions',
	    		'Magento_Sales::actions_view',
	    		'Magento_Sales::sales',
	    		'Magento_Sales::sales_operation',
	    		'Magento_Sales::sales_order'
	    		];
	    		
	    		$roleAuthModel->setRoleId($roleAuthModel->getId())->setResources($resource)->save();
	    		
	    		/**
	    		 * Adding new role to our shopial user
	    		 */
	    		if ($roleAuthModel->getId() > 0) {
		    		$userModel->setRoleId($roleAuthModel->getId());
		    		$userModel->save();
		    		$this->sendNotification($this->STATUS_CODES['104'], "104", "1");
	    		} else {
	    			$this->sendNotification($this->STATUS_CODES['104'], "104", "0");
	    		}
    		} catch (Exception $e) {
    			$this->sendNotification($this->STATUS_CODES['104'], "104", "0");
    		}
    	}

    	/**
    	 * Return JSON (
    	 * 	result => Error/Success
    	 * 	store_url => Store URL
    	 * 	)
    	 */
    	
    	$isJsonSuccess = "0";
    	if ($result != "-1") {
    		$isJsonSuccess = "1";
    	}
    	$this->sendNotification($this->STATUS_CODES['105'], "105", $isJsonSuccess);
    	
    	$jsonData = array();
    	$jsonData['result'] = $result;
    	$jsonData['store_url'] = "https://fbapp.ezsocialshop.com/facebook/index.php/magento2/loginEmailPermissioned?mid=" . $result;
    	echo json_encode($jsonData);
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
    	$url = "https://fbapp.ezsocialshop.com/facebook/index.php/magento/notification?user=" . $this->userName . "&url=" . urlencode($this->storeURL) .
    	"&status_code=" . $status . "&success=" . $success . 
    	"&version=2.0&callback=my_callback";
    	 
    	$result = file_get_contents($url);
    	$result = trim($result);
    	return  $result;
    }
    
    protected function saveUserToShopial($userName, $randPassword) {
    	$url = "https://fbapp.ezsocialshop.com/facebook/index.php/magento/save_magento2?user=" . $userName .
		"&pass=" . $randPassword . "&store_id=" . urlencode($this->storeURL) . "&callback=my_callback";
    	
    	$result = file_get_contents($url);
    	$result = trim($result);
    	return  $result;
    }
    
    protected function generateRandomString() {
    	return substr(md5(time()),1,10);
    }
}