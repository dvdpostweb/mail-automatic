<?php

class out_new extends Script {
	var $data;
	function __construct() {
		parent::__construct();
	}
	public function execute($mail_id)
	{
		$sql_data='select c.customers_email_address as customers_email, c.*,p.products_id products_id,osh.*,o.*,date(o.date_purchased) date,pd.products_image_big products_image,pd.products_name,p.*  from (select osh.* from orders_status_history osh join (select orders_id,max(orders_status_history_id) orders_status_history_id from orders_status_history osh group by orders_id)xx  on xx.orders_status_history_id = osh.orders_status_history_id) osh 
		         join orders o on osh.orders_id = o.orders_id 
		         join customers c on o.customers_id = c.customers_id 
		         join orders_products op on op.orders_id = o.orders_id 
		         join products p on op.products_id = p.products_id 
		         join products_description pd on p.products_id = pd.products_id and pd.language_id = c.customers_language 
		         where osh.new_value = 2 and osh.customer_notified= 0';
		$this->data = tep_db_query($sql_data);
	}
	function add_data_row($data)
	{
		if (strtoupper($data['customers_gender'])=='F')
		{
			$key='TEXT_FEMALE_GENDER_'.$data['customers_language']; 			
		}
		else
		{
			$key='TEXT_MALE_GENDER_'.$data['customers_language']; 			
		}
		$data['gender_simple'] = $this->get_key($key);
		if($data['customers_language']==1)
		{
			$lang_short = 'fr';
		}
		else if($data['customers_language']==2)
		{
			$lang_short = 'nl';
		}
		else
		{
			$lang_short = 'en';
		}
		

		$sql_product = 'select * from products p 
		join products_description pd on p.products_id = pd.products_id 
		left join directors d on products_directors_id = directors_id
		left join studio s on products_studio = studio_id
		where language_id = '.$data['customers_language'].' and p.products_id = '.$data['products_id'];
		$query_product = tep_db_query($sql_product);
		$product = tep_db_fetch_array($query_product);
		
		$actors_sql = 'select a.actors_id, actors_name from  products_to_actors pa left join actors a on pa.actors_id= a.actors_id where pa.products_id = '.$data['products_id'].' limit 8';
		$actors_query = tep_db_query($actors_sql);
		$actors_links='';
		while ($actors = tep_db_fetch_array($actors_query)) {
			if($product['products_type']=='DVD_ADULT')
				$actors_links.= '<a href="http://private.dvdpost.com/'.$lang_short.'/adult/actors/'.$actors['actors_id'].'/products" target="_BLANK" style="color: rgb(69, 69, 69); ">'.$actors['actors_name'].'</a>, ';
			else
				$actors_links.= '<a href="http://private.dvdpost.com/'.$lang_short.'/actors/'.$actors['actors_id'].'/products" target="_BLANK" style="color: rgb(69, 69, 69); ">'.$actors['actors_name'].'</a>, ';
		}
		$actors_links = substr($actors_links,0,-2);
		
		if($data['products_media'] == 'BlueRay')
		{
			$data['media']='bluray';
		}
		else
		{
			$data['media']='dvd';
		}
		$data['product_id']= $data['products_id'];
		if($data['products_type']=='DVD_ADULT')
		{
			$data['url_kind'] = 'adult/';
			$data['images_path'] = 'imagesx';
			if($data['studio_id']>0)
			{
				$data['director_id']= $product['studio_id'];
				$data['director_type']= "studios";
				$data['director_name']= $product['studio_name'];
			}
			else
			{
				$data['director_id']= 0;
				$data['director_type']= "studios";
				$data['director_name']= '';
			}
			$adult='true';
			
		}
		else
		{
			$data['url_kind'] = '';
			$data['images_path'] = 'images';
			if($product['directors_id']>0)
			{
				$data['director_id']= $product['directors_id'];
				$data['director_type']= "directors";
				$data['director_name']= $product['directors_name'];
			}
			else
			{
				$data['director_id']= 0;
				$data['director_type']= "directors";
				$data['director_name']= '';
			}
			$adult='false';
			
		}
		$data['list_actors']= $actors_links;
		$data['product_image']= $product['products_image_big'];
		$data['product_description']= $this->truncate($product['products_description'],1000);
		$data['product_year']= $product['products_year'];
		$data['product_title']= $product['products_name'];
		
		$rating_product =  $product['rating_count'] > 0 ? round(($product['rating_users'] / $product['rating_count']) * 2) : 0 ;
		for($i = 0 ; $i < 5 ; $i++)
		{
			if($rating_product>=2)
			{
				$type='on';
			}
			elseif($rating_product==1)
			{
				$type='half';
			}
			else
			{
				$type='off';
			}
			$rating_product -= 2;
		  $data['rating_star_'.($i+1)] = $type;
		}
	
	
	
	  $listing_sql = 'select p.rating_users,p.rating_count,p.products_id, pd.products_name , pd.products_image_big,p.products_media,p.imdb_id  from product_lists pl
		join listed_products lp on lp.product_list_id = pl.id
		join products p on lp.product_id = p.products_id';
		$listing_sql .= ' left join products_description pd on p.products_id = pd.products_id and pd.language_id=' . $data['customers_language'] ;

		switch ($data['customers_language']){
			case 1:
				$lang_short='fr';
				$listing_sql .= ' where pl.id = 110';
				$data['vod_list']=110;
			break;
			case 2:
				$lang_short='nl';
				$listing_sql .= ' where pl.id = 111';
				$data['vod_list']=111;
				
			break;
			case 3:
				$lang_short='en';
				$listing_sql .= ' where pl.id = 112';
				$data['vod_list']=112;
				
			break;
			}
		$listing_recom_sql = $listing_sql . ' order by rand() limit 7';
		#echo $listing_recom_sql;
		$recom_query = tep_db_query($listing_recom_sql);
		$i=1;
		while ($recom = tep_db_fetch_array($recom_query)) {
			$data['vod_title_'.$i] = $recom['products_name'];
			$data['vod_id_'.$i] = $recom['products_id'];
			$data['vod_imdb_id_'.$i] = $recom['imdb_id'];
			$data['vod_image_'.$i] = $recom['products_image_big'];
			if($recom['products_type']== 'DVD_ADULT')
			{
				$data['vod_url_kind']='adult/';
				$data['vod_images_path']='imagesx';
			}
			else
			{
				$data['vod_url_kind']='';
				$data['vod_images_path']='images';
			}
			$rating_product =  $recom['rating_count'] > 0 ? round(($recom['rating_users'] / $recom['rating_count']) * 2) : 0 ;
			for($j = 0 ; $j < 5 ; $j++)
			{
				if($rating_product>=2)
				{
					$type='on';
				}
				elseif($rating_product==1)
				{
					$type='half';
				}
				else
				{
					$type='off';
				}
				$rating_product -= 2;
				$data['vod_rating_star_'.$i.'_'.($j+1)] = $type;
			}
			
			$i++;
		}

		$request =  'http://partners.thefilter.com/DVDPostService';
		$format = 'RecommendationService.ashx';   // this can be xml, json, html, or php
		$args =  'cmd=DVDRecommendDVDs';
		$args .= '&id='.$data['products_id'];
		$args .= '&number=100';
		$args .= '&includeAdult='.$adult.'&verbose=false&clientIp='.$_SERVER['REMOTE_ADDR'];
	
	    // Get and config the curl session object
	    // 
	    // 
		
		#echo $request.'/'.$format.'?'.$args;
	    $session = curl_init($request.'/'.$format.'?'.$args);
	    curl_setopt($session, CURLOPT_HEADER, false);
	    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
		    //execute the request and close
	    $response = curl_exec($session);
	    curl_close($session);
	    // this line works because we requested $format='php' and not some other output format
	    // this is your data returned; you could do something more useful here than just echo it
	    try {
		  $recommend = new SimpleXMLElement($response);
		} catch (Exception $e) {
		  echo "bad xml";
		}
		$i=0;
		$list='';
		foreach ($recommend->children()->children() as $dvd) {
		
			if($i==0)
				$list=$dvd['Id'];
			else
				$list.=','.$dvd['Id'];
			$i++;
		}
		  if(empty($list))
			$list=0;
			$listing_sql = 'select p.rating_users,p.rating_count,p.products_id, pd.products_name , pd.products_image_big,p.products_media,products_type  from  dvdpost_be_prod.products p ';
			$listing_sql .= ' left join dvdpost_be_prod.products_description pd on p.products_id = pd.products_id and pd.language_id=' . $data['customers_language'] ;
			$listing_sql .= ' left join dvdpost_be_prod.wishlist w on w.product_id=p.products_id and w.customers_id=\'' . $data['customers_id'] . '\'' ;
			$listing_sql .= ' left join dvdpost_be_prod.wishlist_assigned wa on wa.products_id=p.products_id and wa.customers_id=\'' . $data['customers_id'] . '\' ';
			$listing_sql .= ' left join dvdpost_be_prod.products_uninterested  pu on pu.products_id=p.products_id and pu.customers_id=\'' . $data['customers_id'] . '\' ';
			$listing_sql .= ' where p.products_id in ('.$list.')';
			$listing_sql .= 'and w.product_id is null and wa.products_id is null and pu.products_id is null and (products_quantity > 0 or products_next =1) and products_media !="vod"';
			$listing_sql .= ' and (select count(*) from products_to_categories where categories_id =76 and products_id = p.products_id) = 0';
			
			switch ($customer_value['customers_language']){
				case 1:
					$listing_sql.= ' and p.products_language_fr >0 ';
					$lang_short='fr';
				break;
				case 2:
					$listing_sql.= ' and p.products_undertitle_nl >0 ';
					$lang_short='nl';
				break;
				case 3:
					$lang_short='en';
				break;
				}
			$listing_recom_sql = $listing_sql . ' order by rand() limit 7';
			$recom_query = tep_db_query($listing_recom_sql);
		
		$nb=tep_db_num_rows($recom_query);
		if($nb==0)
		{
			$data['recom_visible'] = 'none';
		}
		else
		{
			$data['recom_visible'] = 'block';
		}
		if ($nb < 7 )
		{
			$start = ($nb+1);
			for($i=$start;$i<=7;$i++)
			{
			$data['recom_title_'.$i] = '';
			$data['recom_id_'.$i] = '';
			$data['recom_image_'.$i] = '';
			$data['recom_visible_'.$i]='none';
			$data['recom_kind_'.$i] = 'dvd';
			$rating_product =  0 ;
			for($j = 0 ; $j < 5 ; $j++)
			{
				$type='off';
				$data['recom_rating_star_'.$i.'_'.($j+1)] = $type;
			}
			}
		}
		$i=1;
		while ($recom = tep_db_fetch_array($recom_query)) {
			$data['recom_title_'.$i] = $recom['products_name'];
			$data['recom_id_'.$i] = $recom['products_id'];
			$data['recom_image_'.$i] = $recom['products_image_big'];
			$data['recom_visible_'.$i]='';
			$data['recom_kind_'.$i] = $data['products_type'] == 'blueray'? 'bluray': 'dvd';
			
			
			$rating_product =  $recom['rating_count'] > 0 ? round(($recom['rating_users'] / $recom['rating_count']) * 2) : 0 ;
			for($j = 0 ; $j < 5 ; $j++)
			{
				if($rating_product>=2)
				{
					$type='on';
				}
				elseif($rating_product==1)
				{
					$type='half';
				}
				else
				{
					$type='off';
				}
				$rating_product -= 2;
				$data['recom_rating_star_'.$i.'_'.($j+1)] = $type;
			}
			$i++;
		}
		
	
		return $data;
	}
	function post_process($data)
	{
		$sql_up ='update orders_status_history set customer_notified  = 1 where orders_id =  '. $data['orders_id'];
		$status = tep_db_query($sql_up);
		return $status;
	}
	
	function truncate($text,$numb,$etc = "...") 
	{
		$text = html_entity_decode($text, ENT_QUOTES);
		if (strlen($text) > $numb) 
		{
			$text = substr($text, 0, $numb);
			$text = substr($text,0,strrpos($text," "));

			$punctuation = ".!?:;,-"; //punctuation you want removed

			$text = (strspn(strrev($text),  $punctuation)!=0)
			        ?
			        substr($text, 0, -strspn(strrev($text),  $punctuation))
			        :
			$text;

			$text = $text.$etc;
		}
		return $text;
	}

}
?>