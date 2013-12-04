<div>This is feature/index.tpl content!</div>

<ul>
 <li>$_home = {$_home}</li>
 <li>$_path = {$_path}</li>
 <li>$_folder = {$_folder}</li>
 <li>$_top = {$_top}</li>
 <li>$test = {$test}</li>
</ul>

<ol>
 <li{if $_path == "index.html"} class="selected" {/if}><a href="{$_top}/index.html">index.html</a></li>
 <li{if $_path == "feature/index.html"} class="selected" {/if}><a href="{$_top}/feature/index.html">feature/index.html</a></li>
 <li{if $_path == "feature/subfeature/subfeature.html"} class="selected"{/if}><a href="{$_top}/feature/subfeature/subfeature.html">feature/subfeature/subfeature.html</a></li>
</ol>