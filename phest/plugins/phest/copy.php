<?php
    namespace ChatWork\Phest;

    use \ChatWork\Utility\File;

/**
 * ファイルをコピーする
 *
 * @param  array   $params パラメータ
 * @param  array    $params.sources コピー元のファイル一覧 (source/ からの相対パス)
 * @param  string   $params.outputdir コピー先のフォルダパス (source/ からの相対パス)
 * @param  Phest    $phest Phestオブジェクト
 * @return bool プラグインの成功判定
 */
function plugin_watch_copy(array $params, Phest $phest){
    global $phest_plugin_copy_list;

    if (!isset($params['outputdir'])){
        $phest->add('builderror','[copy] outputdirオプションが指定されていません');
        return false;
    }

    if (!isset($params['sources'])){
        $phest->add('builderror','[copy] sourcesオプションが指定されていません');
        return false;
    }

    $sourcepath = $phest->getSourcePath();
    foreach ($params['sources'] as $spath){
        $spath = $sourcepath.'/'.$spath;
        if (file_exists($spath)){
            $phest->addWatchList($spath);
        }
    }

    return true;
}

function plugin_build_copy(array $params,Phest $phest){
    global $phest_plugin_copy_list;

    $output_dir = rtrim($params['outputdir'],'\\/');
    $sourcepath = $phest->getSourcePath();

    $count = 0;
    foreach ($params['sources'] as $spath){
        $spath = $sourcepath.'/'.$spath;
        if (file_exists($spath)){
            $count++;
            File::buildCopy($spath,$sourcepath.'/'.$output_dir.'/'.basename($spath));
        }else{
            $phest->add('builderror','[copy] sources で指定されたファイルが存在しません: '.$spath);
        }
    }
    $phest->add('build','[copy] '.$count.'個のファイルをコピー: /<b>'.$output_dir.'</b>');

    return true;
}