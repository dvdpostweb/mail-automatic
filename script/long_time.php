<?php

class long_time extends Script {
	var $data;
	function __construct() {
		parent::__construct();
	}
	public function execute()
	{
		$sql_data='select c.*, customers_email_address as customers_email,concat(customers_firstname," ",customers_lastname) customers_name, date_format(customers_abo_validityto,"%d/%c/%X") date_abo_stop from customers c 
		left join (select customers_id,max(date) d from mail_messages_sent_history where mail_messages_id = 574 group by customers_id) hist on c.customers_id = hist.customers_id
		where `customers_abo_auto_stop_next_reconduction` =1 and customers_abo=1 
		and datediff(`customers_abo_validityto`,now())<=10 and datediff(`customers_abo_validityto`,now())>=0
		and `customers_abo_suspended`=0
		and (select a.`action` from abo a where a.`action` in (7,17 ) and a.customerid = c.customers_id order by a.abo_id desc limit 1) = 7
		and (( datediff(now(),hist.d) > 20) or (hist.customers_id is null))';
		$this->data = tep_db_query($sql_data);
	}
	function add_data_row($data)
	{
		if (strtoupper($data['customers_gender'])=='F')
		{
			$key='TEXT_FEMALE_GENDER_'.$data['customers_language']; 			
		}
		else
		{
			$key='TEXT_MALE_GENDER_'.$data['customers_language']; 			
		}
		$data['gender_simple'] = $this->get_key($key);
		return $data;
	}
	/*function post_process($data)
	{
		return true;
	}*/

}
?>