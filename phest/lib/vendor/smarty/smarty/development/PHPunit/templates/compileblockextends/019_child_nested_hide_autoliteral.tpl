{ extends file="019_parent.tpl" }

{ block name="index" }
   { block name="test2" }
      nested block.
      { $smarty.block.child }
   { /block }
   { block name="test" hide }
      I should be hidden.
      { $smarty.block.child }
   { /block }
{ /block } 