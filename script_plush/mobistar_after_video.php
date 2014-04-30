<?php

class mobistar_after_video extends Script {
	var $data;
	function __construct() {
		parent::__construct();
	}
	public function execute($mail_id)
	{
		$sql_data='select 0 customers_id, 1 customers_language,s.* from streaming_codes s
        left join customers c on c.email = s.email
        where s.email is not NULL and used_at is not null and c.email is null and date(used_at ) < date(DATE_ADD(NOW(), INTERVAL -3 day))';
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