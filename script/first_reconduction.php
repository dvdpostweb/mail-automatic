<?php

class First_reconduction extends Script {
	var $data;
	function __construct() {
		parent::__construct();
	}
	public function execute()
	{
		$sql_data='select customers_id, concat(customers_firstname," ",customers_lastname) customers_name,  date_format(customers_abo_validityto,"%d/%m/%Y") next_reconduction_date,customers_language, customers_email_address as customers_email, (select date_format(date,"%d/%m/%Y") from abo a where a.`action` in (7,17 ) and a.customerid = c.customers_id and action = 17 order by a.abo_id desc limit 1) subscription_start,  p.products_price formula_price, qty_credit formula_quantity,customers_abo_payment_method_name payement_type_db
		from customers c
		join products p on customers_next_abo_type = p.products_id
		join products_abo pa on customers_next_abo_type = pa.products_id
		join customers_abo_payment_method pm on customers_abo_payment_method_id = c.customers_abo_payment_method
		where datediff(`customers_abo_validityto`,now())=4 and (select a.`action` from abo a where a.`action` in (7,17 ) and a.customerid = c.customers_id order by a.abo_id desc limit 1) = 17;';
		$this->data = tep_db_query($sql_data);
	}
	function add_data_row($data)
	{
		switch($data['payement_type_db'])
		{
			case 'OGONE':
				$data['payement_type'] = $this->get_key("TEXT_PAYMENT_METHOD_CC_".$data['customers_language']);
				break;
			case 'DOMICILIATION':
				$data['payement_type'] = $this->get_key("TEXT_PAYMENT_METHOD_DOM_".$data['customers_language']);
				break;
			default:
				$data['payement_type'] = $this->get_key("TEXT_PAYMENT_METHOD_BT_".$data['customers_language']);
		}
		return $data;
	}
	/*function post_process($data)
	{
		return true;
	}*/

}
?>