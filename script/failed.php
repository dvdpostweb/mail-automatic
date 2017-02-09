<?php

class Failed extends Script {
	var $data;
	function __construct() {
		parent::__construct();
	}
	public function execute($mail_id)
	{
		$sql_data="select c.customers_email_address as customers_email, c.*, (select date_added from `credit_history` where customers_id = c.customers_id and (quantity_paid =-1 or quantity_free = -1) order by id desc limit 1)  t
		from customers c
		where customers_abo=1 and `customers_abo_suspended`=0  and customers_info_date_account_created< '2014-07-30' having t > '2014-03-01'";
		$this->data = tep_db_query($sql_data);
	}
	function add_data_row($data)
	{
		return $data;
	}
	/*
	function post_process($data)
	{
		return true;
	}*/

}
?>