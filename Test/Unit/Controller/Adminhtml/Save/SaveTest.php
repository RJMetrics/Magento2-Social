<?php
namespace Magestore\HelloMagento\Test\Unit\Model;
use Magestore\HelloMagento\Model\Save;
class SaveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HelloMessage
     */
    protected $helloMessage;
    
    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->helloMessage = $objectManager->getObject('Magestore\HelloMagento\Model\HelloMessage');
        $this->expectedMessage = 'Hello Magento 2! We will change the world!';
    }
    
    public function testGetMessage()
    {
         $this->assertEquals($this->expectedMessage, $this->helloMessage->getMessage());
    }
}