<?php
	$dir_sites = '../sites';
	$ver = 'v0.2';
	
	ini_set('display_errors','On');
	require('./lib/debuglib.php');
	require('./lib/smarty/Smarty.class.php');
	require('./lib/File.php');
	
	if (!is_dir($dir_sites)){
		die('dir_sites がディレクトリではありません');
	}
	
	$bsmarty = new Smarty;
	$bmsg = new BuildMessage;;
	
	$site_list = array();
	foreach (glob($dir_sites.'/*') as $site_dir){
		if (is_dir($site_dir)){
			$site_list[] = basename($site_dir);
		}
	}
	$bsmarty->assign('site_list',$site_list);
	
	$site = '';
	if (isset($_GET['site']) and in_array($_GET['site'],$site_list)){
		$site = $_GET['site'];
	}
	$bsmarty->assign('site',$site);
	
	if (!empty($_GET['build_local'])){
		$build = true;
		$buildtype = 'local';
	}else if (!empty($_GET['build_production'])){
		$build = true;
		$buildtype = 'production';
	}else{
		$build = false;
	}
	
	//site作成
	if (!empty($_GET['create_site'])){
		$create_site = trim($_GET['create_site']);
		
		File::copyDir('./blanksite/',$dir_sites.'/'.$create_site);
		$path_config_yml = $dir_sites.'/'.$create_site.'/config.yml';
		file_put_contents($path_config_yml,strtr(file_get_contents($path_config_yml),array('{{site}}' => $create_site)));
		
		header('Location: ?site='.$create_site);
		exit;
	}
	
	
	//build実行
	if ($build and $site){
		define('SMARTBUILDER_BUILTTYPE',$buildtype);
		
		$build_class = '';
		switch ($buildtype){
			case 'local':
				$build_class = 'text-primary';
				break;
			case 'production':
				$build_class = 'text-success';
				break;
				
		}
		$bmsg->registerSection('build','Build <strong>'.$site.'</strong> for <strong class="'.$build_class.'">'.$buildtype.'</strong> at '.date('H:i:s'));
		
		$dir_site = $dir_sites.'/'.$site;
		$dir_source = $dir_site.'/source';
		$dir_pages = $dir_source.'/pages';
		$dir_style = $dir_source.'/style';
		$dir_javascript = $dir_source.'/javascript';
		$dir_output = $dir_site.'/htdocs';
		$path_config_yml = $dir_site.'/config.yml';
		$path_vars_yml = $dir_site.'/vars.yml';
		
		require('./lib/spyc.php');
		require('./lib/lessphp/lessc.inc.php');
		require('./lib/scssphp/scss.inc.php');
		
		//yaml load
		if (!file_exists($path_config_yml)){
			die('config.ymlが見つかりません。path='.$path_config_yml);
		}
		if (!file_exists($path_vars_yml)){
			die('vars.ymlが見つかりません。path='.$path_vars_yml);
		}
		$config_yaml = spyc_load_file($path_config_yml);
		$vars_yaml = spyc_load_file($path_vars_yml);
		
		$home = $config_yaml['home'][$buildtype];
		$home_local = $config_yaml['home']['local'];
		if (!$home){
			die('config.ymlにhomeが正しく設定されていません');
		}
		
		$bmsg->add('build','home path: <a href="'.$home.'" target="_blank">'.$home.'</a>');
		$bmsg->add('build','site filepath: '.realpath($dir_site));
		
		$bmsg->registerSection('create','File created:');
		
		$smarty = new Smarty;
		$smarty->template_dir = array($dir_source,'./templates');
		$smarty->addPluginsDir('./plugins');
		
		$dir_compile = './templates_c/'.$site;
		File::buildMakeDir($dir_compile);
		$smarty->compile_dir = $dir_compile;
		
		//ページをスキャン
		$urls = array();
		$ite = new RecursiveDirectoryIterator($dir_pages);
		foreach (new RecursiveIteratorIterator($ite) as $pathname => $path){
			$pagepath = pathinfo($pathname);
			if (isset($pagepath['extension'] ) and $pagepath['extension'] == 'tpl'){
				$dirname = ltrim(substr($pagepath['dirname'],strlen($dir_pages)),'\\/');
			
				if ($dirname){
					$rpath = $dirname.'/'.$pagepath['filename'];
					$dirname = '/'.$dirname;
				}else{
					$rpath = $pagepath['filename'];
				}
				$smarty->assign('_pagename',$pagepath['filename']);
				
				//for canonical
				if ($pagepath['filename'] == 'index'){
					$path_current = substr($rpath,0,strlen($rpath) - 5);
					$changefreq = 'daily';
					$priority = '1.0';
				}else{
					$path_current = $rpath.'.html';
					$changefreq = 'monthly';
					$priority = '0.5';
				}
				
				$urls[] = array('path' => $path_current,'lastmod' => date('c',filemtime($pathname)),'changefreq' => $changefreq,'priority' => $priority);
				
				//vars.ymlで読み出すセクションのリストを生成
				$pages_section = array();
				$page_tmp = '';
				foreach (explode('/',$dirname) as $page) {
					if ($page){
						$pages_section[] = $page_tmp.$page.'/';
						$page_tmp .= $page.'/';
					}
				}
				$pages_section[] = $page_tmp.$pagepath['filename'];
				
				
				//smartyのアサイン変数をクリア
				$smarty->clearAllAssign();
				
				$smarty->assign('_home',$home);
				$smarty->assign('_path',ltrim($dirname.'/'.$pagepath['filename'],'\\/'));
				$smarty->assign('_content_tpl','pages'.$dirname.'/'.$pagepath['basename']);
				
				$page_vars = $vars_yaml['common'];
				if (!is_array($page_vars)){
					$page_vars = array();
				}
				foreach ($pages_section as $psect){
					if (isset($vars_yaml['path'][$psect]) and is_array($vars_yaml['path'][$psect])){
						$page_vars = array_merge_recursive_distinct($page_vars,$vars_yaml['path'][$psect]);
					}
				}
				
				$smarty->assign($page_vars);
				
				$filepath = $dirname.'/'.$pagepath['filename'].'.html';
				
				$output_html = $smarty->fetch('parts/base.tpl');
				
				if (!empty($config_yaml['encode'])){
					$output_html = mb_convert_encoding($output_html, $config_yaml['encode']);
				}
				File::buildPutFile($dir_output.$filepath,$output_html);
				$bmsg->add('create','<a href="'.$home_local.$filepath.'" target="_blank">'.$filepath.'</a>');
			}
		}
		
		$smarty->clearAllAssign();
		$smarty->assign('_home',$home);
		
		if (file_exists($dir_style)){
			//css
			foreach (glob($dir_style.'/*.css') as $path_css){
				$basename_css = basename($path_css);
				$filepath = '/style/'.$basename_css;
				
				$create_option = '';
				if (strpos($basename_css,'.tpl') !== false){
					$source = $smarty->fetch('style/'.$basename_css);
					$create_option = ' (smarty)';
				}else{
					$source = file_get_contents($path_css);
				}
				
				File::buildPutFile($dir_output.$filepath,$source);
				$bmsg->add('create','<a href="'.$home_local.$filepath.'" target="_blank">'.$filepath.'</a>'.$create_option);
			}
			
			
			//less
			$less = new lessc;
			foreach (glob($dir_style.'/*.less') as $path_less){
				$basename_less = basename($path_less);
				if (substr($basename_less,0,1) !== '_'){
					$filepath = '/style/'.$basename_less.'.css';
					
					$create_option = '';
					if (strpos($basename_less,'.tpl') !== false){
						$source = $smarty->fetch('style/'.$basename_less);
						$create_option = ' (smarty)';
					}else{
						$source = file_get_contents($path_less);
					}
					
					File::buildPutFile($dir_output.$filepath,$less->compile($source));
					$create_option .= ' (less)';
					$bmsg->add('create','<a href="'.$home_local.$filepath.'" target="_blank">'.$filepath.'</a>'.$create_option);
				}
			}
			
			//scss
			$scss = new scssc;
			$scss->setImportPaths($dir_style);
			foreach (glob($dir_style.'/*.scss') as $path_scss){
				$basename_scss = basename($path_scss);
				if (substr($basename_scss,0,1) !== '_'){
					
					$create_option = '';
					if (strpos($basename_scss,'.tpl') !== false){
						$source = $smarty->fetch('style/'.$basename_scss);
						$create_option = ' (smarty)';
					}else{
						$source = file_get_contents($path_scss);
					}
					
					$filepath = '/style/'.$basename_scss.'.css';
					File::buildPutFile($dir_output.$filepath,$scss->compile($source));
					$create_option .= ' (scss)';
					$bmsg->add('create','<a href="'.$home_local.$filepath.'" target="_blank">'.$filepath.'</a>'.$create_option);
				}
			}
		}
		
		//javascript
		if (file_exists($dir_javascript)){
			$bmsg->registerSection('jslint','JavaScript Lint');
			foreach (glob($dir_javascript.'/*.js') as $path_js){
				$basename_js = basename($path_js);
				
				//lint check
				$lint_error = jslint($path_js);
				
				if ($lint_error){
					foreach ($lint_error as $lerr){
						$bmsg->add('jslint',$basename_js.':'.$lerr);
					}
				}
				
				$filepath = '/javascript/'.$basename_js;
				
				$create_option = '';
				if (strpos($basename_js,'.tpl') !== false){
					$source = $smarty->fetch('style/'.$basename_js);
					$create_option = ' (smarty)';
				}else{
					$source = file_get_contents($path_scss);
				}
				File::buildPutFile($dir_output.$filepath.'.tmp',file_get_contents($path_js));
				
				//本番環境かつcompilejs=1なら圧縮
				if ($buildtype == 'production' and !empty($config_yaml['compilejs'])){
					compile($dir_output.$filepath.'.tmp',$dir_output.$filepath);
					unlink($dir_output.$filepath.'.tmp');
					$create_option .= ' (minified)';
				}else{
					rename($dir_output.$filepath.'.tmp',$dir_output.$filepath);
				}
				$bmsg->add('create','<a href="'.$home_local.$filepath.'" target="_blank">'.$filepath.'</a>'.$create_option);
			}
		}
		
		//サイトマップ生成
		if (!empty($config_yaml['sitemap'])){
			$smarty->assign('_urls',$urls);
			$filepath = '/sitemap.xml';
			file_put_contents($dir_output.$filepath,$smarty->fetch('_sitemap_xml.tpl'));
			$bmsg->add('create','<a href="'.$home.$filepath.'" target="_blank">'.$filepath.'</a>');
		}
		
		//robots.txt
		if (!empty($config_yaml['robotstxt'])){
			$filepath = '/robots.txt';
			file_put_contents($dir_output.$filepath,$smarty->fetch('_robots_txt.tpl'));
			$bmsg->add('create','<a href="'.$home.$filepath.'" target="_blank">'.$filepath.'</a>');
		}
	}
	
	$bsmarty->assign('ver',$ver);
	$bsmarty->assign('message_list',$bmsg->getData());
	$bsmarty->display('_build.tpl');

