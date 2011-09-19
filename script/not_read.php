<?php

class not_read extends Script {
	var $data;
	function __construct() {
		parent::__construct();
	}
	public function execute()
	{
		$sql_data='select customers_email_address as customers_email,c.customers_id,customers_language
		from dvdpost_be_prod.tickets t
		join customers c on customers_id = customer_id
		join dvdpost_be_prod.message_tickets mt on ticket_id = t.`id`
		join mail_messages mm on mt.mail_id = mm.mail_messages_id
		where  datediff(now(), mt.created_at)>=7 and user_id > 0 and is_read =0  and reminder = 1 and customers_abo = 1 group by c.customers_id';
		$this->data = tep_db_query($sql_data);
	}
	/*function add_data_row($data)
	{
		$data['url']="test";
		return $data;
	}
	function post_process($data)
	{
		return true;
	}*/

}
?>