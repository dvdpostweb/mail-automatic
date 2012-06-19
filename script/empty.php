<?php

class EMPTY extends Script {
	var $data;
	function __construct() {
		parent::__construct();
	}
	public function execute($mail_id)
	{
		$sql_data='';
		$this->data = tep_db_query($sql_data);
	}
	/*function add_data_row($data)
	{
		return $data;
	}
	function post_process($data)
	{
		return true;
	}*/

}
?>