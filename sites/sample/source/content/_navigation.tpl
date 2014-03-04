{* ナビゲーションリンクを生成。$navigations は vars.yml で定義されている *}
<ol>
 {foreach $navigations as $navi}
 <li{if $_path == $navi} class="selected" {/if}><a href="{$_top}/{$navi}">{$navi}</a></li>
 {/foreach}
</ol>