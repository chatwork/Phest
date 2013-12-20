<?php
    namespace ChatWork\Phest;

    use \ChatWork\Utility\File;

$phest_plugin_concat_list = array();
function plugin_watch_concat(array $params, Phest $phest){
    global $phest_plugin_concat_list;

    if (!isset($params['output'])){
        $phest->add('builderror','[concat] outputオプションが指定されていません');
        return false;
    }

    if (!isset($params['sources'])){
        $phest->add('builderror','[concat] sourcesオプションが指定されていません');
        return false;
    }

    $phest_plugin_concat_list[$params['output']] = array();
    foreach ($params['sources'] as $spath){
        $spath = $phest->getSourcePath().'/'.$spath;
        if (file_exists($spath)){
            $phest->addWatchList($spath);
            $phest_plugin_concat_list[$params['output']][] = $spath;
        }else{
            $phest->add('builderror','[concat] sources で指定されたファイルが存在しません: '.$spath);
        }
    }

    return true;
}

function plugin_build_concat(array $params,Phest $phest){
    global $phest_plugin_concat_list;

    foreach ($phest_plugin_concat_list as $output_to => $cpath_list){
        $output_source = '';
        foreach ($cpath_list as $cpath){
            $output_source .= file_get_contents($cpath);
        }
        $phest->add('build','[concat] '.count($cpath_list).'個のファイルを結合: /<b>'.$output_to.'</b>');
        File::buildPutFile($phest->getSourcePath().'/'.$output_to, $output_source);
        unset($phest_plugin_concat_list[$output_to]);
    }

    return true;
}