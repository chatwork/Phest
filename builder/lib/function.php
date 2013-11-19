<?php
	namespace ChatWork\SmartBuilder;
	
	require(DIR_BUILDER.'/lib/vendor/CoffeeScript/Init.php');
	\CoffeeScript\Init::load();
	

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
	
	$compile_command = 'java -jar "'.DIR_BUILDER.'/lib/vendor/closurecompiler/compiler.jar" --compilation_level SIMPLE_OPTIMIZATIONS --js "'.$source_from.'" --js_output_file "'.$output_to.'"';
	
	$compile_output = array();
	if ($os == 'mac'){
		$compile_command = 'export DYLD_LIBRARY_PATH="";'.$compile_command;
	}
	exec($compile_command,$compile_output);
	
	if (file_exists($output_to)){
		chmod($output_to,0777);
	}
}

/**
 * JavaScriptの構文チェック
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
	$cmd = './lib/vendor/jsl/'.$os.'/jsl -conf ./lib/vendor/jsl/ec.conf -process "'.$jspath.'" 2>&1';
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