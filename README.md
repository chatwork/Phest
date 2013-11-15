SmartBuilder
============

※現在まだ開発中です！！(ベータバージョン) ご利用は **at your own risk** で。

※まだ後方互換性のない仕様変更が入る可能性が大きくありますのでご注意を。

SmartyベースのWebサイト制作ツールです。
SmartyのテンプレートファイルをHTMLファイルへ変換し静的サイトとして書き出します。
S3でのホスティングやGitHub Pagesなど、静的ページしか使えないような環境向けのサイト作りに便利です。

特徴
---------------
### Smartyのテンプレートから静的HTMLファイルを生成できる
Smartyの `{include file=""}` や `{if}` `{foreach}` をはじめとした強力で柔軟なテンプレート構文が使用できます。
共通ヘッダやフッタなどを簡単に記述可能です。
`index.tpl` をファイル作成すると、`index.html` として生成されるイメージです。(フォルダ構造も自由に作成可能)

### YAMLで記述した変数定義を使用することが可能
簡易なデータ記述言語であるYAMLで変数を定義することができます。
配列を定義してSmartyの `{foreach}` で回して一括展開することも可能です。
また、フォルダ階層やパスごとに変数の内容を上書き/追加していくことができるので、
フォルダ階層ごとにレイアウトを変えたり、タイトルタグの中身を変数で制御したりなどに使えます。

### ローカル環境用と、本番環境用で別々に生成が可能
ローカル環境時はドメインをlocalhostにして、本番用にはドメインを本番サーバーにしたバージョンで生成など、
環境に応じたファイル生成が柔軟に可能。watch機能によりファイルの変更を検知して自動でビルドを実行することもできます。
(ビルド結果はブラウザのデスクトップ通知機能で通知されます *対応しているブラウザのみ Chrome/Safari/Firefoxなど)

### LESSやSCSS、CoffeeScriptなどのCSS/JSプリプロセッサを使用可能
静的ファイルの書き出しにともなって、LESSなどのコンパイルを実行できます。
さらに、Smartyで一度処理させてからコンパイルさせることもできます。

### JavaScriptの文法チェック、minifyを実行可能
JavaScriptでも、書き出しにともなって文法チェックと圧縮のminify処理を実行できます。
minifyはデバッグしやすいようローカル環境では実施されず、本番環境でのみ実施されます。

### sitemap.xmlを自動生成
ページ一覧を記述した検索エンジン用のsitemap.xmlを自動生成します。
また、sitemap.xmlのパスを記述したrobots.txtも自動生成します。

### HTMLメールの生成などにも便利
encodeオプションでJISコードに変換して生成させることもできます。


ファイル構成
---------------

- SmartBuilder/
    - builder/       (ビルドツール)
    - sites/         (作成するサイトデータ)

というフォルダ構成になっています。

`SmartBuilder/` をphpを処理できるパスにコピーし、
`builder/build.php` をブラウザから実行するとビルドツールが表示されます。


サイトの新規作成
---------------
ビルドツールを表示し、`Create new site`リンクをクリックします。
作成したいサイト名を半角英数で入力します。 (例:mysite)

- SmartBuilder/
    - builder/       (ビルドツール)
    - sites/         (作成するサイトデータ)

すると、 `sites/` 以下にサイト名のフォルダが生成されます。


サイトのファイル構成
---------------
`mysite` というサイトを作成したものとして説明します。

- SmartBuilder/
    - builder/       (ビルドツール)
    - sites/
        - mysite/
            - output/      (静的ファイルの生成先)
            - source/
                - config.yml   (設定ファイル)
                - vars.yml     (サイトの変数定義ファイル)
                - content/
                    - _base.tpl
                    - _footer.tpl
                    - _header.tpl
                    - index.tpl

というフォルダ構成になっています。

`mysite/source/config.yml` が設定ファイルになっているので、
サイトの情報やオプションなどを設定できます。

