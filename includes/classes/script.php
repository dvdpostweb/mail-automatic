<?php

class SCRIPT {
	var $data;
	var $key;
	function __construct() {
		$sql_langue='SELECT *
		FROM dvdpost_common.translation2
		WHERE `translation_page` = "automatic_mails"';
		$query_lang=tep_db_query($sql_langue);
		
		while($row=tep_db_fetch_array($query_lang))
		{
			$key=$row['translation_key'].'_'.$row['language_id'];
			$this->$key=$row['translation_value'];
		}
	}
	public function get_key($key)
	{
		return $this->$key;
	}
	public function execute()
	{
	}
	function add_data_row($data)
	{
		return $data;
	}
	function post_process($data)
	{
		return true;
	}
	function get_data()
	{
		return $data;
	}
}
?>