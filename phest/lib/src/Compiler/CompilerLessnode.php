<?php
    namespace ChatWork\Phest;

class CompilerLessnode extends CompilerBase {
    public function compile($source,$pathname){
        $phest = Phest::getInstance();
        $output = $phest->execCompiler('less',$pathname);

        //ParseErrorが一行目にあったらエラーとみなす
        if (strpos($output[0],'Error: ') !== false){
            throw new \Exception(preg_replace('/[\x00-\x1f\x7f]\[.[^m]?m/', '', implode('<br />',$output)));
        }
        return implode("\n",$output);
    }

    public function getSectionKey(){
        return 'less';
    }

    public function getOptionLabel(){
        return 'less-node';
    }

    protected function getConvertFromExtension(){
        return 'less';
    }

    protected function getConvertToExtension(){
        return 'css';
    }
}