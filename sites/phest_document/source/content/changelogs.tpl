<div class="mainContentInner">
 <h1>変更履歴</h1>
 <div class="contentSection">
  <h2>Phestの変更履歴 (<a href="changelogs.xml" target="_blank">RSS</a>)</h2>
  {foreach $changelogs as $item}
  <div class="changeLog">
   <div class="changeLogContent">
    <time>{$item.date|date_format:"Y年n月d日"}</time>
    <p>{if isset($item.link)}<a href="{$item.link}">{$item.title}</a>{else}{$item.title}{/if}</p>
   </div>
  </div>
  {/foreach}
 </div>
</div>
