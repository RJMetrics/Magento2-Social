<?php
/**
 * Copyright © 2017 Shopial. All rights reserved.
 */
namespace Shopial\Facebook\Block;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Save extends \Magento\Framework\View\Element\Template
{
	/** @var The name of the user  */
	protected $userName;
	
    public function isUserExist()
    {
    	$this->userName = "shopial";
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	$userModel = $objectManager->create('Magento\User\Model\User');
    	$userModel->loadByUsername($this->userName);    	
    	$role_id_under_selected_user = $userModel->loadByUsername($this->userName)->getRoles();    	
    	if ($userModel->getData('password') == NULL || empty($role_id_under_selected_user)) {
    		return false;
    	} else {
    		return true;
    	}
    }

    public function getStoreURL()
    {
    	if ($this->isUserExist()) {
    		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    		$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
    		$storeURL = $storeManager->getStore()->getBaseUrl();    		
    		$url = "https://fbapp.ezsocialshop.com/facebook/index.php/magento/get_magento2?store_id=" . $storeURL . "&callback=my_callback";    		
    		$result = file_get_contents($url);
    		$result = trim($result);
    		return  $result;
    	}
    	return "";	
    }
}