<?php
	namespace ChatWork\Phest;
	
class LanguageBuilder {
	protected $bmsg = null;
	protected $lang_list = array();
	protected $lang_dat = array();
	
	public function __construct(BuildMessage $bmsg,array $lang_list){
		$this->bmsg = $bmsg;
		$this->bmsg->registerSection('lang','Language');
		$this->bmsg->registerSection('langerror','言語ファイルの記述エラー',array('type' => 'danger'));
		
		$this->lang_list = $lang_list;
	}
	
	public function process($yaml_path){
		$lang_dat = spyc_load_file($yaml_path);
		
		//言語内参照を解決する
		$error_list = array();
		$key_map = array();
		$found_key_flag = array();
		foreach ($lang_dat as $key => $ldat){
			foreach ($ldat['lang'] as $lang => $value){
				$match = array();
				if (preg_match_all('/\{\{([^\}]+)\}\}/',$value,$match,PREG_SET_ORDER)){
					$found_key_flag[$key] = true;
					foreach ($match as $idx => $match_dat){
						if (!isset($key_map[$lang][$match_dat[0]])){
							if (!isset($lang_dat[$match_dat[1]]['lang'][$lang])){
								$this->bmsg->add('langerror',$key.'('.$lang.'): 参照しようとした {{'.$match_dat[1].'}} は定義されていません');
								continue;
							}
							
							$key_map_value = $lang_dat[$match_dat[1]]['lang'][$lang];
							
							//参照先で変数が使われているかチェック　※参照先で変数が使われている場合はreplaceし忘れる可能性があるのでNGとする（BaseLangの変数置換を使うようになったらキーチェックができるのでこの仕様は解除してもOK）
							if (preg_match('/%%[^%]+%%/',$key_map_value)){
								$this->bmsg->add('langerror',$key.'('.$lang.'): 参照しようとした {{'.$match_dat[1].'}} は変数(%%xx%%)が使われているため参照できません');
								continue;
							}
							
							$key_map[$lang][$match_dat[0]] = $key_map_value;
						}
					}
				}
				
				//変数が閉じられているかをチェック
				//偶数かどうかを見る
				if (substr_count($value,'%%') % 2 !== 0){
					$this->bmsg->add('langerror',$key.'('.$lang.'): 変数の%%指定が閉じられていない可能性があります');
				}
				
				//ダブルクォートが閉じられていないかチェック
				//偶数かどうかを見る
				if (!isset($ldat['nolint']['quotepair'])){
					if (substr_count($value,'"') % 2 !== 0){
						$this->bmsg->add('langerror',$key.'('.$lang.'): ダブルクォーテーションが閉じられていない可能性があります');
					}
				}
				
				//最初と最後にダブルクォーテーションがついているか
				//スプレッドシートからコピーしてきた時に付加される場合がある
				if (!isset($ldat['nolint']['extraquote'])){
					if (substr($value,0,1) === '"' and substr($value,-1) === '"'){
						$this->bmsg->add('langerror',$key.'('.$lang.'): 先頭と最後にダブルクォーテーションがついています。スプレッドシートからのコピーで意図せず付与された可能性があります。');
					}
				}
			}
		}
		
		$i = 1;
		while (count($found_key_flag)){
			//fecho('loop:'.$i++.'<br />');
			foreach ($found_key_flag as $key => $dummy){
				$replace_comp = true;
				foreach ($lang_dat[$key]['lang'] as $lang => $value){
					if (!isset($key_map[$lang])){
						continue;
					}
					//fecho('replace '.$key.' from '.$value);
					$lang_dat[$key]['lang'][$lang] = strtr($value,$key_map[$lang]);
					//fecho (' to '.$lang_dat[$key]['lang'][$lang].'<br />');
					if (preg_match('/\{\{([^\}]+)\}\}/',$lang_dat[$key]['lang'][$lang])){
						$replace_comp = false;
					}
				}
				if ($replace_comp){
					unset($found_key_flag[$key]);
				}
			}
		}
		unset($key_map);
		
		$empty_key_dat = array();
		$lang_file_dat = array();
		foreach ($lang_dat as $key => $ldat){
			foreach ($this->lang_list as $lang){
				//吐き出し対象ごとにキーをわける
				if (!isset($lang_file_dat[$lang])){
					$lang_file_dat[$lang] = array();
				}
				if (isset($ldat['lang'][$lang])){
					$lang_file_dat[$lang][$key] = $ldat['lang'][$lang];
				}else{
					$empty_key_dat[$lang][] = $key;
					
					if (isset($ldat['lang']['en'])){
						$lang_file_dat[$lang][$key] = $ldat['lang']['en']; //単語がない場合、英語を採用する
					}else{
						$lang_file_dat[$lang][$key] = ''; //英語もない場合、空文字列をセット
					}
				}
			}
		}
		
		foreach ($empty_key_dat as $lang => $keys){
			$this->bmsg->add('langerror','言語 <strong>'.$lang.'</strong> の未定義なキーがあります。('.implode(', ',$keys).')');
		}
		
		$this->lang_dat = $lang_file_dat;
	}
	
	public function getLangDat($lang){
		if (isset($this->lang_dat[$lang])){
			return $this->lang_dat[$lang];
		}else{
			return array();
		}
	}
}