class BuildMessage {
	protected $message_data = array();
	
	public function registerSection($section,$title){
		$this->message_data[$section] = array('title' => $title,'list' => array());
	}
	
	public function add($section,$message){
		$this->message_data[$section]['list'][] = $message;
	}
	
	public function getData(){
		$msg_data = array();
		
		foreach ($this->message_data as $section => $mdat){
			if (count($mdat['list'])){
				$msg_data[$section] = $mdat;
			}
		}
		
		return $msg_data;
	}
}

function array_merge_recursive_distinct ( array &$array1, array &$array2 )
{
  $merged = $array1;

  foreach ( $array2 as $key => &$value )
  {
    if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) )
    {
      $merged [$key] = array_merge_recursive_distinct ( $merged [$key], $value );
    }
    else
    {
      $merged [$key] = $value;
    }
  }

  return $merged;
}


/**
 * コンパイルを実行
 */
function compile($source_from,$output_to){
	//OS判定
	switch(PHP_OS){
		case 'Darwin':
			$os = 'mac';
			break;
		case 'WIN32':
		case 'WINNT':
			$os = 'win';
			break;
		default:
			die('サポートしていないOSです:'.PHP_OS);
			break;
	}
	
	if (file_exists($output_to)){
		unlink($output_to);
	}
	
	$compile_command = 'java -jar '.dirname(__FILE__).'/lib/compiler/closurecompiler/compiler.jar --compilation_level SIMPLE_OPTIMIZATIONS --js '.$source_from.' --js_output_file '.$output_to;
	
	$compile_output = array();
	if ($os == 'mac'){
		$compile_command = 'export DYLD_LIBRARY_PATH="";'.$compile_command;
	}
	exec($compile_command,$compile_output);
	
	chmod($output_to,0777);
}

/**
 * JavaScript構文チェック
 */
function jslint($jspath){
	//OS判定
	switch(PHP_OS){
		case 'Darwin':
			$os = 'mac';
			break;
		case 'WIN32':
		case 'WINNT':
			$os = 'win';
			break;
		default:
			die('サポートしていないOSです:'.PHP_OS);
			break;
	}
	
	$lint_output = array();
	$cmd = './lib/jsl/'.$os.'/jsl -conf ./lib/jsl/ec.conf -process '.$jspath.' 2>&1';
	$cmd = strtr($cmd,'/',DIRECTORY_SEPARATOR);
	
	exec($cmd,$lint_output);
	
	$lint_error = array();
	if (count($lint_output) > 6){
		for ($i = 4;$i < (count($lint_output) - 2);$i++){
			$lint_error[] = $lint_output[$i];
		}
	}
	
	return $lint_error;
}

function bytename($size,$unit = 'B'){
	$unim = array('','K','M','G','T','P');
	$c = 0;
	while ($size >= 1024) {
		$c++;
		$size = $size / 1024;
	}
	return number_format($size,($c ? 2 : 0),'.',',').' '.$unim[$c].$unit;
}
