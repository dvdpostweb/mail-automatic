
<?php

class launch_48 extends Script {
	var $data;
	function __construct() {
		parent::__construct();
	}
	public function execute($mail_id)
	{
		$sql_data='select email customers_email, customers_language from discount_use d
    join abo a on a.customerid = d.customers_id
    join customers c on d.customers_id = c.customers_id
    where action=4 and `discount_code_id`in (105,106,107,108,109,110,111,112,113,114,115,116,117,118,119,120) and date(discount_use_date) = date(now())
    group by c.customers_id;';
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