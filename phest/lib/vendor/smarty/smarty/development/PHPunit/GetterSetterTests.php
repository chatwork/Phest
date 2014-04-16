<?php
/**
* Smarty PHPunit tests of generic getter/setter
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for generic getter/setter tests
*/
class GetterSetterTests extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
    }

    static function isRunnable()
    {
        return true;
    }

    /**
    * test setter on Smarty object
    */
    public function testSmartySetter()
    {
        $this->smarty->setLeftDelimiter('<{');
        $this->smarty->setRightDelimiter('}>');
        $this->assertEquals('<{', $this->smarty->left_delimiter);
        $this->assertEquals('}>', $this->smarty->right_delimiter);
    }
    /**
    * test getter on Smarty object
    */
    public function testSmartyGetter()
    {
        $this->smarty->setLeftDelimiter('<{');
        $this->smarty->setRightDelimiter('}>');
        $this->assertEquals('<{', $this->smarty->getLeftDelimiter());
        $this->assertEquals('}>', $this->smarty->getRightDelimiter());
    }
    /**
    * test setter on Template object
    */
    public function testTemplateSetter()
    {
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        $tpl->setLeftDelimiter('<{');
        $tpl->setRightDelimiter('}>');
        $this->assertEquals('<{', $tpl->smarty->left_delimiter);
        $this->assertEquals('}>', $tpl->smarty->right_delimiter);
        $this->assertEquals('{', $this->smarty->left_delimiter);
        $this->assertEquals('}', $this->smarty->right_delimiter);
    }
    /**
    * test getter on Template object
    */
    public function testTemplateGetter()
    {
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        $tpl->setLeftDelimiter('<{');
        $tpl->setRightDelimiter('}>');
        $this->assertEquals('<{', $tpl->getLeftDelimiter());
        $this->assertEquals('}>', $tpl->getRightDelimiter());
    }
}
