<?php
/**
* Smarty PHPunit tests compilation of {nocache} tag
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for {nocache} tag tests
*/
class CompileNocacheTests extends PHPUnit_Framework_TestCase
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
    * test nocache tag caching disabled
    */
    public function testNocacheCachingNo()
    {
        $this->smarty->caching = 0;
        $this->smarty->assign('foo', 0);
        $this->smarty->assign('bar', 'A');
        $content = $this->smarty->fetch('test_nocache_tag.tpl');
        $this->assertContains("root 2A", $content);
        $this->assertContains("include 4A", $content);
        $this->smarty->assign('foo', 2);
        $this->smarty->assign('bar', 'B');
        $content = $this->smarty->fetch('test_nocache_tag.tpl');
        $this->assertContains("root 4B", $content);
        $this->assertContains("include 6B", $content);
    }
    /**
    * test nocache tag caching enabled
    */
    public function testNocacheCachingYes1()
    {
        $this->smarty->caching = 1;
        $this->smarty->cache_lifetime = 5;
        $this->smarty->assign('foo', 0);
        $this->smarty->assign('bar', 'A');
        $content = $this->smarty->fetch('test_nocache_tag.tpl');
        $this->assertContains("root 2A", $content);
        $this->assertContains("include 4A", $content);
    }
    public function testNocacheCachingYes2()
    {
        $this->smarty->caching = 1;
        $this->smarty->cache_lifetime = 5;

        $this->smarty->assign('foo', 2);
        $this->smarty->assign('bar', 'B');
        $content = $this->smarty->fetch('test_nocache_tag.tpl');
        $this->assertContains("root 4A", $content);
        $this->assertContains("include 6A", $content);
    }
}
