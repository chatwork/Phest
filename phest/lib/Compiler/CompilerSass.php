<?php
    namespace ChatWork\Phest;

    require(DIR_PHEST.'/lib/vendor/scssphp/scss.inc.php');
    use \scssc;

class CompilerSass extends CompilerBase {
    public function compile($source,$pathname){
        $scss = new scssc;
        $scss->setImportPaths(dirname($pathname));

        return $scss->compile($source);
    }

    public function getSectionKey(){
        return 'sass';
    }

    protected function getConvertFromExtension(){
        return 'scss';
    }

    protected function getConvertToExtension(){
        return 'css';
    }
}