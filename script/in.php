<?php

class in extends Script {
	var $data;
	function __construct() {
		parent::__construct();
	}
	public function execute()
	{
		$sql_data='select c.customers_email_address as customers_email,c.*,p.products_id products_id,osh.*,o.*,date(o.date_purchased) date,pd.products_image_big products_image,pd.products_name,p.*  from (select osh.* from orders_status_history osh join (select orders_id,max(orders_status_history_id) orders_status_history_id from orders_status_history osh group by orders_id)xx  on xx.orders_status_history_id = osh.orders_status_history_id) osh 
		         join orders o on osh.orders_id = o.orders_id 
		         join customers c on o.customers_id = c.customers_id 
		         join orders_products op on op.orders_id = o.orders_id 
		         join products p on op.products_id = p.products_id 
		         join products_description pd on p.products_id = pd.products_id and pd.language_id = c.customers_language 
		         where osh.new_value = 3 and osh.customer_notified= 0';
		$this->data = tep_db_query($sql_data);
	}
	function add_data_row($data)
	{
		if (strtoupper($data['customers_gender'])=='f')
		{
			$key='TEXT_FEMALE_GENDER_'.$data['customers_language']; 			
		}
		else
		{
			$key='TEXT_MALE_GENDER_'.$data['customers_language']; 			
		}
		$data['gender_simple'] = $this->get_key($key);
		
		
		$request = 'http://www.dvdpost.be/webservice';
		$format = 'recommendations_dvd_to_dvd.php';
		$args='product_id='.$data['products_id'].'&limit=7&customer_id='.$data['customers_id'].'&hide=1';
		$session = curl_init($request.'/'.$format.'?'.$args);
	  curl_setopt($session, CURLOPT_HEADER, false);
	  curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
	    //execute the request and close
	  $response = curl_exec($session);
	  curl_close($session);

		$request = 'http://www.dvdpost.be/webservice';
		$format = 'vod_selection.php';
		$args='limit=7&customer_id='.$data['customers_id'];
		$session = curl_init($request.'/'.$format.'?'.$args);
	  curl_setopt($session, CURLOPT_HEADER, false);
	  curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
	    //execute the request and close
	  $response2 = curl_exec($session);
	  curl_close($session);

		$request = 'http://www.dvdpost.be/webservice';
		$format = 'movie_detail.php';
		$args='product_id='.$data['products_id'].'&customer_id='.$data['customers_id'].'&type=in';
		$session = curl_init($request.'/'.$format.'?'.$args);
	  curl_setopt($session, CURLOPT_HEADER, false);
	  curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
	    //execute the request and close
	  $response3 = curl_exec($session);
	  curl_close($session);
	
		$data['movie_details'] =$response3;
		$data['recommendation_dvd_to_dvd'] =$response;
		$data['selection_vod'] =$response2;
	
		return $data;
	}
	function post_process($data)
	{
		$sql_up ='update orders_status_history set customer_notified  = 1 where orders_id =  '. $data['orders_id'];
		$status = tep_db_query($sql_up);
		return $status;
	}

}
?>