<?php
	namespace ChatWork\Phest;

/**
 * Phest - PHP Easy Static Site Generator
 * https://github.com/chatwork/Phest
 *
 * Licensed under MIT, see LICENSE
 * https://github.com/chatwork/Phest/blob/master/LICENSE
 *
 * @link https://github.com/chatwork/Phest
 * @copyright 2013 ChatWork Inc
 * @author Masaki Yamamoto (https://chatwork.com/cw_masaki)
 */
	use \RecursiveDirectoryIterator;
	use \RecursiveIteratorIterator;
	use \FilesystemIterator;

	define('DIR_PHEST',dirname(__FILE__));
	require(DIR_PHEST.'/config.php');

	$ver = 'v0.9.1';

	error_reporting(E_ALL);
	ini_set('display_errors','On');
	date_default_timezone_set('Asia/Tokyo');
	set_time_limit(600);

	require(DIR_PHEST.'/lib/function.php');
	require(DIR_PHEST.'/lib/Phest.php');
	require(DIR_PHEST.'/lib/Compiler.php');
	require(DIR_PHEST.'/lib/LanguageBuilder.php');

	require(DIR_PHEST.'/lib/File.php');
	use \ChatWork\Utility\File;

	require(DIR_PHEST.'/lib/vendor/smarty/Smarty.class.php');
	use \Smarty;
	require(DIR_PHEST.'/lib/vendor/cssmin/cssmin-v3.0.1.php');
	use \CssMin;

	require(DIR_PHEST.'/lib/vendor/debuglib.php');
	require(DIR_PHEST.'/lib/vendor/spyc/spyc.php');

	$phest = Phest::getInstance();
	$current_time = time();

	$site_list = array();
	foreach (glob(DIR_SITES.'/*') as $site_dir){
		if (is_dir($site_dir)){
			$site_list[] = basename($site_dir);
		}
	}

	$site = '';
	if (isset($_GET['site']) and in_array($_GET['site'],$site_list)){
		$site = $_GET['site'];
	}else{
		$site = current($site_list);
	}

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
		header('Content-type:application/json;charset=UTF-8');
		$watch = 1;
	}else{
		header('Content-type:text/html;charset=UTF-8');
	}

	$plugin_idx = false;
	if (isset($_GET['plugin_idx'])){
		$plugin_idx = $_GET['plugin_idx'];
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
		case 'plugin':
			$build_class = 'text-primary';
			$build = false;
			break;
		default:
			$build = false;
			break;
	}

	$phest->setBuildType($buildtype);

	$lang = '';
	$lang_list = array();
	$plugin_list = array();
	$extra_buttons = array();

	if ($site){
		$dir_site = DIR_SITES.'/'.$site;
		$dir_source = $dir_site.'/source';
		$dir_content = $dir_source.'/content';
		$path_config_yml = $dir_source.'/config.yml';
		$path_vars_yml = $dir_source.'/vars.yml';
		$path_languages_yml = $dir_source.'/languages.yml';

		$phest->setSite($site);
		$phest->addPluginsDir($dir_source.'/plugins/phest');

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
		$config_yaml = array_merge(spyc_load_file(DIR_PHEST.'/default_config.yml'),spyc_load_file($path_config_yml));


		if (isset($config_yaml['plugins'])){
			foreach ($config_yaml['plugins'] as $idx => $pdat){
				if (is_array($pdat)){
					$plugin_name = key($pdat);
					$plugin_params = current($pdat);
				}else{
					$plugin_name = $pdat;
					$plugin_params = array();
				}

				$plugin_list[$idx] = array(
					'name' => $plugin_name,
					'params' => $plugin_params,
					);

				if (isset($plugin_params['_button'])){
					$extra_buttons[$idx] = $plugin_params['_button'];
				}
			}
		}

		$lang_list = $config_yaml['languages'];

		if ($lang_list){
			if (isset($_GET['lang']) and in_array($_GET['lang'],$lang_list)){
				$lang = $_GET['lang'];
			}else{
				$lang = $lang_list[0];
			}
		}

		$phest->setLang($lang);

		//コンパイラの設定
		//コマンドラインのインストール状況を確認
		$compiler_type = $phest->getCompilerType();

		foreach ($compiler_type as $ext => $cmp_dat){
			if (empty($config_yaml['compile'.$cmp_dat['type']])){
				$phest->setCompiler($ext,false);
			}else{
				if ($config_yaml['use'.$cmp_dat['nativetype']]){
					$compiler_path = $config_yaml['path'.$cmp_dat['nativetype']];
					if ($compiler_path == 'auto'){
						$compiler_path = $phest->detectCommand($cmp_dat['nativecommand']);
					}else if (!file_exists($compiler_path)){
						$compiler_path = '';
					}
					if ($compiler_path){
						$phest->setCompiler($ext,$cmp_dat['nativetype'],$compiler_path);
					}
				}
			}
		}
	}

	//build実行
	if ($build and $site){
		define('PHEST_BUILTTYPE',$buildtype);

		$build_submessage = '';
		if ($lang){
			$build_submessage = $lang.'/';
		}
		$phest->registerSection('build','ビルド完了 [ <strong class="'.$build_class.'">'.$build_submessage.$buildtype.'</strong> ] - '.date('H:i:s'));
		$phest->registerSection('builderror','ビルドエラー',array('type' => 'danger'));

		$dir_output = $phest->getOutputPath();

		File::buildMakeDir($dir_output);
		$default_vars_yaml = spyc_load_file(DIR_PHEST.'/default_vars.yml');
		$source_vars_yaml = spyc_load_file($path_vars_yml);
		$vars_yaml = array_merge_recursive_distinct($default_vars_yaml,$source_vars_yaml);

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
		$home_local = '../sites/'.$site.$phest->getBuildDirName();

		$phest->registerSection('create','作成したファイル',array('type' => 'info','sort' => true));

		//Smarty
		$smarty = new Smarty;
		$smarty->template_dir = array($dir_content,DIR_PHEST.'/templates');
		$smarty->compile_dir = DIR_PHEST.'/cache/templates_c/'.$site;
		$smarty->addPluginsDir(array(DIR_PHEST.'/plugins/smarty',$dir_source.'/plugins/smarty'));
		if ($config_yaml['smartypluginsdir']){
			foreach ($config_yaml['smartypluginsdir'] as $pdir){
				$smarty->addPluginsDir($dir_source.'/'.$pdir);
			}
		}
		File::buildMakeDir($smarty->compile_dir);
		$phest->registerSection('smartyerror','Smarty コンパイルエラー',array('type' => 'danger'));

		//less
		$phest->registerSection('lesserror','LESS 構文エラー',array('type' => 'danger'));

		//scss
		$phest->registerSection('sasserror','SCSS 構文エラー',array('type' => 'danger'));

		//coffee
		$phest->registerSection('coffeescripterror','CoffeeScript 構文エラー',array('type' => 'danger'));

		//jslint
		$phest->registerSection('jslint','JavaScript 文法エラー',array('type' => 'danger'));
		$phest->registerSection('jscompileerror','JavaScript コンパイルエラー',array('type' => 'danger'));

		$include_vars_list = array();
		foreach ($vars_yaml['includes'] as $ipath){
			$ipath = $dir_source.'/'.$ipath;
			if (file_exists($ipath)){
				$phest->addWatchList($ipath);
				$include_vars_list[] = $ipath;
			}else{
				$phest->add('builderror','vars.yml インクルードしようとしたファイルは存在しません: '.$ipath);
			}
		}

		$phest->addWatchList(File::getFileList($dir_source));

		//phest pluginを処理
		$loaded_plugins = array();
		foreach ($plugin_list as $idx => $pdat){
			$plugin_name = $pdat['name'];
			$plugin_params = $pdat['params'];
			if ($phest->loadPlugin($plugin_name)){
				$loaded_plugins[$plugin_name] = true;
				$watch_func_name = 'ChatWork\Phest\plugin_watch_'.$plugin_name;
				if (function_exists($watch_func_name)){
					$loaded_plugins[$plugin_name] = $watch_func_name($plugin_params,$phest);
				}
			}
		}

		if ($watch and !$phest->hasNew()){
			exit;
		}

		foreach ($plugin_list as $idx => $pdat){
			$plugin_name = $pdat['name'];
			$plugin_params = $pdat['params'];
			if (!empty($loaded_plugins[$plugin_name])){
				$build_func_name = 'ChatWork\Phest\plugin_build_'.$plugin_name;
				if (function_exists($build_func_name)){
					$build_func_name($plugin_params,$phest);
				}
			}
		}

		//vars.ymlのincludesを処理
		foreach ($include_vars_list as $ipath){
			$inc_yaml = spyc_load_file($ipath);
			$core_vars_yaml = array_merge_recursive_distinct($core_vars_yaml,$inc_yaml);
		}

		//------------- build処理
		$lang_list = $config_yaml['languages'];
		if ($lang_list){
			if (!file_exists($path_languages_yml)){
				File::buildTouch($path_languages_yml);
			}
			$LG = new LanguageBuilder($phest,$lang_list);
			$LG->process($path_languages_yml);
		}

		$build_option = '';
		if (!empty($config_yaml['buildclear'])){
			File::removeDir($dir_output);
			mkdir($dir_output,0777);
		}
		if ($build_option){
			$build_option = ' <code>'.trim($build_option).'</code>';
		}
		$phest->add('build','ビルド元: '.realpath($dir_source));
		$phest->add('build','ビルド先: <a href="'.$home_local.'" target="_blank">'.realpath($dir_output).'</a>'.$build_option);

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

			$smarty->assign('_phestver','Phest '.$ver);
			$smarty->assign('_vars',$vars_yaml);
			$smarty->assign('_config',$config_yaml);
			$smarty->assign('_time',$current_time);
			$smarty->assign('_home',$home);
			$smarty->assign('_path',$_path);
			$smarty->assign('_folder',$_folder);
			if ($_folder){
				$_top = rtrim(str_repeat('../',substr_count($_folder, '/') + 1),'/');
			}else{
				$_top = '.';
			}
			$smarty->assign('_top',$_top);
			$smarty->assign('_content_tpl',$content_tpl);

			$smarty->assign($core_vars_yaml);

			if ($lang){
				$smarty->assign('_lang',$lang);
				$smarty->assign('L',$LG->getLangDat($lang));
			}

			//vars.ymlで読み出すセクションのリストを生成
			$pages_section = array();
			$page_tmp = '';
			foreach (explode('/',$dirname) as $page) {
				if ($page){
					$pages_section[] = $page_tmp.$page.'/';
					$page_tmp .= $page.'/';
				}
			}

			//拡張子の変換 (最終的に出力されるファイル名へ)
			$pages_section[] = $page_tmp.strtr($pagepath['basename'],array(
				'.tpl.' => '.',
				'.tpl' => '.html',
				'.less' => '.css',
				'.scss' => '.css',
				'.coffee' => '.js',
				));

			$page_vars = array();
			foreach ($pages_section as $psect){
				if (isset($vars_yaml['path'][$psect]) and is_array($vars_yaml['path'][$psect])){
					$page_vars = array_merge_recursive_distinct($page_vars,$vars_yaml['path'][$psect]);
				}
			}

			$smarty->assign($page_vars);

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

				if (!check_path_match($_path,$config_yaml['ignoresitemaps'])){
					$urls[] = array('path' => $path_current,'lastmod' => date('c',filemtime($pathname)),'changefreq' => $changefreq,'priority' => $priority);
				}

				$filepath = ltrim($dirname.'/'.$pagepath['filename'].'.html','/');

				try {
					$output_html = $smarty->fetch($config_yaml['basetpl']);

					if (!empty($config_yaml['encode'])){
						$output_html = mb_convert_encoding($output_html, $config_yaml['encode']);
					}
					File::buildPutFile($dir_output.'/'.$filepath,$output_html);
					$phest->add('create','<a href="'.$home_local.'/'.$filepath.'" target="_blank">'.$filepath.'</a>');
				} catch (\Exception $e){
					$phest->add('smartyerror','<strong>'.$filepath.'</strong>: '.$e->getMessage());
					continue;
				}
			}else{
				//最後が .tpl 以外のアセットファイル
				$filepath = ltrim($dirname.'/'.$basename,'/');

				//Smartyの事前処理が必要なファイルを処理
				if (strpos($basename,'.tpl') !== false){
					try {
						$source = $smarty->fetch($filepath);
					} catch (\Exception $e){
						$phest->add('smartyerror','<strong>'.$filepath.'</strong>: '.$e->getMessage());
						continue;
					}
					$basename = str_replace('.tpl','',$basename);
					$pathname = $pagepath['dirname'].'/'.$basename;
					$filepath = ltrim($dirname.'/'.$basename,'/');

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
			$is_js = false; //JavaScriptファイルか
			$is_css = false; //CSSファイルか
			$is_nolint = false; //Lintエラーを無視するか

			$compile_list = array();

			switch ($first_char){
				//@ ならLintしない
				case '@':
					$is_nolint = true;
					$filepath = $dirname.'/'.substr($basename,1);
					break;
			}

			//拡張子をバラして、対応するコンパイラを特定
			$filepart = explode('.',$filepath);
			$filebase = '';
			$extensions = array();
			for ($i = 0;$i < count($filepart);$i++){
				if ($i == 0){
					$filebase = $filepart[0];
				}else{
					$extensions[$filepart[$i]] = true;
				}
			}

			if (isset($extensions['less'])){
				$ctype = $phest->getCompilerType('less');
				if ($ctype !== false){
					$compile_list[] = $ctype;
				}
				$is_css = true;
			}
			if (isset($extensions['scss'])){
				$ctype = $phest->getCompilerType('scss');
				if ($ctype !== false){
					$compile_list[] = $ctype;
				}
				$is_css = true;
			}
			if (isset($extensions['coffee'])){
				$ctype = $phest->getCompilerType('coffee');
				if ($ctype !== false){
					$compile_list[] = $ctype;
				}
				$is_nolint = true;
				$is_js = true;
			}
			if (isset($extensions['js'])){
				$is_js = true;
			}
			if (isset($extensions['css'])){
				$is_css = true;
			}

			if (isset($assets_smarty_flag[$pathname])){
				$create_option .= ' (smarty)';
			}

			if ($is_js or $is_css){
				if (file_exists($pathname)){
					$source = file_get_contents($pathname);
				}else{
					continue;
				}

				foreach ($compile_list as $compile_type){
					try {
						$compiler = Compiler::factory($compile_type);
						$source = $compiler->compile($source,$pathname);
						$create_option .= ' ('.$compiler->getOptionLabel().')';
						$filepath = $compiler->convertFileName($filepath);
					} catch (\Exception $e){
						$phest->add($compiler->getSectionKey().'error','<strong>'.$filepath.'</strong>: '.$e->getMessage());
						continue;
					}
				}

				//js
				if ($is_js){
					//本番環境かつcompilejs=1なら圧縮
					if ($buildtype == 'production' and !empty($config_yaml['compilejs'])){

						//ignorecomilejsオプションで、コンパイルしないjsを検証
						if (!check_path_match($filepath,$config_yaml['ignorecompilejs'])){
							//何か出力しないとブラウザ側でタイムアウトするので空白を出力
							echo '<span></span>';flush();ob_flush();

							//コマンドラインで処理するために、一度テンポラリファイルとして書き出す
							$output_to = $dir_output.'/'.$filepath;
							$source_tmp = $dir_output.'/'.$filepath.'.tmp';
							File::buildPutFile($source_tmp,$source);

							//コンパイル
							compile($source_tmp,$output_to);
							//完了したらテンポラリファイルを削除
							unlink($source_tmp);

							$org_filesize = filesize($pathname);
							if (!file_exists($output_to) or !($org_filesize and filesize($output_to))){
								$phest->add('jscompileerror',"Couldn't compile: <strong>".$filepath.'</strong>');
								continue;
							}
							$is_output = false;
							$create_option .= ' (minified)';
						}else{
							$create_option .= ' (ignore minify)';
						}
					}
				}

				//css
				if ($is_css){
					//本番環境かつcompilecss=1なら圧縮
					if ($buildtype == 'production' and !empty($config_yaml['compilecss'])){
						if (!check_path_match($filepath,$config_yaml['ignorecompilecss'])){
							$source = CssMin::minify($source);
							$create_option .= ' (minified)';
						}else{
							$create_option .= ' (ignore minify)';
						}
					}
				}

				//ファイルとして出力
				if ($is_output){
					File::buildPutFile($dir_output.'/'.$filepath,$source);

					if ($is_js and !$is_nolint){
						//lint check
						$lint_error = jslint($pathname);

						if ($lint_error){
							foreach ($lint_error as $lerr){
								$phest->add('jslint',$basename.':'.$lerr);
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
			$phest->add('create','<a href="'.$home_local.'/'.$filepath.'" target="_blank">'.$filepath.'</a>'.$create_option);
		}

		//Smarty処理して生成したファイルを削除
		foreach ($assets_smarty_flag as $pathname => $dummy){
			unlink($pathname);
		}

		//サイトマップ生成
		if (!empty($config_yaml['sitemap'])){
			$smarty->assign('_urls',$urls);
			$filepath = '/sitemap.xml';
			file_put_contents($dir_output.$filepath,$smarty->fetch('phest_internal/sitemap_xml.tpl'));
			$phest->add('create','<a href="'.$home.$filepath.'" target="_blank">'.$filepath.'</a>');
		}

		//robots.txt
		if (!empty($config_yaml['robotstxt'])){
			$filepath = '/robots.txt';
			file_put_contents($dir_output.$filepath,$smarty->fetch('phest_internal/robots_txt.tpl'));
			$phest->add('create','<a href="'.$home.$filepath.'" target="_blank">'.$filepath.'</a>');
		}

		//フィニッシュプラグインの実行
		foreach ($plugin_list as $idx => $pdat){
			$plugin_name = $pdat['name'];
			$plugin_params = $pdat['params'];
			if (!empty($loaded_plugins[$plugin_name])){
				$build_func_name = 'ChatWork\Phest\plugin_finish_'.$plugin_name;
				if (function_exists($build_func_name)){
					$build_func_name($plugin_params,$phest);
				}
			}
		}
	}

	//ボタンプラグインの実行
	if ($plugin_idx !== false){
		$pdat = $plugin_list[$plugin_idx];
		$plugin_name = $pdat['name'];
		$plugin_params = $pdat['params'];
		if ($phest->loadPlugin($plugin_name)){
			$build_func_name = 'ChatWork\Phest\plugin_button_'.$plugin_name;
			if (function_exists($build_func_name)){
				$build_func_name($plugin_params,$phest);
			}
		}
	}

	if ($watch){
		echo json_encode(array('code' => 200,'message_list' => $phest->getMessageData()));
		exit;
	}else{
		$bsmarty = new Smarty;
		$bsmarty->compile_dir = DIR_PHEST.'/cache/templates_c';

		$bsmarty->assign('ver',$ver);
		$bsmarty->assign('message_list',$phest->getMessageData());
		$bsmarty->assign('extra_buttons',$extra_buttons);
		$bsmarty->assign('site',$site);
		$bsmarty->assign('site_list',$site_list);
		$bsmarty->assign('lang',$lang);
		$bsmarty->assign('lang_list',$lang_list);
		$bsmarty->display('phest_internal/build.tpl');
	}

function check_path_match($filepath,array $path_list){
	foreach ($path_list as $igval){
		if (is_array($igval) and key($igval) == 'regex'){
			if (preg_match('@'.current($igval).'@',$filepath)){
				return true;
			}
		}else if ($filepath == $igval){
			return true;
		}
	}

	return false;
}