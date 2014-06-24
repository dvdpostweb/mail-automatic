<?php

class exp_reply extends Script {
	var $data;
	function __construct() {
		parent::__construct();
	}
	public function execute($mail_id)
	{
		$sql_data='select 0 customers_id, 1 customers_language, 1645155 imdb_id, name streaming_code, email, email customers_email, streaming_codes.* from `streaming_codes` where email is not null and used_at is null and date(updated_at) = date(DATE_ADD(now(), INTERVAL -3 day)) and (name like "EXP%" or name like "TEXP%" or name like "SEXP%" or name like "FEXP%" or name like "WEXP%" ) ;';
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