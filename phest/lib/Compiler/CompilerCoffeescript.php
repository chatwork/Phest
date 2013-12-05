<?php
    namespace ChatWork\Phest;

    require(DIR_PHEST.'/lib/vendor/CoffeeScript/Init.php');
    \CoffeeScript\Init::load();

class CompilerCoffeescript extends CompilerBase {
    public function compile($source,$pathname){
        return \CoffeeScript\Compiler::compile($source,array('filename' => $pathname));
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