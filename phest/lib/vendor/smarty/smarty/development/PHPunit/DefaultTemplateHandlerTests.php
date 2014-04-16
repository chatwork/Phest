<?php
/**
* Smarty PHPunit tests deault template handler
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for block plugin tests
*/
class DefaultTemplateHandlerTests extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
        $this->smarty->force_compile = true;
        $this->smarty->disableSecurity();
    }

    static function isRunnable()
    {
        return true;
    }

    /**
    * test error on unknow template
    */
    public function testUnknownTemplate()
    {
        try {
            $this->smarty->fetch('foo.tpl');
        } catch (Exception $e) {
            $this->assertContains('Unable to load template', $e->getMessage());

            return;
        }
        $this->fail('Exception for none existing template has not been raised.');
    }
    /**
    * test error on registration on none existent handler function.
    */
    public function testRegisterNoneExistentHandlerFunction()
    {
        try {
            $this->smarty->registerDefaultTemplateHandler('foo');
        } catch (Exception $e) {
            $this->assertContains("Default template handler 'foo' not callable", $e->getMessage());

            return;
        }
        $this->fail('Exception for none callable function has not been raised.');
    }
    /**
    * test replacement by default template handler
    */
/**
    public function testDefaultTemplateHandlerReplacement()
    {
        $this->smarty->register->defaultTemplateHandler('my_template_handler');
        $this->assertEquals("Recsource foo.tpl of type file not found", $this->smarty->fetch('foo.tpl'));
    }
*/
    public function testDefaultTemplateHandlerReplacementByTemplateFile()
    {
        $this->smarty->registerDefaultTemplateHandler('my_template_handler_file');
        $this->assertEquals("hello world", $this->smarty->fetch('foo.tpl'));
    }
    /**
    * test default template handler returning fals
    */
    public function testDefaultTemplateHandlerReturningFalse()
    {
        $this->smarty->registerDefaultTemplateHandler('my_false');
        try {
            $this->smarty->fetch('foo.tpl');
        } catch (Exception $e) {
            $this->assertContains('Unable to load template', $e->getMessage());

            return;
        }
        $this->fail('Exception for none existing template has not been raised.');
    }

}

function my_template_handler ($resource_type, $resource_name, &$template_source, &$template_timestamp, Smarty $smarty)
{
    $output = "Recsource $resource_name of type $resource_type not found";
    $template_source = $output;
    $template_timestamp = time();

    return true;
}
function my_template_handler_file ($resource_type, $resource_name, &$template_source, &$template_timestamp, Smarty $smarty)
{
    return $smarty->getTemplateDir(0) . 'helloworld.tpl';
}
function my_false ($resource_type, $resource_name, &$template_source, &$template_timestamp, Smarty $smarty)
{
    return false;
}
