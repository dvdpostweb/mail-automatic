<?php

class mobistar_after_video extends Script {
	var $data;
	function __construct() {
		parent::__construct();
	}
	public function execute($mail_id)
	{
		$sql_data='select 0 customers_id, 1 customers_language,s.*, s.email customers_email from streaming_codes s
        left join customers c on c.email = s.email
        where s.email is not NULL and used_at is not null and customers_abo !=1 and date(used_at ) = date(DATE_ADD(NOW(), INTERVAL -3 day)) and name like "MB%"' group by s.email';
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
