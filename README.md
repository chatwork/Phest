SmartBuilder
============

※現在まだ開発中です！！(アルファバージョン) ご利用は at your own riskで。

SmartyベースのWebサイト制作ツールです。
SmartyのテンプレートファイルをHTMLファイルへ変換し静的サイトとして書き出します。
S3でのホスティングやGitHub Pagesなど、静的ページしか使えないような環境向けのサイト作りに便利です。

特徴
---------------
### Smartyベースのテンプレートファイルを扱える
Smartyの `{include file=""}` をはじめとした柔軟なテンプレート構文が使用できます。
共通ヘッダやフッタなどを簡単に記述可能です。
`index.tpl` をファイル作成すると、`index.html` として生成されるイメージです。

### YAMLで記述した変数定義を使用することが可能
簡易なデータ記述言語であるYAMLで変数を定義することができます。
フォルダ階層やパスごとに変数の内容をカスケーディングしていくことができるので、
フォルダ階層ごとにレイアウトを変えたり、タイトルタグの中身を変数で制御したりなどに使えます。
また、配列などを定義してSmartyの `{foreach}` で回して一括展開することも可能です。

### ローカル環境用と、本番環境用で別々に生成が可能
ローカル環境時はドメインをlocalhostにして、本番用にはドメインを本番サーバーにしたバージョンで生成など、
環境に応じたファイル生成が柔軟に可能。

### LESSやSCSSなどのCSSプリプロセッサを使用可能
静的ファイルの書き出しにともなって、LESSなどのコンパイルを実行できます。
さらに、LESSファイルをSmartyで一度処理させてからコンパイルさせることもできます。

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

 - SmartBuilder
  - builder/       (ビルドツール)
  - sites/         (作成するサイトデータ)

というフォルダ構成になっています。

`SmartBuilder/` をphpを処理できるパスにコピーし、
`builder/build.php` をブラウザから実行するとビルドツールが表示されます。


サイトの新規作成
---------------
ビルドツールを表示し、`Create new site`リンクをクリックします。
作成したいサイト名を半角英数で入力します。 (例:mysite)

 - SmartBuilder
  - builder/       (ビルドツール)
  - sites/         (作成するサイトデータ)

すると、 `sites/` 以下にサイト名のフォルダが生成されます。


サイトのファイル構成
---------------
`mysite` というサイトを作成したものとして説明します。

 - SmartBuilder
  - builder/       (ビルドツール)
  - sites/         (作成するサイトデータ)
   - mysite/
    - config.yml   (設定ファイル)
    - vars.yml     (サイトの変数定義ファイル)
    - htdocs/      (静的ファイルの生成先)
    - source/
     - javascript/
     - pages/
     - parts/
     - style/

というフォルダ構成になっています。

`mysite/config.yml` が設定ファイルになっているので、
サイトの情報やオプションなどを設定できます。

`mysite/vars.yml` は変数定義ファイルになっていて、
サイト全体で使用するテンプレート変数や、パスに応じて
変数の内容を変化させることもできます。

`mysite/htdocs` は生成されるファイルが出力されるフォルダです。
ここのファイルをサーバーにアップロードしてください。

`mysite/source` 以下は、実際のサイトのコードやリソースになります。

`javascript/` , `style/` 以下は Javascript 、スタイルシートをそれぞれ入れます。

`pages/` 以下は、Smartyのtplファイルを入れます。
ここに `index.tpl` と置くと、`htdocs/index.html` として生成されます。
フォルダ階層を自由につくることもできます。

`parts/` 以下は、Smartyのtplファイルを入れますが、`pages/`との違いは、
ここに置いた `.tpl` ファイルは自動で `.html` ファイルへと生成されないため、
ページではない共通のテンプレートファイルなどを入れます。


config.yml の設定方法
---------------
下記の内容がデフォルトで入っています。

	home:
	 local:"http://localhost/SmartBuilder/sites/sample/htdocs"
	 production:"http://www.sample.com"
	sitemap:1
	robotstxt:1
	compilejs:1

`home` には、ローカル環境時と本番環境時のブラウザから見たルートパスを設定します。
この設定値はビルド時のリンクにも使われますが、`{$_home}` としてテンプレート変数としても
自動でアサインされるため、`<a href="{$home}/test.html"></a>` など、
パスとしてしたい場合にも便利です。

`sitemap` は `sitemap.xml` ファイルを自動で生成するかどうかのオプションです。
1 で生成し、0 を指定すると生成しません。

`robotstxt` は `robots.txt` ファイルを自動で生成するかどうかのオプションです。
1 で生成し、0 を指定すると生成しません。

`compilejs` は JavaScript の minify 処理を実行するかどうかのオプションです。
1 の場合は本番環境用に Build した時に minify されます。

他に、 `encode` というキーでエンコードしたい文字コードを入れると、
その文字コードに変換して出力できます。(HTMLメールの場合はJISにしたいなど)
PHPの `mb_convert_encoding` 関数で指定できる文字コードの文字列をセットできます。


vars.yml の設定方法
---------------
下記の内容がデフォルトで入っています。

	common:
	path:

`common` 以下には、全ページ共通でアサインされる変数を定義します。
`path` 以下には、ページごとにアサインされる変数を定義します。

例えば、

	common:
	 title:"no title"
	path:
	 index:
	  title:"index page"
	 feature/index:
	  title:"feature page"

と定義した場合は、`{$title}` というテンプレート変数が、

`/index.html` では `index page` とアサインされ、
`/feature/index.html` では `feature page` とアサインされます。
それ以外のページでは、 `no title` とアサインされます。


style/ のファイル作成方法
---------------
`style/` にはCSS系のファイルを入れますが、拡張子に応じて自動でコンパイル処理などが行われます。

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


javascript/ のファイル作成方法
---------------
`javscript/` にはJavascriptのファイルを入れますが、CSSと同様に拡張子で処理が行われます。

 - *.js
  - そのままコピーします。compilejs:1 なら本番環境でビルドするとminifyします
 - *.tpl.js
  - Smartyで処理後、compilejs:1 なら本番環境でビルドするとminifyします


組み込み変数
---------------
テンプレート内で自動でアサインされる変数の一覧です。

 - {$_home}
  - ブラウザからアクセスするルートパスを指定します。最後の/は指定しない。
  - `config.yml` の `home` の値がビルドタイプに応じて入ります。
 - {$_path}
  - ルートパスからのページパスを表します。拡張子は省略される形式になります。
  - 例：www.sample.com/feature/index.html なら `feature/index`
 - {$_content_tpl}
  - 表示対象ファイルのテンプレートファイルパスです。システムが内部的に使用しています。


組み込み関数
---------------
テンプレート内で実行できる独自のテンプレート関数です。(Smarty標準でないもの)

- {local}〜{/local}
 - ローカル環境でだけブロック内の文字列を出力します
- {production}〜{/production}
 - 本番環境でだけブロック内の文字列を出力します
- {time}
 - ビルド時のタイムスタンプを出力します


