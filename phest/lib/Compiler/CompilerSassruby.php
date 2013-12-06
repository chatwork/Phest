<?php
    namespace ChatWork\Phest;

class CompilerSassruby extends CompilerBase {
    public function compile($source,$pathname){
        $phest = Phest::getInstance();
        $output = $phest->execCompiler('scss',$pathname,'-C');

        //Syntax error:が一行目にあったらエラーとみなす
        if (strpos($output[0],'Syntax error: ') !== false){
            throw new \Exception(preg_replace('/[\x00-\x1f\x7f]\[.[^m]?m/', '', implode('<br />',$output)));
        }
        return implode("\n",$output);
    }

    public function getSectionKey(){
        return 'sass';
    }

    public function getOptionLabel(){
        return 'sass-ruby';
    }

    protected function getConvertFromExtension(){
        return 'scss';
    }

    protected function getConvertToExtension(){
        return 'css';
    }
}