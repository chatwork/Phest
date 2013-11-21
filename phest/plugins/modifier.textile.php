<?php
    require_once(DIR_PHEST.'/lib/vendor/textile/classTextile.php');
    
function smarty_modifier_textile($string){
	$parser = new Textile();
	return $parser->textileThis($string);
}