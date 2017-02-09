<?php

class new_price extends Script {
	var $data;
	function __construct() {
		parent::__construct();
	}
	public function execute($mail_id)
	{
		$data_sub = tep_db_query('select codevalue = date(now()) today from `generalglobalcode` where codetype="new_price"');
    $row = tep_db_fetch_array($data_sub);
    if($row['today'] == '1')
    {
    	$sql_data='select customers_email_address as customers_email, c.*
 from customers c where customers_abo=1 and `customers_abo_auto_stop_next_reconduction`=0';

    }
    else
    {
    	$sql_data='select customers_email_address as customers_email, c.*
 from customers c where customers_abo=10 and `customers_abo_auto_stop_next_reconduction`=0';
    }
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