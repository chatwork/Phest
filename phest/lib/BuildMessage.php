<?php
	namespace ChatWork\Phest;

/**
 * 出力メッセージの管理
 */
class BuildMessage {
	protected $message_data = array();
	
	/**
	 * セクションを登録
	 * 
	 * @method registerSection
	 * @param string $section セクション名
	 * @param string $title セクションタイトル
	 * @param array [$options] セクション表示オプション
	 * @param enum(success|primary|info|danger) [$option.type=success] 表示種類
	 * @param bool [$option.sort=false] メッセージをソートするか
	 * @chainable
	 */
	public function registerSection($section,$title,array $options = array('type' => 'success','sort' => false)){
		$this->message_data[$section] = array_merge(array('title' => $title,'list' => array()),$options);
		$this->section_order_list[] = $section;
		
		return $this;
	}
	
	/**
	 * セクションにメッセージを追加
	 * @method add
	 * @param string $section セクション名 (registerSectionで登録した値)
	 * @param string $message メッセージ内容
	 * @chainable
	 */
	public function add($section,$message){
		if (!isset($this->message_data[$section])){
			trigger_error('BuildMessage: section='.$section.' は定義されていません');
			return $this;
		}
		$this->message_data[$section]['list'][] = $message;
		
		return $this;
	}
	
	/**
	 * メッセージデータを取得
	 * 
	 * @method getData
	 * @return array メッセージデータの配列
	 */
	public function getData(){
		$msg_data = array();
		
		$type_list = array('success','danger','primary','info');
		
		foreach ($type_list as $type){
			foreach ($this->message_data as $section => $mdat){
				if ($mdat['type'] != $type){
					continue;
				}
				if (count($mdat['list'])){
					if (!empty($mdat['sort'])){
						asort($mdat['list']);
					}
					$msg_data[] = $mdat;
				}
			}
		}
		
		return $msg_data;
	}
}