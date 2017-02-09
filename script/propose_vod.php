<?php
class Propose_vod extends Script {
	var $data;
	function __construct() {
	}
	public function execute($mail_id)
	{
		$sql_data='select "test" customers_name, product_id,c.customers_id, customers_gender, customers_lastname, customers_firstname, customers_email_address customers_email, mail_id, customers_language  from customers c
		left join address_book a on a.customers_id = c.customers_id and a.address_book_id = c.customers_default_address_id
		join wishlist w on w.customers_id = c.customers_id
		join products p on w.product_id = p.products_id
		left join vod_wishlists v on v.customer_id = c.customers_id and v.imdb_id = p.imdb_id
		join propose_vods pv on p.imdb_id = pv.imdb_id
		where customers_abo=1 and customers_abo_suspended = 0 and entry_country_id=21  and v.imdb_id is null and date(w.`date_added`)=date(date_add(now(),interval -7 DAY)) and (select count(*) from streaming_products where imdb_id = p.imdb_id and
		streaming_products.status = "online_test_ok" and (streaming_products.available_from <= date(date_add(now(),interval -10 day)) and streaming_products.expire_at >= date(date_add(now(),interval -10 day)) or (streaming_products.available_backcatalogue_from <= date(now()) and streaming_products.expire_backcatalogue_at >= date(now()))) and available = 1) and mail_id = '.$mail_id.' and (customers_language = if(fr = 1,1,0) or customers_language = if(nl = 1,2,0) or customers_language = if(en = 1,3,0));';
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