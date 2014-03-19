<?php
    use Netcarver\Textile\Parser;
    
function smarty_block_textile($params, $content, $template, &$repeat)
{
    if(!$repeat){
        if (isset($content)) {
			$parser = new Parser();
			return $parser->textileThis($content);
        }
    }
}