<?php

class newsletterempty extends Script {
	var $data;
	function __construct() {
		parent::__construct();
	}
	public function execute($mail_id)
	{
		$sql_data='select count(*)nb,email customers_email,0 as customers_id,language_id customers_language, group_concat(product_id) products_id from `public_newsletters` n
    left join public_newsletter_products p on n.id = p.public_newsletter_id
    where product_id is null and n.created_at between date(DATE_ADD( now(), INTERVAL -1 DAY )) and date(now()) group by n.id;';
		$this->data = tep_db_query($sql_data);
	}
	/*function add_data_row($data)
	{
		return $data;
	}
	function post_process($data)
	{
		return true;
	}*/

}
?>