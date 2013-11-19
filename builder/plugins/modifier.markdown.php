<?php
    use \Michelf\Markdown;
    require_once(DIR_BUILDER.'/lib/vendor/phpmarkdown/Michelf/Markdown.php');    
    
function smarty_modifier_markdown($string){
    return Markdown::defaultTransform($string);
}