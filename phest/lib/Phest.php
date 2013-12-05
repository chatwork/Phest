<?php
    namespace ChatWork\Phest;

    use \ChatWork\Utility\File;

/**
 * Phestの様々な処理を行う
 */
class Phest {
    protected $message_data = array();
    protected $site = '';
    protected $lang = '';
    protected $buildtype = '';
    protected $watch_list = array();
    protected $path_buildstatus_site = '';
    protected $site_last_buildtime = 0;
    protected $site_last_buildhash = 0;
    protected $plugins_dir = array();
    protected $compiler_type = array(
        'less' => array(
            'type' => 'less',
            'path' => '',
            ),
        'scss' => array(
            'type' => 'sass',
            'path' => '',
            ),
        'coffee' => array(
            'type' => 'coffeescript',
            'path' => '',
            ),
        );

    /**
     * インスタンスの取得 (Singleton)
     *
     * @method getInstance
     * @return Phest Phestオブジェクト
     */
    public static function getInstance(){
        static $phest = null;

        if (!$phest){
            $phest = new Phest;
        }

        return $phest;
    }

    public function __construct(){
        $this->plugins_dir = array(DIR_PHEST.'/plugins/phest');
        return $this;
    }

    /**
     * ビルド種類 (local|production) をセット
     *
     * @method setBuildType
     * @param ｓ
     */
    public function setBuildType($buildtype){
        $this->buildtype = $buildtype;
        return $this;
    }

    public function getBuildType(){
        return $this->buildtype;
    }

    /**
     * Build実行されてるサイトをセット
     *
     * @method setSite
     * @param string $site サイト
     */
    public function setSite($site){
        $this->site = $site;
        $dir_buildstatus = DIR_PHEST.'/cache/buildstatus';
        $this->path_buildstatus_site = $dir_buildstatus.'/'.$site.'.dat';

        //ソースフォルダの全ファイルをスキャン。新しいファイルがあるかどうかの判定に使う。
        if (file_exists($this->path_buildstatus_site)){
            $this->site_last_buildtime = filemtime($this->path_buildstatus_site);
            $this->site_last_buildhash = file_get_contents($this->path_buildstatus_site);
        }
        return $this;
    }

    /**
     * 言語をセット
     *
     * @method setLang
     * @param string $lang 言語タイプ
     * @chainable
     */
    public function setLang($lang){
        $this->lang = $lang;
        return $this;
    }

    /**
     * 言語を取得
     *
     * @method getLang
     * @return string 言語キー
     */
    public function getLang(){
        return $this->lang;
    }

    /**
     * プラグインのディレクトリを追加
     *
     * @method addPluginsDir
     * @param string/array $path ディレクトリ
     * @chainable
     */
    public function addPluginsDir($path){
        if (is_array($path)){
            $this->plugins_dir = array_merge($this->plugins_dir,$path);
        }else{
            $this->plugins_dir[] = $path;
        }

        return $this;
    }

    /**
     * プラグインをロードする
     *
     * ロードに失敗したらfalseを返し、エラーメッセージを出力
     *
     * @method loadPlugin
     * @param string $plugin_name プラグイン名
     * @return bool 成功したか
     */
    public function loadPlugin($plugin_name){
        $loaded = false;
        foreach ($this->plugins_dir as $pdir){
            $pluginpath = $pdir.'/'.$plugin_name.'.php';
            if (file_exists($pluginpath)){
                require_once($pluginpath);
                $loaded = true;
                break;
            }
        }

        if (!$loaded){
            $this->add('builderror','プラグインファイルのロードに失敗しました: '.$plugin_name);
            return false;
        }

        return true;
    }

    /**
     * サイトのsource/パスを返す
     *
     * @method getSourcePath
     * @return string パス
     */
    public function getSourcePath(){
        return DIR_SITES.'/'.$this->site.'/source';
    }

    /**
     * ビルド先のディレクトリ名を返す (/output 以下)
     *
     * @method getBuildDirName
     * @param string $lang 言語キー
     * @param string $buildtype ビルド種類(local|production)
     * @return string ディレクトリ名
     */
    public function getBuildDirName($lang = null,$buildtype = null){
        if (!$lang){
            $lang = $this->getLang();
        }
        if (!$buildtype){
            $buildtype = $this->getBuildType();
        }
        if ($lang){
            $builddirname = '/output/'.$lang.'/'.$buildtype;
        }else{
            $builddirname = '/output/'.$buildtype;
        }

        return $builddirname;
    }

    /**
     * ビルド先のパスを返す
     *
     * @method getOutputPath
     * @param string $lang 言語キー
     * @param string $buildtype ビルド種類(local|production)
     * @return string パス
     */
    public function getOutputPath($lang = null,$buildtype = null){
        return DIR_SITES.'/'.$this->site.$this->getBuildDirName($lang,$buildtype);
    }

    /**
     * セクションを登録
     *
     * @method registerSection
     * @param string $section セクション名
     * @param string $title セクションタイトル
     * @param array [$options] セクション表示オプション
     * @param enum(success|primary|info|danger) [$option.type=success] 表示種類
     * @param bool [$option.sort=false] メッセージをソートするか
     * @chainable
     */
    public function registerSection($section,$title,array $options = array('type' => 'success','sort' => false)){
        $this->message_data[$section] = array_merge(array('title' => $title,'list' => array()),$options);
        $this->section_order_list[] = $section;

        return $this;
    }

