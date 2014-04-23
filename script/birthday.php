<?php

class Birthday extends Script  {
	var $mail;
	var $data;
	function __construct() {
		
	}
	public function execute($mail_id)
	{
			$sql_data='SELECT c.customers_id as id, c.customers_id, customers_language ,entry_country_id,customers_email_address as customers_email,"www" as site_link, "promotion" as promotion, "avantage" as avantage   FROM customers c left join address_book a on a.customers_id = c.customers_id and a.address_book_id = c.customers_default_address_id where date_format(now(),"%d-%m") = date_format(customers_dob,"%d-%m") and customers_abo=1 limit 1;';
			$this->data = tep_db_query($sql_data);
	}
	function add_data_row($data)
	{
		$sql='insert into actions_key (customers_id ,actions_id , `key`,ref_id) values ('.$data['customers_id'].',"3",uuid(),1)';
		$status=tep_db_query($sql);
		if($status)
		{
			$id=tep_db_insert_id();
			$sql_select='select * from actions_key where id ='.$id;
			$query_select=tep_db_query($sql_select,'db_config',true);
			$row_select=tep_db_fetch_array($query_select);

			$uniqid=$row_select['key'];
			switch(strtolower($data['customers_country']))
			{
				case 'nederlands':
					$host='www.dvdpost.nl';
				break;
				case 21:
				default:
					$host='www.dvdpost.be';
			
			}
			$url='http://'.$host.'/actions.php?uniq_id='.$uniqid;
			$data['url']=$url;
			return $data;
		}
	}
}
?>