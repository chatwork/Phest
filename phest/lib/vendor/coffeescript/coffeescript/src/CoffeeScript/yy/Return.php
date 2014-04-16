<?php

namespace CoffeeScript;

class yy_Return extends yy_Base
{
  public $children = array('expression');

  function constructor($expr = NULL)
  {
    if ($expr && ! ($expr->unwrap()->is_undefined()))
    {
      $this->expression = $expr;
    }

    return $this;
  }

  function compile($options, $level = NULL)
  {
    $expr = (isset($this->expression) && $this->expression) ? $this->expression->make_return() : NULL;

    if ($expr && ! ($expr instanceof yy_Return))
    {
      $ret = $expr->compile($options, $level);
    }
    else
    {
      $ret = parent::compile($options, $level);
    }

    return $ret;
  }

  function compile_node($options)
  {
    return $this->tab.'return'.(isset($this->expression) && $this->expression ? ' '.$this->expression->compile($options, LEVEL_PAREN) : '').';';
  }

  function is_statement($options = NULL)
  {
    return TRUE;
  }

  function jumps()
  {
    return $this;
  }

  function make_return($res = NULL)
  {
    return $this;
  }
}

?>
