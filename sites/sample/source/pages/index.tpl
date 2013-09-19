<div>This is index.tpl content!</div>

<ul>
 <li>$_home = {$_home}</li>
 <li>$_path = {$_path}</li>
 <li>$_folder = {$_folder}</li>
 <li>$test = {$test}</li>
</ul>

<div>
{"Markdown `<b>Test</b>` *aaa* "|markdown}

{markdown}
this is **markdown**
{/markdown}

{"Textile *test* _test test_"|textile}

{textile}
this is *Textile*
{/textile}
</div>