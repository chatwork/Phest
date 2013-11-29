<?php
	namespace ChatWork\Utility;
	use \RecursiveDirectoryIterator;
	use \FilesystemIterator;
####################################################################
# File Utility
# copyright (c): 2005-2010,ChatWork Inc all rights reserved
####################################################################

//FilesystemIteratorクラスがあるか。このクラスがあるかで挙動が変わる。
define('ECF_FILE_FILESYSTEMITERATOR_EXIST',class_exists('FilesystemIterator',false));

/**
 * ファイルシステムを操作する関数
 *
 * ディレクトリを再帰的に削除する、ディレクトリごとコピーなど
 * 高度なAPIを提供する
 *
 * @version 0.18
 *
 * 修正:山本 v0.18 removeDirで環境により親ディレクトリのファイルまで削除されてしまう問題があったのを修正 2013/02/26 20:55
 * 修正:山本 v0.17 getFileList、removeDirで、ファイルに.からはじまるものがあった場合に対象にならないバグを修正 2010/03/25 21:04:54
 * 改良:山本 v0.16 getFileListにフィルタ機能を追加。特定のファイルのみをピックアップできるように 2010/03/12 19:16:19
 * 改良:山本 v0.15 removeDir,getFileListの内部ロジックを書き直し。特定のケースでうまく動作しない問題を解決　2010/03/12 19:13:34
 * 追加:山本 v0.14 PHP5専用へ。staticキーワードの追加、コーディング規約の統一。2010/03/12 19:12:21
 * 修正:山本 v0.13 buldMakeDirで、連続でメソッド実行された時にmkdirがwarningが出る可能性がある問題を修正 11:51:10 2007/11/06
 * 修正:山本 v0.12 buildMakeDirで、0という名前のフォルダが作れなかったバグを修正 21:59:13 2007/11/05
 * 追加:山本 v0.11 buildTouchを追加 touchのbuild版 16:50:02 2007/09/12
 */
class File {
	/**
	 * ディレクトリごと再帰的に削除
	 *
	 * 中にファイルが入っていても削除する。
	 *
	 * @param string $dir ディレクトリ名
	 * @param bool $remove_top トップのディレクトリを削除するか
	 * @param string $hook_func 削除ごとに実行する関数を指定。
	 */
	public static function removeDir($dir,$remove_top = true,$hook_func= ''){
		if (is_file($dir)){
			unlink($dir);
			if ($hook_func){
				call_user_func($hook_func,'delete_file',$dir);
			}
		}else if (is_dir($dir)){
			if (ECF_FILE_FILESYSTEMITERATOR_EXIST){
				$iterator = new RecursiveDirectoryiterator($dir,FilesystemIterator::SKIP_DOTS);
			}else{
				$iterator = new RecursiveDirectoryiterator($dir);
			}
			foreach($iterator as $path){
				if ($path->isDir()){
					File::removeDir($path->getPathname());
				}else{
					unlink($path->getPathname());
				}
			}
			if ($remove_top){
				rmdir($dir);
				if ($hook_func){
					call_user_func($hook_func,'delete_dir',$dir);
				}
			}
		}

		if (file_exists($dir)){
			return false;
		}else{
			return true;
		}
	}

	/**
	 * ディレクトリが存在しない場合、作成を試みてファイルポインタを返す。
	 *
	 * <code>
	 * #ディレクトリを全て作成し、test.datのファイルポインタを返す。
	 * $fp = File::buildFileOpen('cache/te/ki/tou/na/folder/test.dat');
	 * </code>
	 *
	 * @param string $filepath ファイルのパス
	 * @param string $mode fopenのmode
	 * @param string $hook_func ファイル、ディレクトリ作成ごとに実行する関数を指定。
	 * @return resouce ファイルポインタ
	 */
	public static function buildFileOpen($filepath,$mode = 'w',$hook_func = ''){
		File::buildMakeDir(dirname($filepath),$hook_func);

		if (!is_dir($filepath)){
			return fopen($filepath,$mode);
		}

		return false;
	}

	/**
	 * ディレクトリが存在しない場合、作成を試みてファイル作成を行う
	 *
	 * <code>
	 * #ディレクトリを全て作成し、test.datを作成。
	 * File::buildPutFile('cache/te/ki/tou/na/folder/test.dat','コンテンツ');
	 * </code>
	 *
	 * @param string $filepath ファイルのパス
	 * @param string $content 書き込む内容
	 * @param string $hook_func ファイル、ディレクトリ作成ごとに実行する関数を指定。
	 * @return resouce ファイルポインタ
	 */
	public static function buildPutFile($filepath,$content,$hook_func = ''){
		File::buildMakeDir(dirname($filepath),$hook_func);

		if ($fp = File::buildFileOpen($filepath)){
			flock($fp,LOCK_EX);
			ftruncate($fp,0);
			fputs($fp,$content);
			flock($fp,LOCK_UN);
			fclose($fp);
			if ($hook_func){
				call_user_func($hook_func,'make_file',$filepath);
			}

			return true;
		}

		return false;
	}


	/**
	 * ディレクトリが存在しない場合、作成を試みて空ファイルの作成を行う
	 *
	 * <code>
	 * #ディレクトリを全て作成し、test.datを作成。
	 * File::buildTouch('cache/te/ki/tou/na/folder/test.dat');
	 * </code>
	 *
	 * @param string $filepath ファイルのパス
	 * @param string $hook_func ファイル、ディレクトリ作成ごとに実行する関数を指定。
	 * @return resouce ファイルポインタ
	 */
	public static function buildTouch($filepath,$hook_func = ''){
		File::buildMakeDir(dirname($filepath),$hook_func);

		if (!is_dir($filepath)){
			if (touch($filepath)){
				if ($hook_func){
					call_user_func($hook_func,'make_file',$filepath);
				}
				return true;
			}
		}

		return false;
	}

