<?php
    namespace ChatWork\Phest;

    use \ChatWork\Utility\File;

/**
 * フォルダをコピーする
 *
 * @param  array   $params パラメータ
 * @param  string   $params.from コピー元のフォルダパス (source/ からの相対パス)
 * @param  string   $params.to コピー先のフォルダパス (source/ からの相対パス)
 * @param  Phest    $phest Phestオブジェクト
 * @return bool プラグインの成功判定
 */
function plugin_watch_copydir(array $params, Phest $phest){
    $dir_source = $phest->getSourcePath();
    $from_dpath = $dir_source.'/'.$params['from'];
    $to_dpath = $dir_source.'/'.$params['to'];
    if (!is_dir($from_dpath)){
        $phest->add('builderror','[copydir] from はディレクトリではありません: '.$from_dpath);
        return false;
    }

    $phest->addWatchList(File::getFileList($from_dpath));
    $phest_plugin_copydir_list[$from_dpath] = $to_dpath;

    return true;
}

function plugin_build_copydir(array $params, Phest $phest){
    $dir_source = $phest->getSourcePath();
    $from_dpath = $dir_source.'/'.$params['from'];
    $to_dpath = $dir_source.'/'.$params['to'];

    $phest->add('build','[copydir] <b>'.$from_dpath.'</b> から <b>'.$to_dpath.'</b> へコピー');
    File::copyDir($from_dpath, $to_dpath, true);

    return true;
}