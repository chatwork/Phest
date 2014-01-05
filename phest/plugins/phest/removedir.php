<?php
    namespace ChatWork\Phest;

    use \ChatWork\Utility\File;

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