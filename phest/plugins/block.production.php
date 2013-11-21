<?php
function smarty_block_production($params, $content, $template, &$repeat)
{
    if(!$repeat){
        if (isset($content)) {
        	if (PHEST_BUILTTYPE == 'production'){
        		return $content;
        	}else{
        		return '';
        	}
        }
    }
}