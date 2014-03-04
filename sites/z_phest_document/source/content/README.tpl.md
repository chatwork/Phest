<img src="https://raw.github.com/chatwork/Phest/master/docs/image/common/logo/logo_phest_white.png"/>

{foreach $content_keys as $ckey}
{if $ckey == "aboutphest"}{$_phestver}{else}{$contents[$ckey].title}{/if}

============

{$contents[$ckey].content}

{/foreach}
