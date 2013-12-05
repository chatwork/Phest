<?php
    namespace ChatWork\Phest;

/**
 * 各種コンパイラを抽象化する
 */
class Compiler {
    protected static $cache = array();

    /**
     * コンパイラクラスを取得
     *
     * @method factory
     * @param string $compiler_type コンパイラクラス名 (CompilerXxxx の Xxxx以降)
     * @return CompilerBase コンパイラクラス
     */
    public static function factory($compiler_type){
        $classname = 'Compiler'.ucfirst($compiler_type);

        if (isset(self::$cache[$compiler_type])){
            return self::$cache[$compiler_type];
        }

        if (!class_exists($classname)){
            require(dirname(__FILE__).'/Compiler/'.$classname.'.php');
        }

        $classname = '\\ChatWork\\Phest\\'.$classname;

        self::$cache[$compiler_type] = new $classname;
        return self::$cache[$compiler_type];
    }
}

/**
 * コンパイラのベースクラス
 */
abstract class CompilerBase {
    /**
     * コンパイル処理を実行
     *
     * @param string $source ソースコードの中身
     * @param string $pathname ファイルパス
     * @return string コンパイル後のソールコード
     */
    public abstract function compile($source,$pathname);
    
    /**
     * Phestのメッセージ用のセクション名を返す
     * 
     * @return string セクション名
     */
    public abstract function getSectionKey();
    
    /**
     * 変換前の拡張子
     * 
     * @return string 拡張子
     */
    protected abstract function getConvertFromExtension();
    
    /**
     * 変換後の拡張子
     * 
     * @return string 拡張子
     */
    protected abstract function getConvertToExtension();
    
    /**
     * ファイル名の拡張子を変換 .less -> .css など
     * 
     * @param string $filepath ファイルパス
     * @return string 新しいファイル名
     */
    public function convertFileName($filepath){

        $from = $this->getConvertFromExtension();
        $to = $this->getConvertToExtension();

        $new_filename = '';
        $filepart = explode('.',$filepath);
        for ($i = 0;$i < count($filepart);$i++){
            if ($i == 0){
                $new_filename = $filepart[0];
            }else{
                $new_filename .= '.'.str_replace($from,$to,$filepart[$i]);
            }
        }

        return $new_filename;
    }
}