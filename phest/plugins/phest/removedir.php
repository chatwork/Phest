<?php
    namespace ChatWork\Phest;

    use \ChatWork\Utility\File;

/**
 * フォルダを削除する
 *
 * @param  array  $params パラメータ
 * @param  string  $params.dir 削除対象のフォルダパス (source/ からの相対パス)
 * @param  Phest   $phest Phestオブジェクト
 * @return bool プラグインの成功判定
 */
function plugin_build_removedir(array $params, Phest $phest){
    if (empty($params['dir'])){
        $phest->add('builderror','[removedir] dirオプションが指定されていません');
        return false;
    }

    $dir_source = $phest->getSourcePath();
    $dpath = $dir_source.'/'.$params['dir'];
    if (!is_dir($dpath)){
        $phest->add('builderror','[removedir] dir はディレクトリではありません: '.$dpath);
        return false;
    }

    $phest->add('build','[removedir] <b>'.$dpath.'</b> を削除しました');
    File::removeDir($dpath);

    return true;
}