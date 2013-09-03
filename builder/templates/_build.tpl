<!DOCTYPE html>
<html>
<head>
 <title>SmartBuilder {$ver}</title>
 <link href="./assets/bootstrap/css/bootstrap.css" rel="stylesheet">
<!--[if lt IE 9]>
<script type="text/javascript" src="./assets/jquery-1.10.2.min.js"></script>
<![endif]-->
<!--[if gte IE 9]><!-->
<script type="text/javascript" src="./assets/jquery-2.0.3.min.js"></script>
<!--<![endif]-->
<script type="text/javascript" src="./assets/bootstrap/js/bootstrap.js"></script>
<script type="text/javascript" src="./assets/underscore-min.js"></script>
<script type="text/javascript" src="./assets/NotificationAPI.js"></script>
<script type="text/javascript" src="./assets/common.js"></script>
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
  var query = {};
  parse_str(window.location.search.substr(1),query);
  
  var build = '';
  if (query.build){
    build = query.build;
  }
  
  $('#site').change(function(){
    document.location.href = '?site=' + $(this).val();
  });
  
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

  var watch_timer = null;
  $('#buildLocalWatch').click(function(){
    //デスクトップ通知
    if (NotificationAPI.checkPermission() != 0){
      NotificationAPI.requestPermission();
    }
    
    if (build != 'watch'){
      document.location.href = '?build=watch&site=' + $('#site').val();
    }
    
    $('#result').fadeOut();
    
    var watching = false;
    if (watch_timer){
      $('#watchStatus').hide();
      
      clearInterval(watch_timer);
      watch_timer = null;
      $(this).text('Local watch');
      $('#buildLocal').removeClass('disabled');
      $('#buildProduction').removeClass('disabled');
    }else{
      $('#watchStatus').fadeIn();
      
      watch_timer = setInterval(function(){
        if (!watching){
          watching = true;
          $.getJSON('?build=local&watch=1&site=' + $('#site').val(),function(data){
            if (data){
              build_result(data.message_list);
              
              for (var i = 0,sec_len = data.message_list.length;i < sec_len;i++){
                var section = data.message_list[i];
                
                var body = '';
                switch (section.type){
                  case 'danger':
                    var body = section.list.length + ' errors';
                  case 'success':
                    (function(){
                      var popup = NotificationAPI.createNotification('./assets/image/' + section.type + '.png',strip_tags(section.title),body);
                      popup.onclick = function(){
                        window.focus();
                        this.cancel();
                      };
                      popup.show();
                      
                      setTimeout(function(){
                        popup.close();
                      },3000);
                    })();
                    break;
                  default:
                    break;
                }
              }
            }
            watching = false;
          });
        }
      },1000);
      $(this).text('pause');
      $('#buildLocal').addClass('disabled');
      $('#buildProduction').addClass('disabled');
    }
  });

  $('#buildProduction').click(function(){
    $('#result').fadeOut();
    document.location.href = '?build=production&site=' + $('#site').val();
  });
  
  var message_list_tpl = _.template($('#messageListTemplate').html());
  
  var build_result = function(message_list){
    $('#result').html(message_list_tpl({
      message_list:message_list
    })).show();
  };
  
  var message_list = {if isset($message_list)}{$message_list|json_encode}{else}null{/if};
  
  if (message_list){
    build_result(message_list);
  }
  
  if (build == 'watch'){
     $('#buildLocalWatch').click();
  }
});
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
    <button id="buildLocalWatch" class="btn btn-primary">Local watch</button>
    <button id="buildProduction" class="btn btn-success">Production</button>
   </td>
  </tr>
 </tbody>
</table>
</section>



<div id="watchStatus" style="padding:5px;display:none"><img src="./assets/image/ajax-loader.gif" style="width:25px;height:25px"/>Watching....</div>
<section id="result" class="resultSection"></section>

<script id="messageListTemplate" type="text/template">
 <% _.each(message_list,function(section_dat){ %>
  <div class="panel panel-<%= section_dat.type %>">
   <div class="panel-heading">
    <h3 class="panel-title"><%= section_dat.title %></h3>
   </div>
   <div class="panel-body">
    <ul>
     <% _.each(section_dat.list,function(msg){ %>
      <li><%= msg %></li>
     <% }); %>
    </ul>
   </div>
  </div>
 <% }); %>
</script>