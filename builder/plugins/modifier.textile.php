<?php
    require_once(DIR_BUILDER.'/lib/vendor/textile/classTextile.php');
    
function smarty_modifier_textile($string){
	$parser = new Textile();
	return $parser->textileThis($string);
}