    /**
     * セクションにメッセージを追加
     * @method add
     * @param string $section セクション名 (registerSectionで登録した値)
     * @param string $message メッセージ内容
     * @chainable
     */
    public function add($section,$message){
        if (!isset($this->message_data[$section])){
            trigger_error('BuildMessage: section='.$section.' は定義されていません');
            return $this;
        }
        $this->message_data[$section]['list'][] = $message;

        return $this;
    }

    /**
     * エラーがあるか
     *
     * @method hasError
     * @return boolean  [description]
     */
    public function hasError(){
        foreach ($this->message_data as $section => $mdat){
            if ($mdat['type'] == 'danger' and count($mdat['list'])){
                return true;
            }
        }

        return false;
    }

    /**
     * メッセージデータを取得
     *
     * @method getMessageData
     * @return array メッセージデータの配列
     */
    public function getMessageData(){
        $msg_data = array();

        $type_list = array('success','danger','primary','info');

        foreach ($type_list as $type){
            foreach ($this->message_data as $section => $mdat){
                if ($mdat['type'] != $type){
                    continue;
                }
                if (count($mdat['list'])){
                    if (!empty($mdat['sort'])){
                        asort($mdat['list']);
                    }
                    $msg_data[] = $mdat;
                }
            }
        }

        return $msg_data;
    }

    /**
     * watchによる監視対象のファイルパスを追加する
     *
     * @method addWatchList
     * @param string/array $path ファイルパス
     * @chainable
     */
    public function addWatchList($path){
        if (is_array($path)){
            $this->watch_list = array_merge($this->watch_list,$path);
        }else{
            $this->watch_list[] = $path;
        }

        return $this;
    }

    /**
     * ファイルに更新があるか
     *
     * @method hasNew
     * @return boolean 更新がある場合true
     */
    public function hasNew(){
        $has_new = false;
        $path_concat_string = '';

        //ページをスキャン
        foreach ($this->watch_list as $filepath){
            if ($this->site_last_buildtime < filemtime($filepath)){
                $has_new = true;
            }
            $path_concat_string .= ':'.$filepath;
        }

        //ファイルパスを全部つないだ文字列のハッシュをとる
        $source_pathhash = md5($path_concat_string);

        //ファイルパスの変更があるか
        if ($this->site_last_buildhash != $source_pathhash){
            $has_new = true;
        }

        if ($has_new){
            $this->site_last_buildhash = $source_pathhash;
            //ビルド時間を記録
            File::buildPutFile($this->path_buildstatus_site,$this->site_last_buildhash);
        }

        return $has_new;
    }

    /**
     * コンパイラの種類を取得
     *
     * @method getCompilerType
     * @param string $extension 対応する拡張子
     * @return string|false コンパイラtypeの文字列 見つからなかったらfalse
     */
    public function getCompilerType($extension){
        if (isset($this->compiler_type[$extension]['type'])){
            return $this->compiler_type[$extension]['type'];
        }

        return false;
    }

    /**
     * コンパイラの実行コマンドのパスを返す
     *
     * @method getCompilerPath
     * @param string $extension 対応する拡張子
     * @return string|false コンパイラの実行コマンドのパス 見つからなかったらfalse
     */
    public function getCompilerPath($extension){
        if (isset($this->compiler_type[$extension]['path'])){
            return $this->compiler_type[$extension]['path'];
        }

        return false;
    }
    
    /**
     * コンパイラコマンドを実行
     * 
     * @param  string $extension 拡張子
     * @param  string $pathname  ファイルパス
     * @param  string $option    オプション
     * @return array コマンド出力結果の配列
     */
    public function execCompiler($extension,$pathname,$option = ''){
        $compiler_path = $this->getCompilerPath($extension);
        $compiler_dir = dirname($compiler_path);
        $compiler_command = basename($compiler_path);
        
        switch(PHP_OS){
            case 'Darwin':
            case 'Linux':
                $os = 'unix';
                break;
            case 'WIN32':
            case 'WINNT':
                $os = 'win';
                break;
            default:
                die('サポートしていないOSです:'.PHP_OS);
                break;
        }
        
        $output = array();
        if ($os == 'unix'){
            exec('export PATH=$PATH:'.$compiler_dir.'; export DYLD_LIBRARY_PATH=;'.$compiler_command.' '.$option.' "'.$pathname.'" 2>&1',$output);
        }else{
            putenv('PATH=' . getenv('PATH').';'.$compiler_dir);
            exec($compiler_path.' '.$option.' "'.$pathname.'" 2>&1',$output);
        }
        
        return $output;
    }

    /**
     * コンパイラを設定
     *
     * @method setCompiler
     * @param string $extension 対応する拡張子
     * @param string $type コンパイラtypeの文字列
     * @param string $path コンパイラの実行コマンドのパス 見つからなかったらfalse
     * @chainable
     */
    public function setCompiler($extension,$type,$path = ''){
        $this->compiler_type[$extension] = array('type' => $type,'path' => $path);
        return $this;
    }
}