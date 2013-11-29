Phest
============

 * 現在まだ開発中です！！(ベータバージョン) ご利用は **at your own risk** で。
 * まだ後方互換性のない仕様変更が入る可能性が大きくありますのでご注意を。

Phest (フェスト) はPHPでできた、デザイナ向けの静的サイトジェネレーターです。
(Phest = **PH**P **E**asy **St**atic Site Generator)

静的サイトジェネレーターとは、テンプレートなどプログラム的な処理を実行し、HTMLファイルとして書き出すツールのことです。
[Amazon S3](http://aws.amazon.com/jp/s3/)でのホスティングやGitHub Pagesなど、静的ページしか使えないような環境向けのサイト作りに便利です。
テンプレートエンジンには[Smarty](http://www.smarty.net/)を採用。テンプレートファイルのインクルードや各種条件文などを柔軟に使用できます。

Phestはクラウド型ビジネスチャットツール「[チャットワーク](http://www.chatwork.com/ja/)」を開発するChatWork社が開発しています。
ChatWork社内で実際にサイト制作に使用しているツールであるため、随時継続的なバージョンアップが行われています。

※静的サイトにするメリット (参考:[静的サイトジェネレータのメリット・デメリット](http://mymemo.weby117.com/static/static_2.html))
 * 低コストで運用可能。(Amazon S3なら月額10円〜)
 * サーバー側でプログラム処理が不要なので、配信が高速。
 * CMSなどを使わないので、セキュリティが高い。
 * Gitなどによるソースコード管理やバックアップが容易。


特徴
---------------
### 黒い画面不要！デザイナ向けのツールです。
一般的な静的サイトジェネレータはプログラマ向けで、黒い画面(ターミナル)によるコマンド操作が必須です。
PhestはPHPさえ動けば、ブラウザによるGUI操作が可能でターミナルによるコマンド操作は必要ありません。
ファイルの自動更新検知もコマンドを実行する必要はなく、ブラウザ側から実行でき、更新をブラウザのデスクトップ通知でリアルタイムに通知します。
(※デスクトップ通知は対応しているブラウザのみ。Chrome/Firefox/Safari/Opera など)

### Smartyのテンプレートから静的HTMLファイルを生成できる
Smartyの `{include file=""}` や `{if}` `{foreach}` をはじめとした強力で柔軟なテンプレート構文が使用できます。
共通ヘッダやフッタなどを簡単に記述可能です。
`index.tpl` をファイル作成すると、`index.html` として生成されるイメージです。(フォルダ構造も自由に作成可能)

### LESSやSCSS、CoffeeScriptなどのCSS/JSプリプロセッサ、minifyを実行可能
静的ファイルの書き出しにともなって、LESS/SCSS/CoffeScriptなどのコンパイル、圧縮処理(minify)を実行できます。
さらに、CSSやJavaScriptをSmartyで一度処理させてからコンパイルさせることもできます。(CSSやJavaScript内でSmarty構文が使用可能)
なお、圧縮処理は時間がかかるため本番環境用ビルドでのみ実行されます。

### YAMLで記述した変数定義を使用することが可能
簡易なデータ記述言語である[YAML](http://ja.wikipedia.org/wiki/YAML)で変数を定義することができます。
ベースレイアウトを記述したテンプレートの、`<title>` タグの中身をページごとに変更したり、
フォルダ階層ごとにレイアウトを変えたり、配列を定義してSmartyの `{foreach}` で回して一括展開することも可能です。

### sitemap.xmlを自動生成
ページ一覧を記述した検索エンジン用のsitemap.xmlを自動生成します。
また、sitemap.xmlのパスを記述したrobots.txtも自動生成します。

### ローカル環境用と、本番環境用で別々に生成が可能
ローカル環境時はドメインをlocalhostにして、本番用にはドメインを本番サーバーにしたバージョンで生成など、
環境に応じたファイル生成が柔軟に可能。watch機能によりファイルの変更を検知して自動でビルドを実行することもできます。
(ビルド結果はブラウザのデスクトップ通知機能で通知されます *対応しているブラウザのみ Chrome/Safari/Firefoxなど)

### HTMLメールの生成などにも便利
encodeオプションでJISコードに文字コード変換して生成させることもできます。


インストール
---------------
PHP5.3以上がインストールされている必要があります。
Windowsでは[XAMPP](http://www.apachefriends.org/jp/xampp.html)、Macでは[MAMP](http://www.mamp.info/en/index.html)が簡単にインストールできるのでオススメです。
また、ツール内部でGoogle Closure Compilerを実行するためJavaの実行環境が必要です。

PHPが稼働するドキュメントルート以下(通常 `htdocs/` や `www/` 、 `public_html/` など)に、リポジトリ内のデータをコピーするだけでokです。

フォルダ構成は

- phest/         (ビルドツール)
- sites/         (作成するサイトデータ)

となっています。

`phest/` をブラウザから開くとビルドツールが表示されます。

※コピーするフォルダは `phest/` だけでも問題ありません。
リポジトリにコミットされている `sites/` にはあらかじめ参考となるサンプルのサイトデータが設置されていますが、不要であれば削除してください。


サイトの新規作成
---------------
ビルドツールを表示し、`Create new site`リンクをクリックします。
作成したいサイト名を半角英数で入力します。 (例:mysite)

- phest/         (ビルドツール)
- sites/         (作成するサイトデータ)

すると、 `sites/` 以下にサイト名のフォルダが生成されます。


Phestのファイル構成
---------------
`mysite` というサイトを作成したものとして説明します。

- phest/         (ビルドツール)
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

というファイル構成になっています。

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
	  local: "http://localhost/Phest/sites/sample/output/local"
	  production: "http://www.sample.com"
    basetpl: "_base.tpl"
    buildclear: 1
	sitemap: 1
	robotstxt: 1
    compilejs: 1
	compilecss: 1

`home` には、ローカル環境時と本番環境時のブラウザから見たルートパスを設定します。
この設定値は、`{$_home}` としてテンプレート変数として自動でアサインされるため、`<a href="{$_home}/test.html"></a>` など、
パスとして使用したい場合に便利です。

`basetpl` は、レイアウトのベースとなるテンプレートファイル名を指定します。(デフォルト:_base.tpl)

`buildclear` は、ビルド時に `output/` の中身をすべて削除するかどうかのオプションです。
1 ですべて削除し、0 を指定すると削除しません。(デフォルト:1)

`sitemap` は `sitemap.xml` ファイルを自動で生成するかどうかのオプションです。
1 で生成し、0 を指定すると生成しません。(デフォルト:1)

`robotstxt` は `robots.txt` ファイルを自動で生成するかどうかのオプションです。
1 で生成し、0 を指定すると生成しません。(デフォルト:1)

`compilejs` は JavaScript の minify 処理を実行するかどうかのオプションです。
1 の場合は本番環境用に Build した時に minify されます。(デフォルト:1)

`compilecss` はスタイルシートの minify 処理を実行するかどうかのオプションです。
1 の場合は本番環境用に Build した時に minify されます。(デフォルト:1)

他に、 `encode` というキーでエンコードしたい文字コードを入れると、
その文字コードに変換して出力できます。(HTMLメールの場合はJISにしたいなど)
PHPの `mb_convert_encoding` 関数で指定できる文字コードの文字列をセットできます。

 * 高度なconfig.ymlの設定

    smartypluginsdir: []

サイトごとに個別のSmartyプラグインフォルダを指定できます。
`source/` フォルダからの相対パスを指定してください。

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

 * 高度なvars.ymlの設定

    includes: []

別のYAMLファイルの内容を取り込むことができます。`source/`フォルダからの相対パスで、YAMLファイルを指定してください。

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

- `{$_home}`
    - ブラウザからアクセスするルートパスを指定します。最後の/は指定しない。
    - `config.yml` の `home` の値がビルドタイプに応じて入ります。
- `{$_path}`
    - ルートパスからのページパスを表します。
    - 例：www.sample.com/feature/index.html なら `feature/index.html`
    - これを使って、特定のパスだけレイアウトを変えるなどが可能です。
- `{$_folder}`
    - `{$_path}` のフォルダ名部分のみが入ります。
- `{$_content_tpl}`
    - 表示対象ファイルのテンプレートファイルパスです。システムが内部的に使用しています。
- `{$_time}`
    - ビルド実行時のタイムスタンプです。


組み込みテンプレート関数
---------------
テンプレート内で実行できる独自のテンプレート関数です。(Smarty標準でないもの)

- `{local}〜{/local}`
    - ローカル環境でだけブロック内の文字列を出力します
- `{production}〜{/production}`
    - 本番環境でだけブロック内の文字列を出力します
- `{$xxx|markdown}`
    - 変数をmarkdownとして解釈し、対応するHTMLに変換します。
- `{markdown}〜{/markdown}`
    - ブロックで囲んだ部分をMarkdownとして処理します。
- `{$xxx|textile}`
    - 変数をtextileとして解釈し、対応するHTMLに変換します。
- `{textile}〜{/textile}`
    - ブロックで囲んだ部分をTextileとして処理します。
- `{$xxx|print_a}`
    - 変数の内容をビジュアルに出力します(配列などの場合に便利)


多言語対応
---------------
Phestには多言語に対応するための仕組みが用意されています。
`config.yml` に `languages` というキーを定義することで有効になります。

例：日本語、英語、ベトナム語に対応

    languages: [ "ja", "en", "vi" ]

このキーを足してビルドツールを表示すると、「言語」という項目が表示されるようになります。
("ja"や"en"などは特に決まっておらず、任意の言語を表す文字列を指定できます)

上記の設定をしたあと、1度ビルドを実行するとサイトの `source/` フォルダに `languages.yml` という空ファイルが作成されます。
そのYAMLファイルに多言語の言語キーをセットしていくことで多言語対応が可能です。

`language.yml` の記述は下記の様に定義します。

    testkey:
      description:"テストキー"
      lang:
        ja: "日本語"
        en: "English"
        vi: "Tiếng Việt"

こう記述しておくと、`.tpl` ファイル内で

    {$L.testkey}

と書くことでビルド時に選択している言語を埋め込むことができます。

また、言語キー内で別の言語キーを読み込むこともできます。

例：testkey2 を読み込み

    testkey:
      lang:
        ja: "読み込み {{testkey2}}"

これを使うことで共通で使う文言などをまとめることができます。

その他、詳しくはリポジトリの `sites/sample_i18n` の設定を参照してください。


プラグイン
---------------
`config.yml` に `plugins` というキーを定義することで、
ビルド時に複数のファイルを1つにまとめたり、
特定のフォルダをコピーしてきたりといった高度な処理を行うことができます。

設定例：

    plugins:
      - concat:
          output: "all.js"
          sources:[ "test.js", "test2.js" ]
      - copydir:
          from: "cptest1"
          to: "cptest2"

※いまのところはファイルを統合する `concat` とフォルダをコピーする `copydir` のみ。
今後追加予定。sites側でサイトごとに独立してpluginを作成して拡張することが可能。

参考
---------------
- Smarty http://www.smarty.net/docs/ja/
- YAML http://ja.wikipedia.org/wiki/YAML
- Markdown http://ja.wikipedia.org/wiki/Markdown
- Textile http://txstyle.org/


LICENSE
---------------
Licensed under MIT, see [LICENSE](https://github.com/chatwork/Phest/blob/master/LICENSE)
