<?php
    namespace ChatWork\Phest;

    use \ChatWork\Utility\File;

$phest_plugin_copydir_list = array();
function plugin_watch_copydir(array $params, Phest $phest){
    global $phest_plugin_copydir_list;

    if (empty($params['from'])){
        $phest->add('builderror','[copydir] fromオプションが指定されていません');
        return false;
    }

    if (empty($params['to'])){
        $phest->add('builderror','[copydir] toオプションが指定されていません');
        return false;
    }

    $dir_source = $phest->getSourcePath();
    $from_dpath = $dir_source.'/'.$params['from'];
    $to_dpath = $dir_source.'/'.$params['to'];
    if (!is_dir($from_dpath)){
        $phest->add('builderror','[copydir] from はディレクトリではありません: '.$from_dpath);
        return false;
    }
    if (!is_dir($to_dpath)){
        $phest->add('builderror','[copydir] to はディレクトリではありません: '.$to_dpath);
        return false;
    }

    $phest->addWatchList(File::getFileList($from_dpath));
    $phest_plugin_copydir_list[$from_dpath] = $to_dpath;

    return true;
}

function plugin_build_copydir(array $params, Phest $phest){
    global $phest_plugin_copydir_list;

    foreach ($phest_plugin_copydir_list as $copyfrom => $copyto){
        $phest->add('build','[copydir] <b>'.$copyfrom.'</b> から <b>'.$copyto.'</b> へコピー');
        File::copyDir($copyfrom, $copyto, true);
    }

    return true;
}