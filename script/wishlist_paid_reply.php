<?php

class Wishlist_paid_reply extends Script {
	var $data;
	function __construct() {
		parent::__construct();
	}
	public function execute()
	{
		$sql_data='select c.customers_id,count(w.wl_id) as size,customers_language,c.customers_email_address as customers_email,customers_firstname as firstname,customers_lastname as lastname,customers_gender,pa.qty_credit, if(qty_credit = 2 or qty_credit = 4,10,if(qty_credit = 6 or qty_credit = 8,20,if(qty_credit = 0 ,if(qty_at_home = 2,10,if(qty_at_home = 1,10,30)),30))) as min_size,pa.qty_at_home
						from customers c
						left join wishlist w on (w.customers_id = c.customers_id)
						left join products p on p.products_id = w.product_id
						left join products_abo pa on c.customers_abo_type = pa.products_id
						left join (select customer_id, max(t.created_at) d from tickets t join message_tickets mt on t.id= mt.`ticket_id` where mail_id = 452 group by customer_id) hist on c.customers_id = hist.customer_id
						where c.customers_abo = 1 and customers_registration_step = 100 and c.customers_abo_suspended = 0
						and (w.wishlist_type = "dvd_norm" or w.wishlist_type is null)
						and (p.products_status <> -1 or p.products_status is null ) and (p.products_next = 0 or p.products_next is null)
						and c.customers_abo_dvd_norm > 0 and c.customers_abo_dvd_adult = 0
						and (select a.`action` from abo a where a.`action` in (7,17 ) and a.customerid = c.customers_id order by a.abo_id desc limit 1) = 7
						and qty_credit > 0
						and (( datediff(now(),hist.d) > 3) or (hist.customer_id is null)) and sleep = 0
						group by c.customers_id
						having size < if(qty_credit = 2 or qty_credit = 4,10,if(qty_credit = 6 or qty_credit = 8,20,if(qty_credit = 0 ,if(qty_at_home = 2,10,if(qty_at_home = 1,10,30)),30)));';
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
	/*function post_process($data)
	{
		return true;
	}*/

}
?>