<?php
    require_once(DIR_PHEST.'/lib/vendor/textile/classTextile.php');
    
function smarty_block_textile($params, $content, $template, &$repeat)
{
    if(!$repeat){
        if (isset($content)) {
			$parser = new Textile();
			return $parser->textileThis($content);
        }
    }
}