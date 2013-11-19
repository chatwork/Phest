<?php
	use \Michelf\Markdown;
	require(DIR_BUILDER.'/lib/vendor/phpmarkdown/Michelf/Markdown.php');	
	require(DIR_BUILDER.'/lib/vendor/textile/classTextile.php');
	
function markdown($text){
	return Markdown::defaultTransform($text);
}
	
function textile($text){
	static $parser = null;
	
	if (!$parser){
		$parser = new Textile();
	}
	
	return $parser->textileThis($text);
}