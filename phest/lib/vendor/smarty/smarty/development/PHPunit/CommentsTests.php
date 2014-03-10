<?php
/**
 * Smarty PHPunit tests comments in templates
 *
 * @package PHPunit
 * @author Uwe Tews
 */

/**
 * class for security test
 */
class CommentsTests extends PHPUnit_Framework_TestCase
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
     * test simple comments
     */
    public function testSimpleComment1()
    {
        $tpl = $this->smarty->createTemplate("eval:{* this is a comment *}");
        $this->assertEquals("", $this->smarty->fetch($tpl));
    }
    public function testSimpleComment2()
    {
        $tpl = $this->smarty->createTemplate("eval:{* another \$foo comment *}");
        $this->assertEquals("", $this->smarty->fetch($tpl));
    }
    public function testSimpleComment3()
    {
        $tpl = $this->smarty->createTemplate("eval:{* another  comment *}some in between{* another  comment *}");
        $this->assertEquals("some in between", $this->smarty->fetch($tpl));
    }
    public function testSimpleComment4()
    {
        $tpl = $this->smarty->createTemplate("eval:{* multi line \n comment *}");
        $this->assertEquals("", $this->smarty->fetch($tpl));
    }
    public function testSimpleComment5()
    {
        $tpl = $this->smarty->createTemplate("eval:{* /* foo * / *}");
        $this->assertEquals("", $this->smarty->fetch($tpl));
    }
    /**
     * test comment text combinations
     */
    public function testTextComment1()
    {
        $tpl = $this->smarty->createTemplate("eval:A{* comment *}B\nC");
        $this->assertEquals("AB\nC", $this->smarty->fetch($tpl));
    }
    public function testTextComment2()
    {
        $tpl = $this->smarty->createTemplate("eval:D{* comment *}\n{* comment *}E\nF");
        $this->assertEquals("D\nE\nF", $this->smarty->fetch($tpl));
    }
    public function testTextComment3()
    {
        $tpl = $this->smarty->createTemplate("eval:G{* multi \nline *}H");
        $this->assertEquals("GH", $this->smarty->fetch($tpl));
    }
    public function testTextComment4()
    {
        $tpl = $this->smarty->createTemplate("eval:I{* multi \nline *}\nJ");
        $this->assertEquals("I\nJ", $this->smarty->fetch($tpl));
    }
}
