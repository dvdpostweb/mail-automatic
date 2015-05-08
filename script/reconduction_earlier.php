<?php

class Reconduction_earlier extends Script {
	var $data;
	function __construct() {
		parent::__construct();
	}
	public function execute($mail_id)
	{
		
		/*$abo = tep_db_query('select * from aboprocess_stats order by aboprocess_id desc limit 1');
		$row_abo=tep_db_fetch_array($abo);
		$abo_process_id = $row_abo['aboprocess_id'];*/
		
		$sql_data='select customers_gender,  customers_lastname as lastname,customers_email_address as customers_email, date_format(customers_abo_validityto,"%d/%c/%X") datereconduction,customers_language,c.customers_id, site
		from customers c
		left join (select customer_id, max(t.created_at) d from tickets t join message_tickets mt on t.id= mt.`ticket_id` where mail_id = 648 group by customer_id) hist on c.customers_id = hist.customer_id
		where customers_abo_dvd_credit = 0
		      and customers_abo_type not in (5,6,7,8,9,41,42,133672,133671)
		      and customers_abo = 1 
		      and customers_registration_step = 100 
		      and customers_abo_suspended = 0
		      and (select a.`action` from abo a where a.`action` in (7,17 ) and a.customerid = c.customers_id order by a.abo_id desc limit 1) = 17
		      and (( datediff(now(),hist.d) > 15) or (hist.customer_id is null)) 
		      and customers_abo_validityto > Date_add(now(), interval 4 day)
		';
		//and (select sum(credit) from `customers_aboprocess_stats` where customers_id = c.customers_id and aboprocess_id > '.$abo_process_id.' order by id desc) = 0 
    
		$this->data = tep_db_query($sql_data);
	}
	function add_data_row($data)
	{
		if($data['site'] == 'nl')
    {
      $data['host'] = 'www.dvdpost.nl';
      $data['host_private'] = 'private.dvdpost.nl';
      $data['host_public'] = 'public.dvdpost.nl';
    }
    else
    {
      $data['host'] = 'www.dvdpost.be';
      $data['host_private'] = 'private.dvdpost.com';
      $data['host_public'] = 'public.dvdpost.com';
    }
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