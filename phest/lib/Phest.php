<?php
    namespace ChatWork\Phest;

class Phest {
    protected $message_data = array();
    protected $site = '';
    protected $watch_list = array();
    protected $site_last_buildtime = 0;
    protected $site_last_buildhash = 0;
    protected $plugins_dir = array();

    public function __construct(){
        $this->plugins_dir = array(DIR_PHEST.'/plugins/phest');
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
        $path_buildstatus_site = $dir_buildstatus.'/'.$site.'.dat';

        //ソースフォルダの全ファイルをスキャン。新しいファイルがあるかどうかの判定に使う。
        if (file_exists($path_buildstatus_site)){
            $this->site_last_buildtime = filemtime($path_buildstatus_site);
            $this->site_last_buildhash = file_get_contents($path_buildstatus_site);
        }
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

    public function getSourcePath(){
        return DIR_SITES.'/'.$this->site.'/source';
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
            $this->site_last_buildhash = $source_pathhash;
            //ビルド時間を記録
            File::buildPutFile($path_buildstatus_site,$pathhash);
        }

        return $has_new;
    }
}