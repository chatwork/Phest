<?php
/**
* Smarty PHPunit tests of modifier
*
* @package PHPunit
* @author Rodney Rehm
*/

/**
* class for modifier tests
*/
class PluginModifierCountCharactersTests extends PHPUnit_Framework_TestCase
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

    public function testDefault()
    {
        $result = "29";
        $tpl = $this->smarty->createTemplate('eval:{"Cold Wave Linked to Temperatures."|count_characters}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
    }

    public function testDefaultWithoutMbstring()
    {
        Smarty::$_MBSTRING = false;
        $result = "29";
        $tpl = $this->smarty->createTemplate('eval:{"Cold Wave Linked to Temperatures."|count_characters}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
        Smarty::$_MBSTRING = true;
    }

    public function testSpaces()
    {
        $result = "33";
        $tpl = $this->smarty->createTemplate('eval:{"Cold Wave Linked to Temperatures."|count_characters:true}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
    }

    public function testSpacesWithoutMbstring()
    {
        Smarty::$_MBSTRING = false;
        $result = "33";
        $tpl = $this->smarty->createTemplate('eval:{"Cold Wave Linked to Temperatures."|count_characters:true}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
        Smarty::$_MBSTRING = true;
    }

    public function testUmlauts()
    {
        $result = "29";
        $tpl = $this->smarty->createTemplate('eval:{"Cold Wäve Linked tö Temperatures."|count_characters}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
    }

    public function testUmlautsWithoutMbstring()
    {
        Smarty::$_MBSTRING = false;
        $result = "29";
        $tpl = $this->smarty->createTemplate('eval:{"Cold Wäve Linked tö Temperatures."|count_characters}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
        Smarty::$_MBSTRING = true;
    }

    public function testUmlautsSpaces()
    {
        $result = "33";
        $tpl = $this->smarty->createTemplate('eval:{"Cold Wäve Linked tö Temperatures."|count_characters:true}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
    }

    public function testUmlautsSpacesWithoutMbstring()
    {
        Smarty::$_MBSTRING = false;
        $result = "33";
        $tpl = $this->smarty->createTemplate('eval:{"Cold Wäve Linked tö Temperatures."|count_characters:true}');
        $this->assertNotEquals($result, $this->smarty->fetch($tpl));
        Smarty::$_MBSTRING = true;
    }
}
