<?php

class Reconduction_earlier_payed extends Script {
	var $data;
	function __construct() {
		parent::__construct();
	}
	public function execute()
	{
		$sql_data='select customers_gender,  customers_lastname as lastname,customers_email_address as customers_email, date_format(customers_abo_validityto,"%d/%c/%X") datereconduction,if(qty_credit = 2 or qty_credit = 4,10,if(qty_credit = 6 or qty_credit = 8,20,30)) as size,customers_language,c.customers_id
		from customers c
		left join products_abo pa on c.customers_abo_type = pa.products_id 
		left join (select customers_id,max(date) d from mail_messages_sent_history where mail_messages_id = 555 group by customers_id) hist on c.customers_id = hist.customers_id
		where customers_abo_dvd_credit = 0
		      and customers_abo_type not in (5,6,7,8,9,41,42)
		      and customers_abo = 1 
		      and customers_registration_step = 100 
		      and customers_abo_suspended = 0
		      and (select a.`action` from abo a where a.`action` in (7,17 ) and a.customerid = c.customers_id order by a.abo_id desc limit 1) = 7
		      and (( datediff(now(),hist.d) > 15) or (hist.customers_id is null)) 
		      and customers_abo_validityto > Date_add(now(), interval 4 day) 
		';
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