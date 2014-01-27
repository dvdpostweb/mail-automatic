<?php

class Mobistar extends Script {
	var $data;
	function __construct() {
		parent::__construct();
	}
	public function execute($mail_id)
	{
		$sql_data="select p.email, p.email customers_email, 0 customers_id, locale_id customers_language 
    from streaming_codes s
    join prospects p on s.email = p.email
    where TIMESTAMPDIFF(minute, used_at,now()) >= 30 and TIMESTAMPDIFF(minute, used_at,now()) < 90 ";
		$this->data = tep_db_query($sql_data);
	}
	function add_data_row($data)
	{
		return $data;
	}
	
	/*function post_process($data)
	{
		return true;
	}*/

}
?>