	/**
	 * 与えられたパスのディレクトリを全て作成する
	 *
	 * <code>
	 * File::buildMakeDir('cache/te/ki/tou/na/folder/');
	 * </code>
	 *
	 * @param string $filepath パス
	 * @param string $hook_func ディレクトリ作成ごとに実行する関数を指定。
	 */
	public static function buildMakeDir($filepath,$hook_func = ''){
		if (strpos($filepath,'\\') !== false){
			$filepath = str_replace('\\','/',$filepath);
		}
		$filepath = rtrim($filepath,'\\/').'/';
		if (!is_dir($filepath)){
			//再帰的に呼び出す　一つ上のパスがディレクトリで存在しているか確認
			if ($parent_path = dirname($filepath)){
				File::buildMakeDir($parent_path,$hook_func);
			}
			if (@mkdir($filepath)){
				if ($hook_func){
					call_user_func($hook_func,'make_dir',$filepath);
				}
				chmod($filepath,0755);
			}else{
				return false;
			}
		}
	}

	/**
	 * ディレクトリごとコピー
	 *
	 * サブディレクトリ、含まれるファイルも全てコピーする
	 *
	 * <code>
	 * #./test以下を./backup/testにコピー
	 * File::copyDir('./test','./backup/test');
	 * </code>
	 *
	 * @param string $source コピー元 (ディレクトリ名)
	 * @param string $dest コピー先 (ディレクトリ名)
	 * @param bool $overwrite 上書きするか
	 * @param mixed $ignore 無視するファイル名のリスト (.svnを指定するとSubVersionを無視)
	 * @param mixed $hook_func ファイル、ディレクトリを作成した時にhookする関数名 (call_user_func 形式の引数)
	 */
	public static function copyDir($source,$dest,$overwrite = false,$ignore = array(),$hook_func = ''){
		$source = rtrim($source,'\\/');
		$dest = rtrim($dest,'\\/');

		if (!is_array($ignore)){
			$ignore = array($ignore);
		}

		if (!is_dir($dest)){
			File::buildMakeDir($dest,$hook_func);
		}
		chmod($dest,0755);
		if ($handle = opendir($source)){
			while (false !== ($file = readdir($handle))){
				if ($file != '.' && $file != '..' && !in_array($file,$ignore)){
					$path = $source.'/'.$file;
					if (is_file($path)){
						if (!($ovr = is_file($dest.'/'.$file)) || $overwrite){
							if (!@copy($path,$dest.'/'.$file)){
								ECF::error($path.'はコピーできません。パーミッションが適切に設定されていない可能性があります');
							}
						}
						if ($hook_func){
							call_user_func($hook_func,'make_file',$dest.'/'.$file,$ovr);
						}
					} elseif (is_dir($path)){
						if (!is_dir($dest.'/'.$file)){
							File::buildMakeDir($dest.'/'.$file,$hook_func); //サブディレクトリを作成
						}
						chmod($dest.'/'.$file,0755);
						File::copyDir($path,$dest.'/'.$file,$overwrite,$ignore,$hook_func); //再帰呼び出し
					}
				}
			}
			closedir($handle);
		}
	}


	/**
	 * 指定したディレクトリから、ファイルのパスの配列を返す
	 *
	 * <pre>
	 * ディレクトリのパスは返さず、ファイルパスのみ返す。
	 * ./test/
	 *        index.php
	 *        counter.php
	 *        data/
	 *             count.dat
	 *        img/
	 *
	 * という構成があった場合、
	 * </pre>
	 * <code>
	 * $file_list = File::getFileList('./test');
	 * </code>
	 * <pre>
	 * は、
	 *
	 * array(
	 *    './test/index.php',
	 *    './test/counter.php',
	 *    './test/data/count.dat'
	 * );
	 * を返す。
	 * </pre>
	 *
	 * @param string $directory ディレクトリ
	 * @param int $depth たどる階層の深さ -1 だと無制限
	 * @param array $ignore 無視するパターン (fnmatch関数の引数)
	 * @param array $filter 検出するパターン (fnmatch関数の引数)
	 * @return array ファイルパスのリスト
	 */
	public static function getFileList($directory,$depth = -1,$ignore = array(),$filter = array()) {
		if ($depth == 0){
			return array();
		}

		$directory = rtrim($directory,'\\/');

		$tmp = array();

		if (ECF_FILE_FILESYSTEMITERATOR_EXIST){
			$iterator = new RecursiveDirectoryiterator($directory,FilesystemIterator::SKIP_DOTS);
		}else{
			$iterator = new RecursiveDirectoryiterator($directory);
		}

		foreach ($iterator as $path){
			$filename = $path->getBasename();
			foreach ((array)$ignore as $ival){
				if (fnmatch($ival,$filename)){
					continue 2;
				}
			}

			if ($path->isDir()){
				if ($depth > 0){
					$depth--;
				}
				foreach (File::getFileList($directory.'/'.$filename,$depth,$ignore,$filter) as $ppath){
					$tmp[] = $ppath;
				}
			}else{
				if ($filter){
					$hit = false;
					foreach ((array)$filter as $fval){
						if (fnmatch($fval,$filename)){
							$hit = true;
							break;
						}
					}
					if (!$hit){
						continue;
					}
				}
				$tmp[] = $directory.'/'.$filename;
			}
		}

		return $tmp;
	}
}

if (!function_exists('fnmatch')) {
	function fnmatch($pattern,$string) {
		return @preg_match(
			'/^'.strtr(addcslashes($pattern,'/\\.+^$(){}=!<>|'),
			array('*' => '.*','?' => '.?')).'$/i',$string);
	}
}