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
section {
  padding:0px 10px;
  max-width:700px;
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
  $('#createSite').click(function(){
    var sitename = prompt("Input new site's name",'');
    if (sitename){
      document.location.href = '?create_site=' + sitename;
    }
  });

  $('#buildLocal').click(function(){
    $('#result').fadeOut();
    document.location.href = '?build=local&site=' + $('#site').val();
  });

  $('#buildProduction').click(function(){
    $('#result').fadeOut();
    document.location.href = '?build=production&site=' + $('#site').val();
  });
})
</script>
</head>
<body>
<section class="toolBar">
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
   	<select id="site" name="site" class="form-control">
     {foreach $site_list as $site_val}
      <option value="{$site_val}"{if $site_val == $site} selected{/if}>{$site_val}</option>
     {/foreach}
    </select>
    <a id="createSite">Create new site</a>
   </td>
   <td>
    <button id="buildLocal" class="btn btn-primary">Local</button>
    <button id="buildProduction" class="btn btn-success">Production</button>
   </td>
  </tr>
 </tbody>
</table>
</section>




<section id="result" class="resultSection">
{foreach $message_list as $section => $section_dat}
<div class="panel panel-{$section_dat.type}">
 <div class="panel-heading">
  <h3 class="panel-title">{$section_dat.title}</h3>
 </div>
 <div class="panel-body">
  <ul>
   {foreach $section_dat.list as $msg}
   <li>{$msg}</li>
   {/foreach}
  </ul>
 </div>
</div>
{/foreach}
</section>