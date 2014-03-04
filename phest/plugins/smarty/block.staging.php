<?php
function smarty_block_staging($params, $content, $template, &$repeat)
{
    if(!$repeat){
        if (isset($content)) {
        	if (PHEST_BUILTTYPE == 'staging'){
        		return $content;
        	}else{
        		return '';
        	}
        }
    }
}