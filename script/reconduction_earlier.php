<?php

class Reconduction_earlier extends Script {
	var $data;
	function __construct() {
		parent::__construct();
	}
	public function execute()
	{
		
		/*$abo = tep_db_query('select * from aboprocess_stats order by aboprocess_id desc limit 1');
		$row_abo=tep_db_fetch_array($abo);
		$abo_process_id = $row_abo['aboprocess_id'];*/
		
		$sql_data='select customers_gender,  customers_lastname as lastname,customers_email_address as customers_email, date_format(customers_abo_validityto,"%d/%c/%X") datereconduction,customers_language,c.customers_id
		from customers c
		left join (select customers_id,max(date) d from mail_messages_sent_history where mail_messages_id = 554 group by customers_id) hist on c.customers_id = hist.customers_id
		where customers_abo_dvd_credit = 0
		      and customers_abo_type not in (5,6,7,8,9,41,42)
		      and customers_abo = 1 
		      and customers_registration_step = 100 
		      and customers_abo_suspended = 0
		      and (select a.`action` from abo a where a.`action` in (7,17 ) and a.customerid = c.customers_id order by a.abo_id desc limit 1) = 17
		      and (( datediff(now(),hist.d) > 15) or (hist.customers_id is null)) 
		      and customers_abo_validityto > Date_add(now(), interval 4 day)
		';
		//and (select sum(credit) from `customers_aboprocess_stats` where customers_id = c.customers_id and aboprocess_id > '.$abo_process_id.' order by id desc) = 0 
    
		$this->data = tep_db_query($sql_data);
	}
	function add_data_row($data)
	{
		$key='GENDER_'.strtoupper($data['customers_gender']).'_'.$data['customers_language'];
		$data['gender']=$this->get_key($key);
		if($data['size']==0)
		{
			$data['situation']=$this->get_key('SIZE_NULL_'.$data['customers_language']);
		}
		else
		{
			$data['situation']=sprintf($this->get_key('SIZE_FEW_'.$data['customers_language']),$data['size']);
		}
		
		return $data;
	}

}
?>