<?php

class Survey extends Script {
	var $data;
	function __construct() {
	}
	public function execute($mail_id)
	{
		$sql_data='SELECT cas.id,c.customers_id, customers_language ,entry_country_id,customers_email_address as  customers_email FROM customers_abo_stop cas
			 join customers c on cas.customers_id = c.customers_id 
			 left join address_book a on a.customers_id = c.customers_id and a.address_book_id = c.customers_default_address_id
			 where date_stop>"2009-09-01" and date(now()) = date(DATE_ADD( date_stop, INTERVAL 10 DAY ))';
		$this->data = tep_db_query($sql_data);
	}
	function add_data_row($data)
	{
		$sql='insert into actions_key (customers_id ,actions_id , `key`) values ('.$data['customers_id'].',"4",uuid())';
		tep_db_query($sql);
		$id=tep_db_insert_id();
		$sql_select='select * from actions_key where id ='.$id;
		$query_select=tep_db_query($sql_select);
		$row_select=tep_db_fetch_array($query_select);
		
		$uniqid=$row_select['key'];
		
		switch(strtolower($data['entry_country_id']))
		{
			case '150':
				$host='www.dvdpost.nl';
				$logo='<img src="http://www.dvdpost.nl/images/www3/logonl.jpg" />';
			break;
			case 21:
			default:
				$host='www.dvdpost.be';
				$logo='<img src="http://www.dvdpost.be/images/www3/logo.jpg" />';
				
			
		}
		$url='http://'.$host.'/actions.php?uniq_id='.$uniqid;
		$data['url']=$url;
		$data['logo_dvdpost']=$logo;
		$data['site_link']='http://www.dvdpost.be';
		return $data;		
	}

}
?>