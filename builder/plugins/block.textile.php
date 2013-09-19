<?php
function smarty_block_textile($params, $content, $template, &$repeat)
{
    if(!$repeat){
        if (isset($content)) {
    		return textile($content);
        }
    }
}