<?php
    namespace ChatWork\Phest;

class CompilerLessnode extends CompilerBase {
    public function compile($source,$pathname){
        $output = array();

        $phest = Phest::getInstance();
        $compiler_path = $phest->getCompilerPath('less');
        $compiler_dir = dirname($compiler_path);
        $compiler_command = basename($compiler_path);

        exec('export PATH=$PATH:'.$compiler_dir.'; export DYLD_LIBRARY_PATH=;'.$compiler_command.' '.$pathname.' 2>&1',$output);

        //ParseErrorが一行目にあったらエラーとみなす
        if (strpos($output[0],'ParseError: ') !== false){
            throw new \Exception(preg_replace('/[\x00-\x1f\x7f]\[.[^m]?m/', '', implode('<br />',$output)));
        }
        return implode("\n",$output);
    }

    public function getSectionKey(){
        return 'less';
    }

    protected function getConvertFromExtension(){
        return 'less';
    }

    protected function getConvertToExtension(){
        return 'css';
    }
}