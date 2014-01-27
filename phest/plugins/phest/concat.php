<?php
    namespace ChatWork\Phest;

    use \ChatWork\Utility\File;

/**
 * ファイルを結合する
 *
 * @param  array   $params パラメータ
 * @param  string   $params.from コピー元のフォルダパス (source/ からの相対パス)
 * @param  string   $params.to コピー先のフォルダパス (source/ からの相対パス)
 * @param  Phest    $phest Phestオブジェクト
 * @return bool プラグインの成功判定
 */
function plugin_watch_concat(array $params, Phest $phest){
    if (!isset($params['output'])){
        $phest->add('builderror','[concat] outputオプションが指定されていません');
        return false;
    }

    if (!isset($params['sources'])){
        $phest->add('builderror','[concat] sourcesオプションが指定されていません');
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

function plugin_build_concat(array $params,Phest $phest){
    $sourcepath = $phest->getSourcePath();
    $output_source = '';
    $count = 0;

    foreach ($params['sources'] as $spath){
        $spath = $sourcepath.'/'.$spath;
        if (file_exists($spath)){
            $count++;
            $output_source .= file_get_contents($spath);
        }else{
            $phest->add('builderror','[concat] sources で指定されたファイルが存在しません: '.$spath);
        }
    }
    $phest->add('build','[concat] '.$count.'個のファイルを結合: /<b>'.$params['output'].'</b>');
    File::buildPutFile($sourcepath.'/'.$params['output'], $output_source);

    return true;
}