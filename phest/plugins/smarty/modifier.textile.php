<?php
    use Netcarver\Textile\Parser;
        
function smarty_modifier_textile($string){
	$parser = new Parser();
	return $parser->textileThis($string);
}