<div id="_sideContent" class="sideContent">
 <ul>
  {foreach $navigations as $navi}
  <li{if $_path == $navi} class="select"{/if}><a href="{$navi}">{$_vars.path[$navi].title}<i class="icoFontArrowChevronRight"></i></a></li>
  {/foreach}
  <li{if $_path == 'changelogs.html'} class="select"{/if}><a href="changelogs.html">変更履歴 ({$changelogs[0].date|date_format:"n/d"})<i class="icoFontArrowChevronRight"></i></a></li>
 </ul>
</div>