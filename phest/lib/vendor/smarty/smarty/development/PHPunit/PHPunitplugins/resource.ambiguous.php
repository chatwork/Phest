<?php

/**
 * Ambiguous Filename Custom Resource Example
 *
 * @package Resource-examples
 * @author Rodney Rehm
 */
class Smarty_Resource_Ambiguous extends Smarty_Internal_Resource_File
{
    protected $directory;
    protected $segment;

    public function __construct($directory)
    {
        $this->directory = rtrim($directory, "/\\") . DS;
    }

    public function setSegment($segment)
    {
        $this->segment = $segment;
    }

    /**
     * modify resource_name according to resource handlers specifications
     *
     * @param  Smarty $smarty        Smarty instance
     * @param  string $resource_name resource_name to make unique
     * @return string unique resource name
     */
    protected function buildUniqueResourceName(Smarty $smarty, $resource_name, $is_config = false)
    {
        return get_class($this) . '#' . $this->segment . '#' . $resource_name;
    }

    /**
     * populate Source Object with meta data from Resource
     *
     * @param Smarty_Template_Source   $source    source object
     * @param Smarty_Internal_Template $_template template object
     */
    public function populate(Smarty_Template_Source $source, Smarty_Internal_Template $_template=null)
    {
        $segment = '';
        if ($this->segment) {
            $segment = rtrim($this->segment, "/\\") . DS;
        }

        $source->filepath = $this->directory . $segment . $source->name;
        $source->uid = sha1($source->filepath);
        if ($source->smarty->compile_check && !isset($source->timestamp)) {
            $source->timestamp = @filemtime($source->filepath);
            $source->exists = !!$source->timestamp;
        }
    }
}