`mysite/source/vars.yml` は変数定義ファイルになっていて、
サイト全体で使用するテンプレート変数や、パスに応じて
変数の内容を変化させることもできます。

`mysite/output` は生成されるファイルが出力されるフォルダです。
local用にビルドした場合は `mysite/output/local` に、production用にビルドした場合は `mysite/output/production` に出力されます。
ここのファイルをサーバーにアップロードしてください。
このフォルダは毎回buildの度にすべてクリアされます。(config.ymlによるオプションで変更可能)

`mysite/source` 以下は、各サイトのコードやリソースになります。

`content/` 以下は、サイトで使用するSmartyのtplファイルや画像やCSS、JavaScriptなどを入れます。
ここに `index.tpl` と置くと、`output/local/index.html` として生成されそのまま `output/local/` フォルダへコピーされます。
フォルダ階層を自由につくることもできます。
ファイル名の先頭に `_` をつけると、`output`フォルダへと出力されなくなるので、include用のファイルなどに使用してください。
デフォルトの設定では、 `_base.tpl` がレイアウトのベースとなるテンプレートとなります。(config.ymlによるオプションで変更可能)


config.yml の設定方法
---------------
下記の内容がデフォルトで入っています。

	home:
	  local: "http://localhost/SmartBuilder/sites/sample/output/local"
	  production: "http://www.sample.com"
    basetpl: "_base.tpl"
    buildclear: 1
	sitemap: 1
	robotstxt: 1
	compilejs: 1

`home` には、ローカル環境時と本番環境時のブラウザから見たルートパスを設定します。
この設定値はビルド時のリンクにも使われますが、`{$_home}` としてテンプレート変数としても
自動でアサインされるため、`<a href="{$_home}/test.html"></a>` など、
パスとして使用したい場合に便利です。

`basetpl` は、レイアウトのベースとなるテンプレートファイル名を指定します。

`buildclear` は、ビルド時に `output/` の中身をすべて削除するかどうかのオプションです。
1 ですべて削除し、0 を指定すると削除しません。(デフォルト:1)

`sitemap` は `sitemap.xml` ファイルを自動で生成するかどうかのオプションです。
1 で生成し、0 を指定すると生成しません。(デフォルト:1)

`robotstxt` は `robots.txt` ファイルを自動で生成するかどうかのオプションです。
1 で生成し、0 を指定すると生成しません。(デフォルト:1)

`compilejs` は JavaScript の minify 処理を実行するかどうかのオプションです。
1 の場合は本番環境用に Build した時に minify されます。(デフォルト:1)

他に、 `encode` というキーでエンコードしたい文字コードを入れると、
その文字コードに変換して出力できます。(HTMLメールの場合はJISにしたいなど)
PHPの `mb_convert_encoding` 関数で指定できる文字コードの文字列をセットできます。


vars.yml の設定方法
---------------
下記の内容がデフォルトで入っています。

	common:
	local:
	production:
	path:

`common` 以下には、全ページ共通でアサインされる変数を定義します。
`local` 以下には、ローカル環境でビルドしたときのみアサインされる変数を定義します。
`production` 以下には、本番環境でビルドしたときのみアサインされる変数を定義します。
`path` 以下には、ページごとにアサインされる変数を定義します。

例えば、

	common:
	  title:"no title"
	local:
	  title:"local"
	production:
	  title:"production"
	path:
	  index.html:
	    title:"index page"
      feature/:
        title:"feature page"
	  feature/index.html:
	    title:"feature index page"

と定義した場合は、`{$title}` というテンプレート変数が、

`/index.html` では `index page` とアサインされ、
`/feature/index.html` では `feature index page` とアサインされ、
`/feature/abc.html` では `feature page` とアサインされます。

それ以外のページでは、ローカル環境でビルドすると `local` が、本番環境でビルドすると `production` がアサインされます。


