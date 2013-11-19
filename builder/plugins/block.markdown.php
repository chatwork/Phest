<?
    use \Michelf\Markdown;
    require_once(DIR_BUILDER.'/lib/vendor/phpmarkdown/Michelf/Markdown.php');    
    
function smarty_block_markdown($params, $content, $template, &$repeat)
{
    if(!$repeat){
        if (isset($content)) {
    		return Markdown::defaultTransform($content);
        }
    }
}