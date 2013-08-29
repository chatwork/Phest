<?php
function smarty_block_production($params, $content, $template, &$repeat)
{
    if(!$repeat){
        if (isset($content)) {
        	if (SMARTBUILDER_BUILTTYPE == 'production'){
        		return $content;
        	}else{
        		return '';
        	}
        }
    }
}