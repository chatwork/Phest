<?php
    namespace ChatWork\Phest;

    use \ChatWork\Utility\File;

$phest_plugin_copydir_list = array();
function plugin_watch_copydir(array $params, Phest $phest){
    global $phest_plugin_copydir_list;

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
    global $phest_plugin_copydir_list;

    foreach ($phest_plugin_copydir_list as $copyfrom => $copyto){
        $phest->add('build','[copydir] <b>'.$copyfrom.'</b> から <b>'.$copyto.'</b> へコピー');
        File::copyDir($copyfrom, $copyto);
    }

    return true;
}