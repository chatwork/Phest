<div>This is index.tpl content!</div>

<ul>
 <li>$_home = {$_home}</li>
 <li>$_path = {$_path}</li>
 <li>$_folder = {$_folder}</li>
 <li>$test = {$test}</li>
</ul>

<div>
{"Markdown `<b>Test</b>` *aaa* "|markdown}

<section>
{markdown}
# this is headline.
hello **Phest!**
{/markdown}
</section>


{"Textile *test* _test test_"|textile}

<section>
{textile}
h1. this is headline.
hello *Phest!*
{/textile}
</section>

</div>