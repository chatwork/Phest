{foreach $contents as $content}
<dl>
 <dt>{$content.title}</dt>
 <dd>{$content.content|markdown}</dd>
</dl>
{/foreach}