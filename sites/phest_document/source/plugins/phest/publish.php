<?php
    namespace ChatWork\Phest;
    use ChatWork\Utility\File;

function plugin_button_publish(array $params, Phest $phest){
	$phest->registerSection('publish','Publish');
	$phest->registerSection('publisherror','Publishエラー',array('type' => 'danger'));
	
	$dir_docs = DIR_PHEST.'/../docs';
	
	if (!is_dir($dir_docs)){
		$phest->add('publisherror','docs ディレクトリが見つかりません');
		return false;
	}
	
	File::removeDir($dir_docs);
	mkdir($dir_docs,0777);
	$phest->add('publish','docs/フォルダをクリアしました');
	
	$dir_production = $phest->getOutputPath('','production');
	
	if (!is_dir($dir_production)){
		$phest->add('publisherror','output/production ディレクトリが見つかりません');
		return false;
	}
	File::copyDir($dir_production,$dir_docs);
	$phest->add('publish','docs/フォルダへoutput/productionの内容をコピーしました');
	
	$path_readme = $dir_docs.'/README.md';
	
	if (!file_exists($path_readme)){
		$phest->add('publisherror','README.mdが見つかりません');
		echo $path_readme;
		return false;
	}
	rename($dir_docs.'/README.md',DIR_PHEST.'/../README.md');
	$phest->add('publish','docs/README.mdをトップへ移動しました');
}