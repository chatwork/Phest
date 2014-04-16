<?php
/**
* Smarty PHPunit tests of delimiter
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for delimiter tests
*/
class DelimiterTests extends PHPUnit_Framework_TestCase
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
    * test <{ }> delimiter
    */
    public function testDelimiter1()
    {
        $this->smarty->left_delimiter = '<{';
        $this->smarty->right_delimiter = '}>';
        $tpl = $this->smarty->createTemplate('eval:<{* comment *}><{if true}><{"hello world"}><{/if}>');
        $this->assertEquals("hello world", $this->smarty->fetch($tpl));
    }
    /**
    * test <-{ }-> delimiter
    */
    public function testDelimiter2()
    {
        $this->smarty->left_delimiter = '<-{';
        $this->smarty->right_delimiter = '}->';
        $tpl = $this->smarty->createTemplate('eval:<-{* comment *}-><-{if true}-><-{"hello world"}-><-{/if}->');
        $this->assertEquals("hello world", $this->smarty->fetch($tpl));
    }
    /**
    * test <--{ }--> delimiter
    */
    public function testDelimiter3()
    {
        $this->smarty->left_delimiter = '<--{';
        $this->smarty->right_delimiter = '}-->';
        $tpl = $this->smarty->createTemplate('eval:<--{* comment *}--><--{if true}--><--{"hello world"}--><--{/if}-->');
        $this->assertEquals("hello world", $this->smarty->fetch($tpl));
    }
    /**
    * test {{ }} delimiter
    */
    public function testDelimiter4()
    {
        $this->smarty->left_delimiter = '{{';
        $this->smarty->right_delimiter = '}}';
        $tpl = $this->smarty->createTemplate('eval:{{* comment *}}{{if true}}{{"hello world"}}{{/if}}');
        $this->assertEquals("hello world", $this->smarty->fetch($tpl));
    }
    /**
    * test {= =} delimiter for conficts with option flags
    */
    public function testDelimiter5()
    {
        $this->smarty->left_delimiter = '{=';
        $this->smarty->right_delimiter = '=}';
        $tpl = $this->smarty->createTemplate('eval:{=assign var=foo value="hello world" nocache=}{=$foo=}');
        $this->assertEquals("hello world", $this->smarty->fetch($tpl));
    }
}
