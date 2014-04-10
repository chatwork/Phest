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
class PluginModifierRegexReplaceTests extends PHPUnit_Framework_TestCase
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
        $tpl = $this->smarty->createTemplate('eval:{"Infertility unlikely to\nbe passed on, experts say."|regex_replace:"/[\r\t\n]/":" "}');
        $this->assertEquals("Infertility unlikely to be passed on, experts say.", $this->smarty->fetch($tpl));
    }

    public function testDefaultWithoutMbstring()
    {
        Smarty::$_MBSTRING = false;
        $tpl = $this->smarty->createTemplate('eval:{"Infertility unlikely to\nbe passed on, experts say."|regex_replace:"/[\r\t\n]/":" "}');
        $this->assertEquals("Infertility unlikely to be passed on, experts say.", $this->smarty->fetch($tpl));
        Smarty::$_MBSTRING = true;
    }

    public function testUmlauts()
    {
        $tpl = $this->smarty->createTemplate('eval:{"Infertility unlikely tö\näe passed on, experts say."|regex_replace:"/[\r\t\n]/u":" "}');
        $this->assertEquals("Infertility unlikely tö äe passed on, experts say.", $this->smarty->fetch($tpl));

        $tpl = $this->smarty->createTemplate('eval:{"Infertility unlikely tä be passed on, experts say."|regex_replace:"/[ä]/ue":"ae"}');
        $this->assertEquals("Infertility unlikely tae be passed on, experts say.", $this->smarty->fetch($tpl));
    }
}
