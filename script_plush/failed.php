<?php

class Failed extends Script {
	var $data;
	function __construct() {
		parent::__construct();
	}
	public function execute($mail_id)
	{
		$sql_data="select c.email as customers_email,  c.*, (select ifnull(created_at,'2014-01-01') from `tokens` where customer_id = c.customers_id order by id desc limit 1)  t
from customers c
where customers_abo=1 and `customers_abo_suspended`=0  and created_at< '2014-07-30' having t >'2014-03-01' order by t";
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