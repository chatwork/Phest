<!DOCTYPE html>
<!--[if IE 8 ]><html lang="ja" class="ie8"><![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html lang="ja"><!--<![endif]-->
<html>
 <head>
  <meta charset="UTF-8" />
  <meta name="description" content="{$description}"/>
  <title>{if isset($title)}{$title} - {/if}{$sitename}</title>
  <!--[if lt IE 9]>
   <script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
  <![endif]-->
  <link rel="shortcut icon" href="image/common/logo/favicon.ico?1" type="image/vnd.microsoft.icon"/>
  <link rel="stylesheet" media="all" type="text/css" href="style/chatwork_api.css">
  <link rel="stylesheet" media="all" type="text/css" href="style/prism.css">
 </head>
 <body>
  <div id="_wrapper" class="wrapper">
   {include file="_header.tpl"}
   <div id="_contentWrapper" class="contentWrapper clearfix">
    {include file="_side_navigation.tpl"}
    <div id="_mainContent" class="mainContent">
     {include file=$_content_tpl}
     {include file="_footer.tpl"}
    </div>
   </div>
  </div>
  <!--[if lt IE 9]>
  <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
  <![endif]-->
  <!--[if gte IE 9]><!-->
  <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
  <!--<![endif]-->
  <script type="text/javascript" src="javascript/modernizr.custom.js"></script>
  <script type="text/javascript" src="javascript/prism.js"></script>
  <script type="text/javascript" src="javascript/apidoc.js"></script>
 </body>
</html>