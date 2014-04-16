<?php
    use \Michelf\Markdown;
    
function smarty_modifier_markdown($string){
    return Markdown::defaultTransform($string);
}