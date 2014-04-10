<?php
    use \Michelf\Markdown;
    
function smarty_block_markdown($params, $content, $template, &$repeat)
{
    if(!$repeat){
        if (isset($content)) {
    		return Markdown::defaultTransform($content);
        }
    }
}