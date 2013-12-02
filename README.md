Phestとは
============
 
Phest (フェスト) はPHPでできた、デザイナ向けの静的サイトジェネレーターです。
(Phest = **PH**P **E**asy **St**atic Site Generator)

静的サイトジェネレーターとは、テンプレートなどプログラム的な処理を実行し、HTMLファイルとして書き出すツールのことです。
[Amazon S3](http://aws.amazon.com/jp/s3/)でのホスティングやGitHub Pagesなど、静的ページしか使えないような環境向けのサイト作りに便利です。
テンプレートエンジンには[Smarty](http://www.smarty.net/)を採用。テンプレートファイルのインクルードや各種条件文などを柔軟に使用できます。

Phestはクラウド型ビジネスチャットツール「[チャットワーク](http://www.chatwork.com/ja/)」を開発するChatWork社が開発しています。
ChatWork社内で実際にサイト制作に使用しているツールであるため、随時継続的なバージョンアップが行われています。
 
特徴
============
 
### 黒い画面不要！デザイナ向けのツールです。

<img src="image/ja/capture/phest_gui.png" style="width:80%;height:80%" />

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

### Amazon S3へ簡単にデプロイ可能
ビルドしたファイルをAmazon S3の特定バケットに一括デプロイ(リリース)できる仕組みがあります。
S3側にアップされていて、ローカルにないファイルはS3側から自動で削除するなどファイルの同期も可能です。

### ローカル環境用と、本番環境用で別々に生成が可能
ローカル環境時はドメインをlocalhostにして、本番用にはドメインを本番サーバーにしたバージョンで生成など、
環境に応じたファイル生成が柔軟に可能。watch機能によりファイルの変更を検知して自動でビルドを実行することもできます。

### HTMLメールの生成などにも便利
encodeオプションでJISコードに文字コード変換して生成させることもできます。
 
インストール
============
 
PHP5.3以上がインストールされている必要があります。
Windowsでは[XAMPP](http://www.apachefriends.org/jp/xampp.html)、Macでは[MAMP](http://www.mamp.info/en/index.html)が簡単にインストールできるのでオススメです。
また、ツール内部でGoogle Closure Compilerを実行するためJavaの実行環境が必要です。

PHPが稼働するドキュメントルート以下(通常 `htdocs/` や `www/` 、 `public_html/` など)に、リポジトリ内のデータをコピーするだけでokです。

フォルダ構成は

- phest/         (ビルドツール)
- docs/          (ドキュメント)
- sites/         (作成するサイトデータ)

となっています。

`phest/` をブラウザから開くとビルドツールが表示されます。

※コピーするフォルダは `phest/` だけでも問題ありません。
リポジトリにコミットされている `sites/` にはあらかじめ参考となるサンプルのサイトデータが設置されていますが、不要であれば削除してください。
 
LICENSE
============
 
Licensed under MIT,  see [LICENSE](https://github.com/chatwork/Phest/blob/master/LICENSE)
 
