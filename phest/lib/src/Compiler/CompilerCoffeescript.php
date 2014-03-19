<?php
    namespace ChatWork\Phest;
    
class CompilerCoffeescript extends CompilerBase {
    public function compile($source,$pathname){
        return \CoffeeScript\Compiler::compile($source,array('filename' => $pathname));
    }

    public function getSectionKey(){
        return 'coffeescript';
    }

    public function getOptionLabel(){
        return 'coffeescript-php';
    }

    protected function getConvertFromExtension(){
        return 'coffee';
    }

    protected function getConvertToExtension(){
        return 'js';
    }
}