<?php

class Ultimate_Connect_Adminhtml_Connect_ConnectController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        $this->loadLayout();
        $this->_title(Mage::helper('ultimate_connect')->__('Ultimate Connect'))
             ->_title(Mage::helper('ultimate_connect')->__('Connects'));
        $this->renderLayout();
    }

	public function doconnectAction(){
		
		$userdata=$this->CreateRoleAndUser();
		$userdata['store_id'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . "index.php";
		
		$url = "https://fbapp.ezsocialshop.com/facebook/index.php/magento/save?user=" . $userdata['username'] . "&pass=" . $userdata['api_key'] . "&store_id=" . $userdata['store_id'];
		$result = file_get_contents($url);
		$result = trim($result);
		
		$jsonData = array();
		$jsonData['result'] = $result;
		$jsonData['store_id'] = $userdata['store_id'];
		//$jsonData['url'] = $url;
		
		echo json_encode($jsonData);		
	}
	
	protected function CreateRoleAndUser(){
		
		$role = Mage::getModel('api/roles')->load('Easy Shop', 'role_name');
		
		if(!$role->getId())
		{
			$role = Mage::getModel('api/roles')
			->setName('Easy Shop')
			->setRoleType('G')
			->save();

			Mage::getModel("api/rules")
			->setRoleId($role->getId())
			->setResources(array('__root__', 'catalog', 'catalog/product', 'catalog/product/info', 'catalog/product/media','catalog/category','catalog/category/info'))
			->saveRel();
		}
		$user=Mage::getModel('api/user')->loadByUsername('easysocialshop');
		
		if(!$user->getUserId()){
		
			$api_key=$this->generateRandomString();
			$data = array(
			'username' => 'easysocialshop',
			'firstname' => 'Easy',
			'lastname' => 'Shop',
			'email' => 'info@easysocialshop.com',
			'api_key' => $api_key,
			'api_key_confirmation' => $api_key,
			'is_active' => '1',
			);
			$user = Mage::getModel('api/user')->setData($data)->save();
			$user->setRoleIds(array($role->getId()))
				->setRoleUserId($user->getUserId())
				->saveRelations();
				
			// sotre un encripted format of password, so that can be used in reconnect.
			Mage::getModel('core/config')->saveConfig('ultimate/connect/api_key', $api_key);
			Mage::app()->getCacheInstance()->flush();
		}
		
		
		// Load permissions of the predefined API role
		/** @var Varien_Data_Collection_Db $rulesCollection */
		$rulesCollection = Mage::getModel('api/rules')->getCollection();
		$rulesCollection->addFieldToFilter('role_id', $role->getId());
		$rulesCollection->addFieldToFilter('api_permission', 'allow');
		$aclResources = $rulesCollection->getColumnValues('resource_id');

		// Add permission of listing stores for the predefined API role
		$aclResources = array_merge($aclResources, array(
						'core',
						'core/store',
						'core/store/list',
						));
		
		Mage::getModel("api/rules")
			->setRoleId($role->getId())
			->setResources($aclResources)
			->saveRel();

		$user->setRoleIds(array($role->getId()))
			->setRoleUserId($user->getUserId())
			->saveRelations();

			
		$password=Mage::getStoreConfig('ultimate/connect/api_key');
		
		return array('username'=>$user->getUsername(),'api_key'=>$api_key);
		
	}

	protected function generateRandomString() {		
		return md5(time());		
    //return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);	
	}

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('ultimate_connect/connect');
    }
}
