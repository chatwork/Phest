 <h1>{$title}</h1>
 {foreach $content_keys as $idx => $ckey}
 <div class="contentSection">
  {if $title != $contents[$ckey].title}<h2>{$contents[$ckey].title}</h2>{/if}
  <p>{markdown}{$contents[$ckey].content}{/markdown}</p>
 </div>
 {/foreach}