<?php

class Delay extends Script {
	var $mail;
	var $data;
	
	private $formating;
	
	function __construct() {
		$this->email_process = new EmailProcess();
	}
	public function execute()
	{
			$sql_data='SELECT o.orders_id ,o.customers_name as name,o.customers_name, date_format(o.date_purchased,"%d/%m/%Y") as date ,o.customers_email_address as mail,o.customers_email_address as customers_email, c.customers_language , o.customers_id, p.products_name,o.customers_country, CONCAT(p.products_name," [",p.products_id," - ",products_dvd,"]") as title, concat(o.customers_street_address," ",o.customers_postcode," ",o.customers_city) as address
										FROM orders o
										join orders_products od on o.orders_id=od.orders_id
										JOIN custserv cs ON o.orders_id = cs.orders_id and cs.custserv_cat_id=3
										join customers c on c.customers_id = o.customers_id
										JOIN products_description p ON p.products_id = cs.products_id and p.language_id = c.customers_language
										WHERE orders_status  in (12 , 17) AND admindate > "2009-12-01"
							AND date(now()) = date(DATE_ADD( admindate, INTERVAL 3 DAY ))  GROUP BY o.orders_id 	ORDER BY o.orders_id DESC;';
		$this->data = tep_db_query($sql_data);
	}
	function add_data_row($data)
	{
		$sql='insert into actions_key (customers_id ,actions_id , `key`,ref_id) values ('.$data['customers_id'].',"3",uuid(),'.$data['orders_id'].')';
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
			$data['host']=$host;
			$data['url']=$url;
		}
		else
		{
			return false;
		}
		return $data;
	}
	function post_process($data)
	{
		$custserv_message_query = tep_db_query("select * from custserv_auto_answer where language_id = '" . $data['customers_language']. "' and custserv_auto_answer_id = 21 ");  
		$custserv_message = tep_db_fetch_array($custserv_message_query);
		//$html=$this->modif_attributes($custserv_message['messages_html'],$modif);
		$html = $this->email_process->format($custserv_message['messages_html'],$data,false);
		$sql="INSERT INTO custserv (customers_id , language_id , custserv_cat_id , customer_date , subject,message,adminby ,admindate) 
		VALUES ('" . $data['customers_id']. "', '" . $data['customers_language'] . "', '6', now(), '" .addslashes($custserv_message['custserv_auto_answer_comment'])  ."','" . addslashes($html)."',99,now())" ;
    $status=tep_db_query($sql);
		return $status;
	}				
}
?>