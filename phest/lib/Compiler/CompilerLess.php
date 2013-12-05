<?php
    namespace ChatWork\Phest;

    require(DIR_PHEST.'/lib/vendor/lessphp/lessc.inc.php');
    use \lessc;

class CompilerLess extends CompilerBase {
    public function compile($source,$pathname){
        $less = new lessc;
        $less->setImportDir(dirname($pathname));

        return $less->compile($source);
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