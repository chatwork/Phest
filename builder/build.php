<?php
	$dir_sites = '../sites';
	$ver = 'v0.1';
	
	ini_set('display_errors','On');
	require('./lib/debuglib.php');
	require('./lib/smarty/Smarty.class.php');
	
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
	
	if ($build and $site){
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
		$dir_output = $dir_site.'/htdocs';
		$path_config_yml = $dir_site.'/config.yml';
		
		
		require('./lib/spyc.php');
		require('./lib/File.php');
		require('./lib/lessphp/lessc.inc.php');
		require('./lib/scssphp/scss.inc.php');
		
		//yaml load
		if (!file_exists($path_config_yml)){
			die('config.ymlが見つかりません。path='.$path_config_yml);
		}
		$yaml = spyc_load_file($path_config_yml);
		
		if (!isset($yaml['vars']) or !is_array($yaml['vars'])){
			die('config.ymlが正しくありません');
		}
		if (!isset($yaml['vars']['common']) or !is_array($yaml['vars']['common'])){
			$yaml['vars']['common'] = array();
		}
		
		$yaml_vars = array_merge_recursive_distinct($yaml['vars']['common'],$yaml['vars'][$buildtype]);
		
		if (!isset($yaml_vars['_home'])){
			die('config.ymlに_homeが指定されていません');
		}
		
		$bmsg->add('build','home path: <a href="'.$yaml_vars['_home'].'" target="_blank">'.$yaml_vars['_home'].'</a>');
		$bmsg->add('build','site filepath: '.realpath($dir_site));
		
		$bmsg->add('build','yaml vars: '.print_a($yaml_vars,'r:1;y:1000;'));
		$bmsg->registerSection('create','File created:');
		
		$smarty = new Smarty;
		$smarty->template_dir = array($dir_source,'./templates');
		
		$dir_compile = './templates_c/'.$site;
		File::buildMakeDir($dir_compile);
		$smarty->compile_dir = $dir_compile;
		
		$smarty->assign($yaml_vars);
		
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
					$changefreq = 'monthly';
					$priority = '0.5';
					$path_current = $rpath.'.html';
				}
				$urls[] = array('path' => $path_current,'lastmod' => date('c',filemtime($pathname)),'changefreq' => $changefreq,'priority' => $priority);
				$smarty->assign('_path_current',$path_current);
				
				$smarty->assign('_content_tpl','pages'.$dirname.'/'.$pagepath['basename']);
				$filepath = $dirname.'/'.$pagepath['filename'].'.html';
				File::buildPutFile($dir_output.$filepath,$smarty->fetch('parts/base.tpl'));
				$bmsg->add('create','<a href="'.$yaml_vars['_home'].$filepath.'" target="_blank">'.$filepath.'</a>');
			}
		}
		
		if (file_exists($dir_style)){
			
			//less
			$less = new lessc;
			foreach (glob($dir_style.'/*.less') as $path_less){
				$basename_less = basename($path_less);
				if (substr($basename_less,0,1) !== '_'){
					File::buildMakeDir($dir_output.'/style');
					$filepath = '/style/'.$basename_less.'.css';
					$less->checkedCompile($path_less,$dir_output.$filepath);
					$bmsg->add('create','<a href="'.$yaml_vars['_home'].$filepath.'" target="_blank">'.$filepath.'</a>');
				}
			}
			
			//scss
			$scss = new scssc;
			$scss->setImportPaths($dir_style);
			foreach (glob($dir_style.'/*.scss') as $path_scss){
				$basename_scss = basename($path_scss);
				if (substr($basename_scss,0,1) !== '_'){
					$filepath = '/style/'.$basename_scss.'.css';
					File::buildPutFile($dir_output.$filepath,$scss->compile(file_get_contents($path_scss)));
					$bmsg->add('create','<a href="'.$yaml_vars['_home'].$filepath.'" target="_blank">'.$filepath.'</a>');
				}
			}
		}
		
		//#TODO javascript compile
		
		//サイトマップ生成
		$smarty->assign('_urls',$urls);
		$filepath = '/sitemap.xml';
		file_put_contents($dir_output.$filepath,$smarty->fetch('_sitemap_xml.tpl'));
		$bmsg->add('create','<a href="'.$yaml_vars['_home'].$filepath.'" target="_blank">'.$filepath.'</a>');
		
		//robots.txt
		$filepath = '/robots.txt';
		file_put_contents($dir_output.$filepath,$smarty->fetch('_robots_txt.tpl'));
		$bmsg->add('create','<a href="'.$yaml_vars['_home'].$filepath.'" target="_blank">'.$filepath.'</a>');
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
		return $this->message_data;
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
function compile($raw_source,$output_to,$gzip_output_to = ''){
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
	
	switch (COMPILER_TYPE){
		case 'yui':
			$compile_command = 'java -jar '.PATH_COMPILER.' '.$raw_source.' -o '.$output_to.' --charset UTF-8';
			break;
		case 'google':
			$compile_command = 'java -jar '.PATH_COMPILER.' --compilation_level SIMPLE_OPTIMIZATIONS --js '.$raw_source.' --js_output_file '.$output_to;
			break;
		case 'google-advance':
			$compile_command = 'java -jar '.PATH_COMPILER.' --compilation_level ADVANCED_OPTIMIZATIONS --js '.$raw_source.' --js_output_file '.$output_to;
			break;
		default:
			die('認識できないコンパイラです:'.COMPILER_TYPE);
	}
	
	fecho('Compile: complier type = '.COMPILER_TYPE.'<br />');
	fecho('<div class="command">'.$compile_command.'</div>');
	
	$compile_output = array();
	if ($os == 'mac'){
		$compile_command = 'export DYLD_LIBRARY_PATH="";'.$compile_command;
	}
	exec($compile_command,$compile_output);
	
	chmod($raw_source,0777);
	echo $output_to;
	chmod($output_to,0777);
	
	//gzip圧縮
	if (file_exists($output_to)){
		if ($gzip_output_to){
			file_put_contents($gzip_output_to,gzencode(file_get_contents($output_to),9));
			chmod($gzip_output_to,0777);
			fecho('created:'.$gzip_output_to.'<br />');
		}
		
		$byte_from = filesize($raw_source);
		$byte_to = filesize($output_to);
		
		if (!$byte_to){
			fecho('<div class="alert">Compile Error!　JavaScriptエラーの可能性があります。詳しいエラー内容は上記のコマンドラインを実行してコンパイラのエラー内容を確認してください。</div>');
		}
		
		fecho('<div class="done">done!<br />'.basename($raw_source).'(raw): '.bytename($byte_from).'<br />'.basename($output_to).'(compress): <b>'.bytename($byte_to).'</b><br />');
		
		if ($gzip_output_to){
			$byto_gz_to = bytename(filesize($gzip_output_to));
			fecho(basename($gzip_output_to).'(compress + gzip): <b>'.$byto_gz_to.'</b><br />');
		}
		
		fecho('</div>');
	}else{
		fecho('<hr />error!<br />Compiler didn\'t work');
	}
	
	if ($compile_output){
		print_a($compile_output);
	}
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
