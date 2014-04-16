<?php
    namespace ChatWork\Phest;

class CompilerLess extends CompilerBase {
    public function compile($source,$pathname){
        $less = new \Less_Parser;
        $less->SetImportDirs(array(dirname($pathname) => './'));
        $less->parse($source);
        
        return $less->getCss();
    }

    public function getSectionKey(){
        return 'less';
    }

    public function getOptionLabel(){
        return 'less-php';
    }

    protected function getConvertFromExtension(){
        return 'less';
    }

    protected function getConvertToExtension(){
        return 'css';
    }
}