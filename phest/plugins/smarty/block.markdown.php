<?php
    use \Michelf\Markdown;
    require_once(DIR_PHEST.'/lib/vendor/phpmarkdown/Michelf/Markdown.php');    
    
function smarty_block_markdown($params, $content, $template, &$repeat)
{
    if(!$repeat){
        if (isset($content)) {
    		return Markdown::defaultTransform($content);
        }
    }
}