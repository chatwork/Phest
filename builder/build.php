<?php
	use \Michelf\Markdown;
	
	define('DIR_BUILDER',dirname(__FILE__));
	require(DIR_BUILDER.'/config.php');
	
	$ver = 'v0.3.1';
	
	ini_set('display_errors','On');
	require(DIR_BUILDER.'/lib/function.php');
	require(DIR_BUILDER.'/lib/File.php');
	require(DIR_BUILDER.'/lib/BuildMessage.php');
	require(DIR_BUILDER.'/lib/vendor/debuglib.php');
	require(DIR_BUILDER.'/lib/vendor/smarty/Smarty.class.php');	
	require(DIR_BUILDER.'/lib/vendor/spyc.php');

	if (!is_dir(DIR_SITES)){
		die('dir_sites がディレクトリではありません');
	}
	
	$bsmarty = new Smarty;
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
		
		File::copyDir('./blanksite/',DIR_SITES.'/'.$create_site);
		$path_config_yml = DIR_SITES.'/'.$create_site.'/source/config.yml';
		file_put_contents($path_config_yml,strtr(file_get_contents($path_config_yml),array('{{site}}' => $create_site)));
		
		header('Location: ?site='.$create_site);
		exit;
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
		$dir_pages = $dir_source.'/pages';
		$path_config_yml = $dir_source.'/config.yml';
		$path_vars_yml = $dir_source.'/vars.yml';
		$dir_output = $dir_site.'/htdocs';
		
		//yaml load
		if (!file_exists($path_config_yml)){
			die('config.ymlが見つかりません。path='.$path_config_yml);
		}
		if (!file_exists($path_vars_yml)){
			die('vars.ymlが見つかりません。path='.$path_vars_yml);
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
		$home_local = $config_yaml['home']['local'];
		if (!$home){
			die('config.ymlにhomeが正しく設定されていません');
		}
		
		$bmsg->registerSection('create','Created files',array('type' => 'info','sort' => true));
		
		//Smarty
		$smarty = new Smarty;
		$smarty->template_dir = array($dir_source,DIR_BUILDER.'/templates');
		$smarty->compile_dir = DIR_BUILDER.'/templates_c/'.$site;
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
		
		$dir_buildstatus = DIR_BUILDER.'/buildstatus';
		$path_buildstatus_site = $dir_buildstatus.'/'.$site.'.dat';
		
		//ソースフォルダの全ファイルをスキャン。新しいファイルがあるかどうかの判定に使う。
		$path_concat_string = '';
		$buildtime = 0;
		$pathhash = '';
		if (file_exists($path_buildstatus_site)){
			$buildtime = filemtime($path_buildstatus_site);
			$pathhash = file_get_contents($path_buildstatus_site);
		}
		
		if (class_exists('FilesystemIterator',false)){
			$ite = new RecursiveDirectoryIterator($dir_source,FilesystemIterator::SKIP_DOTS);
		}else{
			$ite = new RecursiveDirectoryIterator($dir_source);
		}
		
		$has_new = false;
		foreach (new RecursiveIteratorIterator($ite) as $pathname => $path){
			$filepath = strtr(ltrim(substr($pathname,strlen($dir_source)),'\\/'),'\\','/');
			if (!$has_new and ($buildtime < filemtime($pathname))){
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
			$ite = new RecursiveDirectoryIterator($dir_pages,FilesystemIterator::SKIP_DOTS);
		}else{
			$ite = new RecursiveDirectoryIterator($dir_pages);
		}
		
		$urls = array();
		foreach (new RecursiveIteratorIterator($ite) as $pathname => $path){
			$create_option = '';
			$pagepath = pathinfo($pathname);
			$dirname = strtr(ltrim(substr($pagepath['dirname'],strlen($dir_pages)),'\\/'),'\\','/');
			
			//OSの隠しファイルはスキップ
			switch (strtolower($pagepath['basename'])){
				case 'thumbs.db':
				case '.ds_store':
					continue 2;
			}
			
			if ($dirname){
				$rpath = $dirname.'/'.$pagepath['filename'];
				$_path = $dirname.'/'.$pagepath['filename'].'.html';
				$_folder = $dirname;
				$content_tpl = 'pages/'.$dirname.'/'.$pagepath['basename'];
			}else{
				$rpath = $pagepath['filename'];
				$_path = $pagepath['filename'].'.html';
				$_folder = '';
				$content_tpl = 'pages/'.$pagepath['basename'];
			}
			
			//smartyのアサイン変数をクリア
			$smarty->clearAllAssign();
			
			$smarty->assign('_home',$home);
			$smarty->assign('_path',$_path);
			$smarty->assign('_folder',$_folder);
			$smarty->assign('_content_tpl',$content_tpl);
			
			if (isset($pagepath['extension'] ) and $pagepath['extension'] == 'tpl'){
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
					$output_html = $smarty->fetch('parts/base.tpl');
					
					if (!empty($config_yaml['encode'])){
						$output_html = mb_convert_encoding($output_html, $config_yaml['encode']);
					}
					File::buildPutFile($dir_output.'/'.$filepath,$output_html);
				} catch (SmartyCompilerException $e){
					$bmsg->add('smartyerror','<strong>'.$filepath.'</strong>: '.$e->getMessage());
					continue;
				}
			}else{
				//tplじゃない場合
				
				//ファイル名の1文字目
				$first_char = substr($pagepath['basename'],0,1);
				
				$is_output = true; //ファイル出力が必要か
				$is_tpl = false; //Smarty処理が必要なtplファイルか
				$is_less = false; //Lessファイルか
				$is_scss = false; //Scssファイルか
				$is_coffee = false; //CoffeeScriptか
				$is_js = false; //JavaScriptファイルか
				$is_nolint = false; //Lintエラーを無視するか
				
				switch ($first_char){
					//_ ならスキップ
					case '_':
						continue 2;
					//@ ならLintしない
					case '@':
						$is_nolint = true;
						break;
				}
				
				$filepath = $dirname.'/'.ltrim($pagepath['basename'],'@');
				
				if (strpos($pagepath['basename'],'.tpl') !== false){
					$is_tpl = true;
				}
				if (strpos($pagepath['basename'],'.less') !== false){
					$is_less = true;
				}
				if (strpos($pagepath['basename'],'.scss') !== false){
					$is_scss = true;
				}
				if (strpos($pagepath['basename'],'.coffee') !== false){
					$is_coffee = true;
					$is_nolint = true;
					$is_js = true;
				}
				if (strpos($pagepath['basename'],'.js') !== false){
					$is_js = true;
				}
				
				if ($is_tpl or $is_less or $is_scss or $is_js){
					//smarty
					if ($is_tpl){
						try {
							$source = $smarty->fetch('pages/'.$filepath);
						} catch (SmartyCompilerException $e){
							$bmsg->add('smartyerror','<strong>'.$filepath.'</strong>: '.$e->getMessage());
							continue;
						}
						$create_option = ' (smarty)';
						$filepath = str_replace('.tpl','',$filepath);
					}else{
						$source = file_get_contents($pathname);
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
							// See available options above.
							$source = CoffeeScript\Compiler::compile($source,array('filename' => $filepath));
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
								$bmsg->add('jscompileerror',"Couldn't compile: <strong>".$filepath.'</strong>: ');
								continue;
							}
							$is_output = false;
							$create_option .= ' (minified)';
						}
					}
					
					if ($is_output){
						File::buildPutFile($dir_output.'/'.$filepath,$source);
						
						if ($is_js and !$is_nolint){
							//lint check
							if ($is_tpl){
								//Smartyの場合、出力先に対してlintをかける
								$lint_error = jslint($dir_output.'/'.$filepath);
							}else{
								$lint_error = jslint($pathname);
							}
							
							if ($lint_error){
								foreach ($lint_error as $lerr){
									$bmsg->add('jslint',$pagepath['basename'].':'.$lerr);
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
			}
			
			if ($create_option){
				$create_option = ' <code>'.trim($create_option).'</code>';
			}
			$bmsg->add('create','<a href="'.$home_local.'/'.$filepath.'" target="_blank">'.$filepath.'</a>'.$create_option);
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
		$bsmarty->display('_build.tpl');
	}