<?php

class Ultimate_Connect_Block_Adminhtml_Connect extends Mage_Adminhtml_Block_Template
{

    public function __construct()
    {
	
		$this->setTemplate('ultimate_connect/index.phtml');
		
    }

    public function checkMyRoleUser(){    	
    	$user=Mage::getModel('api/user')->loadByUsername('easysocialshop');    	
    	if ($user->getUserId()) {
    		return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
    	} else {
    		return "noUser";
    	}
    }
    
	public function getConnectButton()
	{
		
			$button = Mage::app()->getLayout()->createBlock('adminhtml/widget_button');
			$button->setData(array(
				'onclick'=>'doconnect()',
				'type' => 'button',
				'class' => 'button',
				'label' => $this->__('Connect'),
			));

			return $button->toHtml();
	}
}