スタイルシートのファイル作成方法
---------------
スタイルシートで使用する拡張子のファイルを `pages/` 以下に置くと、拡張子に応じて自動でコンパイル処理などが行われます。

- *.css
    - そのままコピーされます
- *.tpl.css
    - Smartyで処理します
- *.less
    - Lessでコンパイルします
- *.tpl.less
    - Smartyで処理後、Lessでコンパイルします
- *.scss
    - SCSSでコンパイルします
- *.tpl.scss
    - martyで処理後、SCSSでコンパイルします

生成後は、`.less` や `.tpl` などの拡張子はカットされ、すべて `.css` として出力されます。
同名で拡張子だけが違う xxx.less xxx.scss などファイルを作ると上書きされるので注意してください。

また、ファイル名の先頭に `_` をつけると、単体でコンパイルされなくなります。
`@import` などでインポートするファイルに使ってください。

`@import` でインポートする場合で、.tpl. を含むSmarty処理後のファイルをインポートしたい場合は、
.tpl. をファイル名から除外したファイル名を指定してください。


JavaScriptのファイル作成方法
---------------
JavaScriptのファイルを `pages/` 以下に置くと、拡張子に応じて自動で構文チェックやコンパイル処理などが行われます。

- *.js
    - そのままコピーします
- *.tpl.js
    - Smartyで処理してコピーします
- *.coffee
    - CoffeeScriptでコンパイルします
- *.tpl.coffee
    - Smartyで処理後、CoffeeScriptでコンパイルします

    
config.yml に `compilejs:1` を指定している場合は、本番環境でビルドするとGoogle Closure Compilerでminifyします。

また、ファイル名の先頭に `@` をつけると、構文チェックが実行されなくなります。
jQueryなどの外部OSSライブラリでエラーが大量に出てしまうのを無視したい場合に使ってください。
※`@`は生成時にはファイル名からカットされて出力されます。


組み込み変数
---------------
テンプレート内で自動でアサインされる変数の一覧です。

- {$_home}
    - ブラウザからアクセスするルートパスを指定します。最後の/は指定しない。
    - `config.yml` の `home` の値がビルドタイプに応じて入ります。
- {$_path}
    - ルートパスからのページパスを表します。
    - 例：www.sample.com/feature/index.html なら `feature/index.html`
    - これを使って、特定のパスだけレイアウトを変えるなどが可能です。
- {$_folder}
    - `{$_path}` のフォルダ名部分のみが入ります。
- {$_content_tpl}
    - 表示対象ファイルのテンプレートファイルパスです。システムが内部的に使用しています。


組み込みテンプレート関数
---------------
テンプレート内で実行できる独自のテンプレート関数です。(Smarty標準でないもの)

- {local}〜{/local}
    - ローカル環境でだけブロック内の文字列を出力します
- {production}〜{/production}
    - 本番環境でだけブロック内の文字列を出力します
- {time}
    - ビルド時のタイムスタンプを出力します
- {$xxx|markdown}
    - 変数をmarkdownとして解釈し、対応するHTMLに変換します。
    - `{"マークダウンの**文字列**"}` などとして、変数にアサインしていない文字列もmarkdown処理できます。
- {markdown}〜{/markdown}
    - ブロックで囲んだ部分をMarkdownとして処理します。
- {$xxx|textile}
    - 変数をtextileとして解釈し、対応するHTMLに変換します。
    - `{"Textileの*文字列*"}` などとして、変数にアサインしていない文字列もtextile処理できます。
- {textile}〜{/textile}
    - ブロックで囲んだ部分をTextileとして処理します。
- {$xxx|print_a}
    - 変数の内容をビジュアルに出力します(配列などの場合に便利)

参考
---------------
- Smarty http://www.smarty.net/docs/ja/
- Markdown http://ja.wikipedia.org/wiki/Markdown
- Textile http://txstyle.org/


LICENSE
---------------
Licensed under MIT, see LICENSE
