<?php
    namespace ChatWork\Phest;

class CompilerCoffeescriptnode extends CompilerBase {
    public function compile($source,$pathname){
        $phest = Phest::getInstance();
        $output = $phest->execCompiler('coffee',$pathname,'-p');

        //error:が一行目にあったらエラーとみなす
        if (strpos($output[0],' error: ') !== false){
            throw new \Exception(preg_replace('/[\x00-\x1f\x7f]\[.[^m]?m/', '', implode('<br />',$output)));
        }
        return implode("\n",$output);
    }

    public function getSectionKey(){
        return 'coffeescript';
    }

    protected function getConvertFromExtension(){
        return 'coffee';
    }

    protected function getConvertToExtension(){
        return 'js';
    }
}