<?php
	namespace ChatWork\SmartBuilder;
	
/**
 * SmartBuilder https://github.com/chatwork/SmartBuilder
 * 
 * Licensed under MIT, see LICENSE
 * 
 * @link https://github.com/chatwork/SmartBuilder
 * @copyright 2013 ChatWork Inc
 * @author Masaki Yamamoto (https://chawork.com/cw_masaki)
 */
	use \RecursiveDirectoryIterator;
	use \RecursiveIteratorIterator;
	use \FilesystemIterator;
	use \Michelf\Markdown;
	
	define('DIR_BUILDER',dirname(__FILE__));
	require(DIR_BUILDER.'/config.php');
	
	$ver = 'v0.4.5b';
	
	error_reporting(E_ALL);
	ini_set('display_errors','On');
	
	require(DIR_BUILDER.'/lib/function.php');
	require(DIR_BUILDER.'/lib/globalfunction.php');
	require(DIR_BUILDER.'/lib/BuildMessage.php');
	
	require(DIR_BUILDER.'/lib/File.php');
	use \ChatWork\Utility\File;
	
	require(DIR_BUILDER.'/lib/vendor/smarty/Smarty.class.php');	
	use \Smarty;
	require(DIR_BUILDER.'/lib/vendor/lessphp/lessc.inc.php');
	use \lessc;
	require(DIR_BUILDER.'/lib/vendor/scssphp/scss.inc.php');
	use \scssc;
	require(DIR_BUILDER.'/lib/vendor/cssmin/cssmin-v3.0.1.php');
	use \CssMin;
	
	require(DIR_BUILDER.'/lib/vendor/debuglib.php');
	require(DIR_BUILDER.'/lib/vendor/spyc/spyc.php');

	$bsmarty = new Smarty;
	$bsmarty->compile_dir = DIR_BUILDER.'/cache/templates_c';
	$bmsg = new BuildMessage;
	
	$site_list = array();
	foreach (glob(DIR_SITES.'/*') as $site_dir){
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
	
	$buildtype = '';
	if (!empty($_GET['build'])){
		$buildtype = $_GET['build'];
	}
	
	//site作成
	if (!empty($_GET['create_site'])){
		$create_site = trim($_GET['create_site']);
		$path_create_site = DIR_SITES.'/'.$create_site;
		if (!file_exists($path_create_site)){
			File::buildMakeDir(DIR_SITES);
			File::copyDir('./sitetemplates/default',DIR_SITES.'/'.$create_site);
			$path_config_yml = DIR_SITES.'/'.$create_site.'/source/config.yml';
			file_put_contents($path_config_yml,strtr(file_get_contents($path_config_yml),array('{{site}}' => $create_site)));
			
			header('Location: ?site='.$create_site);
			exit;
		}else{
			die('すでに '.$create_site.' というサイトが存在します');
		}
	}
	
	//watch mode
	$watch = 0;
	if (!empty($_GET['watch'])){
		$watch = 1;
	}
	
	$build = true;
	$build_class = '';
	switch ($buildtype){
		case 'local':
			$build_class = 'text-primary';
			break;
		case 'production':
			$build_class = 'text-success';
			break;
		default:
			$build = false;
			break;
	}
	
	//build実行
	if ($build and $site){
		define('SMARTBUILDER_BUILTTYPE',$buildtype);
		
		$bmsg->registerSection('build','Build <strong>'.$site.'</strong> for <strong class="'.$build_class.'">'.$buildtype.'</strong> at '.date('H:i:s'));
		
		$dir_site = DIR_SITES.'/'.$site;
		$dir_source = $dir_site.'/source';
		$dir_content = $dir_source.'/content';
		$path_config_yml = $dir_source.'/config.yml';
		$path_vars_yml = $dir_source.'/vars.yml';
		$dir_output = $dir_site.'/output/'.$buildtype;
		
		File::buildMakeDir($dir_output);
		
		//yaml load
		if (!file_exists($path_config_yml)){
			die('config.ymlが見つかりません。path='.$path_config_yml);
		}
		if (!file_exists($path_vars_yml)){
			die('vars.ymlが見つかりません。path='.$path_vars_yml);
		}
		if (!file_exists($dir_content)){
			die('contentフォルダが見つかりません。path='.$dir_content);
		}
		$config_yaml = array_merge(spyc_load_file(DIR_BUILDER.'/default_config.yml'),spyc_load_file($path_config_yml));
		$vars_yaml = array_merge(spyc_load_file(DIR_BUILDER.'/default_vars.yml'),spyc_load_file($path_vars_yml));

		if (!isset($vars_yaml['common']) or !is_array($vars_yaml['common'])){
			$vars_yaml['common'] = array();
		}
		if (!isset($vars_yaml[$buildtype]) or !is_array($vars_yaml[$buildtype])){
			$vars_yaml[$buildtype] = array();
		}
		$core_vars_yaml = array_merge_recursive_distinct($vars_yaml['common'],$vars_yaml[$buildtype]);
		
		$home = $config_yaml['home'][$buildtype];
		if (!$home){
			die('config.ymlにhomeが正しく設定されていません');
		}
		$home_local = '../sites/'.$site.'/output/'.$buildtype;
		
		$bmsg->registerSection('create','Created files',array('type' => 'info','sort' => true));
		$bmsg->registerSection('builderror','Build option error',array('type' => 'danger'));
		
		//Smarty
		$smarty = new Smarty;
		$smarty->template_dir = array($dir_content,DIR_BUILDER.'/templates');
		$smarty->compile_dir = DIR_BUILDER.'/cache/templates_c/'.$site;
		$smarty->addPluginsDir(DIR_BUILDER.'/plugins');
		File::buildMakeDir($smarty->compile_dir);
		$bmsg->registerSection('smartyerror','Smarty compile error',array('type' => 'danger'));
		
		//less
		$less = new lessc;
		$bmsg->registerSection('lesserror','LESS parse error',array('type' => 'danger'));
		
		//scss
		$scss = new scssc;
		$bmsg->registerSection('scsserror','SCSS parse error',array('type' => 'danger'));
		
		//coffee
		$bmsg->registerSection('coffeeerror','Coffee Script parse error',array('type' => 'danger'));
		
		//jslint
		$bmsg->registerSection('jslint','JavaScript lint warning',array('type' => 'danger'));
		$bmsg->registerSection('jscompileerror','JavaScript compile error',array('type' => 'danger'));
		
		//ページをスキャン
		
		$dir_buildstatus = DIR_BUILDER.'/cache/buildstatus';
		$path_buildstatus_site = $dir_buildstatus.'/'.$site.'.dat';
		
		//ソースフォルダの全ファイルをスキャン。新しいファイルがあるかどうかの判定に使う。
		$path_concat_string = '';
		$buildtime = 0;
		$pathhash = '';
		if (file_exists($path_buildstatus_site)){
			$buildtime = filemtime($path_buildstatus_site);
			$pathhash = file_get_contents($path_buildstatus_site);
		}
		
		$has_new = false;
		$watch_list = array();
		
		//configのbuildオプションを処理
		$concat_list = array();
		$copyto_list = array();
		if (isset($config_yaml['build'])){
			foreach ($config_yaml['build'] as $build_dat){
				foreach ($build_dat as $command => $option){
					switch ($command){
						case 'concat':
							if (!isset($concat_list[$option['output']])){
								$concat_list[$option['output']] = array();
							}
							foreach ($option['sources'] as $spath){
								if (file_exists($spath)){
									$watch_list[] = $spath;
									$concat_list[$option['output']][] = $spath;
								}else{
									$bmsg->add('builderror','[concat] source file not exist: '.$spath);
								}
							}
							break;
						case 'copydir':
							$is_valid_dir = true;
							if (!is_dir($option['fromdir'])){
								$is_valid_dir = false;
								$bmsg->add('builderror','[copydir] fromdir is not directory: '.$option['fromdir']);
							}
							
							if ($is_valid_dir){
								$watch_list = array_merge($watch_list,File::getFileList($option['fromdir']));
								$copyto_list[$option['fromdir']] = $option['todir'];
							}
							break;
					}
				}
			}
		}
		
		$watch_list = array_merge($watch_list,File::getFileList($dir_source));
		
		foreach ($watch_list as $filepath){
			if (!$has_new and ($buildtime < filemtime($filepath))){
				$has_new = true;
			}
			$path_concat_string .= ':'.$filepath;
		}
		
		//ファイルパスを全部つないだ文字列のハッシュをとる
		$source_pathhash = md5($path_concat_string);
		//ファイルパスの変更があるか
		if ($pathhash != $source_pathhash){
			$has_new = true;
			$pathhash = $source_pathhash;
		}
		
		if ($watch and !$has_new){
			exit;
		}
		
		//concatを処理
		if ($concat_list){
			foreach ($concat_list as $output_to => $cpath_list){
				$output_source = '';
				foreach ($cpath_list as $cpath){
					$output_source .= file_get_contents($cpath);
				}
				$bmsg->add('build','concat '.count($cpath_list).' files to source/<b>'.$output_to.'</b>');
				File::buildPutFile($dir_source.'/'.$output_to, $output_source);
				$output_source = '';
			}
		}
		
		//copydirを処理
		if ($copyto_list){
			foreach ($copyto_list as $copyfrom => $copyto){
				$bmsg->add('build','copy directory from <b>'.$copyfrom.'</b> to <b>'.$copyto.'</b>');
				File::copyDir($copyfrom, $dir_source.'/'.$copyto);
			}
		}
		
		$build_option = '';
		if (!empty($config_yaml['buildclear'])){
			File::removeDir($dir_output);
			mkdir($dir_output,0777);
			$build_option = ' (cleared)';
		}
		if ($build_option){
			$build_option = ' <code>'.trim($build_option).'</code>';
		}
		$bmsg->add('build','build from: '.realpath($dir_source));
		$bmsg->add('build','build to: <a href="'.$home_local.'" target="_blank">'.realpath($dir_output).'</a>'.$build_option);
		
		if (class_exists('FilesystemIterator',false)){
			$ite = new RecursiveDirectoryIterator($dir_content,FilesystemIterator::SKIP_DOTS);
		}else{
			$ite = new RecursiveDirectoryIterator($dir_content);
		}
		
		$urls = array();
		$assets_list = array();
		$assets_smarty_flag = array();
		foreach (new RecursiveIteratorIterator($ite) as $pathname => $path){
			$pagepath = pathinfo($pathname);
			$dirname = strtr(ltrim(substr($pagepath['dirname'],strlen($dir_content)),'\\/'),'\\','/');
			$basename = $pagepath['basename'];
			//ファイル名の1文字目
			$first_char = substr($basename,0,1);
			
			//OSの隠しファイルはスキップ
			switch (strtolower($basename)){
				case 'thumbs.db':
				case '.ds_store':
					continue 2;
			}
			
			if ($dirname){
				$rpath = $dirname.'/'.$pagepath['filename'];
				$_path = $dirname.'/'.$pagepath['filename'].'.html';
				$_folder = $dirname;
				$content_tpl = $dirname.'/'.$basename;
			}else{
				$rpath = $pagepath['filename'];
				$_path = $pagepath['filename'].'.html';
				$_folder = '';
				$content_tpl = $basename;
			}
			
			//smartyのアサイン変数をクリア
			$smarty->clearAllAssign();
			
			$smarty->assign('_home',$home);
			$smarty->assign('_path',$_path);
			$smarty->assign('_folder',$_folder);
			$smarty->assign('_content_tpl',$content_tpl);
			
			//最後が .tpl のテンプレートファイルなら
			if (isset($pagepath['extension'] ) and $pagepath['extension'] == 'tpl'){
				if ($first_char === '_'){
					continue;
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
				$pages_section[] = $page_tmp.$pagepath['filename'].'.html';
				
				$page_vars = $core_vars_yaml;
				foreach ($pages_section as $psect){
					if (isset($vars_yaml['path'][$psect]) and is_array($vars_yaml['path'][$psect])){
						$page_vars = array_merge_recursive_distinct($page_vars,$vars_yaml['path'][$psect]);
					}
				}
				
				$smarty->assign($page_vars);
				
				$filepath = ltrim($dirname.'/'.$pagepath['filename'].'.html','/');
				
				try {
					$output_html = $smarty->fetch($config_yaml['basetpl']);
					
					if (!empty($config_yaml['encode'])){
						$output_html = mb_convert_encoding($output_html, $config_yaml['encode']);
					}
					File::buildPutFile($dir_output.'/'.$filepath,$output_html);
					$bmsg->add('create','<a href="'.$home_local.'/'.$filepath.'" target="_blank">'.$filepath.'</a>');
				} catch (SmartyCompilerException $e){
					$bmsg->add('smartyerror','<strong>'.$filepath.'</strong>: '.$e->getMessage());
					continue;
				}
			}else{
				//最後が .tpl 以外のアセットファイル
				$filepath = $dirname.'/'.$basename;
				
				//Smartyの事前処理が必要なファイルを処理
				if (strpos($basename,'.tpl') !== false){
					try {
						$source = $smarty->fetch($filepath);
					} catch (SmartyCompilerException $e){
						$bmsg->add('smartyerror','<strong>'.$filepath.'</strong>: '.$e->getMessage());
						continue;
					}
					$basename = str_replace('.tpl','',$basename);
					$pathname = $pagepath['dirname'].'/'.$basename;
					$filepath = $dirname.'/'.$basename;
					
					//拡張子 .tpl を抜いたファイル名で出力する
					file_put_contents($pathname, $source);
					
					$assets_smarty_flag[$pathname] = true;
				}
				
				if ($first_char !== '_'){
					$assets_list[] = array(
						'pathname' => $pathname,
						'dirname' => $dirname,
						'filepath' => $filepath,
						'basename' => $basename,
						'first_char' => $first_char,
						);
				}
			}
		}
		
		foreach ($assets_list as $path_dat){
			$create_option = '';
			$pathname = $path_dat['pathname'];
			$dirname = $path_dat['dirname'];
			$filepath = $path_dat['filepath'];
			$basename = $path_dat['basename'];
			$first_char = $path_dat['first_char'];
			
			$is_output = true; //ファイル出力が必要か
			$is_less = false; //Lessファイルか
			$is_scss = false; //Scssファイルか
			$is_coffee = false; //CoffeeScriptか
			$is_js = false; //JavaScriptファイルか
			$is_css = false; //CSSファイルか
			$is_nolint = false; //Lintエラーを無視するか
			
			switch ($first_char){
				//@ ならLintしない
				case '@':
					$is_nolint = true;
					$filepath = $dirname.'/'.substr($basename,1);
					break;
			}
			
			if (strpos($filepath,'.less') !== false){
				$is_less = true;
				$is_css = true;
			}
			if (strpos($filepath,'.scss') !== false){
				$is_scss = true;
				$is_css = true;
			}
			if (strpos($filepath,'.coffee') !== false){
				$is_coffee = true;
				$is_nolint = true;
				$is_js = true;
			}
			if (strpos($filepath,'.js') !== false){
				$is_js = true;
			}
			if (strpos($filepath,'.css') !== false){
				$is_css = true;
			}
			
			if ($is_js or $is_css){
				if (file_exists($pathname)){
					$source = file_get_contents($pathname);
				}else{
					continue;
				}
				
				if (isset($assets_smarty_flag[$pathname])){
					$create_option .= ' (smarty)';
				}
				
				//less
				if ($is_less){
					try {
						$less->setImportDir(dirname($pathname));
						$source = $less->compile($source);
						$create_option .= ' (less)';
						$filepath = str_replace('.less','.css',$filepath);
					} catch (Exception $e){
						$bmsg->add('lesserror','<strong>'.$filepath.'</strong>: '.$e->getMessage());
						continue;
					}
				}
				
				//scss
				if ($is_scss){
					try {
						$scss->setImportPaths(dirname($pathname));
						$source = $scss->compile($source);
						$create_option .= ' (scss)';
						$filepath = str_replace('.scss','.css',$filepath);
					} catch (Exception $e){
						$bmsg->add('scsserror','<strong>'.$filepath.'</strong>: '.$e->getMessage());
						continue;
					}
				}
				
				//coffee
				if ($is_coffee){
					try {
						$source = \CoffeeScript\Compiler::compile($source,array('filename' => $filepath));
						$create_option .= ' (coffee)';
						$filepath = str_replace('.coffee','.js',$filepath);
					}catch (Exception $e){
						$bmsg->add('coffeeerror',$e->getMessage());
						continue;
					}
				}
				
				//js
				if ($is_js){
					//本番環境かつcompilejs=1なら圧縮
					if ($buildtype == 'production' and !empty($config_yaml['compilejs'])){
						$output_to = $dir_output.'/'.$filepath;
						$source_tmp = $dir_output.'/'.$filepath.'.tmp';
						File::buildPutFile($source_tmp,$source);
						compile($source_tmp,$output_to);
						unlink($source_tmp);
						
						$org_filesize = filesize($pathname);
						if (!file_exists($output_to) or !($org_filesize and filesize($output_to))){
							$bmsg->add('jscompileerror',"Couldn't compile: <strong>".$filepath.'</strong>');
							continue;
						}
						$is_output = false;
						$create_option .= ' (minified)';
					}
				}
				if ($is_css){
					if ($buildtype == 'production' and !empty($config_yaml['compilecss'])){
						$source = CssMin::minify($source);
						$create_option .= ' (minified)';
					}
				}
				
				if ($is_output){
					File::buildPutFile($dir_output.'/'.$filepath,$source);
					
					if ($is_js and !$is_nolint){
						//lint check
						$lint_error = jslint($pathname);
						
						if ($lint_error){
							foreach ($lint_error as $lerr){
								$bmsg->add('jslint',$basename.':'.$lerr);
							}
						}
					}
				}
			}else{
				$outputpath = $dir_output.'/'.$filepath;
				$tmp_dir = dirname($outputpath);
				if (!is_dir($tmp_dir)){
					File::buildMakeDir($tmp_dir);
				}
				copy($pathname,$outputpath);
			}
			
			if ($create_option){
				$create_option = ' <code>'.trim($create_option).'</code>';
			}
			$bmsg->add('create','<a href="'.$home_local.'/'.$filepath.'" target="_blank">'.$filepath.'</a>'.$create_option);
		}
		
		//Smarty処理して生成したファイルを削除
		foreach ($assets_smarty_flag as $pathname => $dummy){
			unlink($pathname);
		}
		
		
		//サイトマップ生成
		if (!empty($config_yaml['sitemap'])){
			$smarty->assign('_urls',$urls);
			$filepath = '/sitemap.xml';
			file_put_contents($dir_output.$filepath,$smarty->fetch('smartbuilder_internal/sitemap_xml.tpl'));
			$bmsg->add('create','<a href="'.$home.$filepath.'" target="_blank">'.$filepath.'</a>');
		}
		
		//robots.txt
		if (!empty($config_yaml['robotstxt'])){
			$filepath = '/robots.txt';
			file_put_contents($dir_output.$filepath,$smarty->fetch('smartbuilder_internal/robots_txt.tpl'));
			$bmsg->add('create','<a href="'.$home.$filepath.'" target="_blank">'.$filepath.'</a>');
		}
		
		
		//ビルド時間を記録
		File::buildPutFile($path_buildstatus_site,$pathhash);
	}
	
	if ($watch){
		header('HTTP/1.1 200 OK');
		header('Content-type:application/json;charset=UTF-8');
		echo json_encode(array('code' => 200,'message_list' => $bmsg->getData()));
		exit;
	}else{
		$bsmarty->assign('ver',$ver);
		$bsmarty->assign('message_list',$bmsg->getData());
		$bsmarty->display('smartbuilder_internal/build.tpl');
	}