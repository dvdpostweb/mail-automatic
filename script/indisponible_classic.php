<?php

class Indisponible_classic extends Script {
	var $data;
	function __construct() {
		parent::__construct();
	}
	public function execute($mail_id)
	{
		$sql_data="select  products_series_id,customers_gender,p.imdb_id,p.products_id, pd.products_name,concat(c.customers_firstname,' ',c.customers_lastname) as customers_name,c.customers_email_address as customers_email,c.customers_id, customers_language,products_media, pd.products_image_big as products_image ,(select ps.products_id from products ps
				left join products_to_languages ptl on ps.products_id = ptl.products_id
				left join `products_to_undertitles` ptu on ps.products_id = ptu.products_id
				where ps.products_status != -1 AND products_type = 'dvd_norm' and imdb_id = p.imdb_id and products_media = 'dvd' and p.products_media ='blueray' and if(c.customers_language = 1, products_languages_id=1, if(c.customers_language =  2 , products_undertitles_id=2, 1=1)) limit 1) products_id2 
				from customers c
				 join wishlist w on w.customers_id = c.customers_id
				 join products p on p.products_id = product_id
				 join products_description pd on pd.products_id = product_id and language_id = c.customers_language
				 left join streaming_products on streaming_products.`imdb_id`= p.imdb_id and streaming_products.available_from < now() and streaming_products.expire_at > now() and streaming_products.status = 'online_test_ok'
				 where products_availability = -2 and c.customers_abo = 1 and products_type = 'DVD_NORM' and products_status !=-1 
				 and not exists (select * from products_dvd pd where pd.products_dvd_status in (1,21,24,25,23) and pd.products_id = p.products_id) and streaming_products.id is null and site !='nl'

				 group by products_series_id,p.imdb_id,c.customers_id
				 having products_id2 is null;";
		$this->data = tep_db_query($sql_data);
	}
	function add_data_row($data)
	{
		$name= preg_replace("/\(disc .*\)/i", "", $data['products_name']);
		$name= preg_replace("/\(volume .*\)/i", "", $name);
		$data['products_name']=$name;
		if($data['customers_gender']=="m")
		{
			$key='TEXT_MALE_GENDER_'.$data['customers_language'];
		}
		else
		{
			$key='TEXT_FEMALE_GENDER_'.$data['customers_language'];
		}
		$data['gender_simple']=$this->get_key($key);
		
		$request = 'http://www.dvdpost.be/webservice';
		$format = 'recommendations_dvd_to_dvd.php';
		$args='product_id='.$data['products_id'].'&limit=7&customer_id='.$data['customers_id'].'&hide=1';
		if (strtolower($data['products_media']) =='dvd')
		{	
			$data['indispo_title'] = 'titre_indisponible';
			$data['indispo_jacket'] = 'dvd_indisponible';
		}
		else
		{
			$data['indispo_title'] = 'titre_indisponiblebluray';
			$data['indispo_jacket'] = 'blu_ray_indisponible';
		}
			
		$session = curl_init($request.'/'.$format.'?'.$args);
	  curl_setopt($session, CURLOPT_HEADER, false);
	  curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
	    //execute the request and close
	  $response = curl_exec($session);
	  curl_close($session);
		$data['recommendation_dvd_to_dvd']=$response;
		$data['recommendation_display']= ((trim($response) =='') ? 'style="display:none"' : '' );
		return $data;
	}
	function post_process($data)
	{
		if ($data['products_series_id']>0)
			$sql_up = 'update products set products_availability = -1 where products_series_id = '.$data['products_series_id'].' and products_availability=-2';
		else
			$sql_up = 'update products set products_availability = -1 where products_id = '.$data['products_id'].' and products_availability=-2';
		$status = tep_db_query($sql_up);
		return $status;
	}

}
?>