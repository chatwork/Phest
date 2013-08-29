<!DOCTYPE html>
<html>
<head>
 <title>SmartBuilder {$ver}</title>
 <link href="./lib/bootstrap/css/bootstrap.css" rel="stylesheet">
<!--[if lt IE 9]>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.js"></script>
<![endif]-->
<!--[if gte IE 9]><!-->
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.js"></script>
<!--<![endif]-->
<script type="text/javascript" src="./lib/bootstrap/js/bootstrap.js"></script>
<style type="text/css">
body {
	padding: 0px 10px;
}
.toolBar {
	width: 500px;
	margin: 10px 10px;
}
.toolBar tbody tr {
	text-align: center;
}
</style>
<script type="text/javascript">
$(function(){
  $('#_createSite').click(function(){
    var sitename = prompt("Input new site's name",'');
    if (sitename){
      document.location.href = '?create_site=' + sitename;
    }
  });
})
</script>
</head>
<body>
<form method="get" action="build.php" class="form-horizontal" role="form">
<div class="toolBar">
<table class="table table-bordered">
 <thead>
  <tr style="text-align:center">
  	<td>Site</td>
  	<td>Build</td>
  </tr>
 </thead>
 <tbody>
  <tr>
   <td>
   	<select name="site" class="form-control">
     {foreach $site_list as $site_val}
      <option value="{$site_val}"{if $site_val == $site} selected{/if}>{$site_val}</option>
     {/foreach}
    </select>
    <a id="_createSite">Create new site</a>
   </td>
   <td>
    <input type="submit" name="build_local" class="btn btn-primary" value="Local">
    <input type="submit" name="build_production" class="btn btn-success" value="Production">
   </td>
  </tr>
 </tbody>
</table>
</div>
</form>

{foreach $message_list as $section => $section_dat}
<div>{$section_dat.title}</div>
 <ul>
  {foreach $section_dat.list as $msg}
  <li>{$msg}</li>
  {/foreach}
 </ul>
{/foreach}
