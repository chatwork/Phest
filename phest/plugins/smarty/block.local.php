<?php
function smarty_block_local($params, $content, $template, &$repeat)
{
    if(!$repeat){
        if (isset($content)) {
        	if (PHEST_BUILTTYPE == 'local'){
        		return $content;
        	}else{
        		return '';
        	}
        }
    }
}