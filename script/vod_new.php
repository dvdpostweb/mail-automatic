<?php

class vod_new extends Script {
	var $data;
	function __construct() {
		parent::__construct();
	}
	public function execute($mail_id)
	{
		$sql_data='select customers_email_address as customers_email,c.customers_id,customers_language, v.imdb_id,customers_gender, concat(customers_firstname," ",customers_lastname) customers_name
    					from vod_wishlists v
    					join streaming_products sp on sp.imdb_id = v.imdb_id
    					join customers c on customer_id = customers_id and (customers_language = language_id or customers_language = subtitle_id)
    					where customers_abo=1 and ((expire_at > now()  and available_from = date(now())) or (expire_backcatalogue_at > now()  and available_backcatalogue_from = date(now()))) and available = 1 and status = "online_test_ok" and is_ppv =0 and sp.country="be"
    					group by v.imdb_id, c.customers_id;';
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
		
		$sql_p = 'select * from products where products_status !=-1 and imdb_id = '.$data['imdb_id']. ' limit 1';
		$query_p = tep_db_query($sql_p);
		$p = tep_db_fetch_array($query_p);
		$data['products_id'] = $p['products_id'];
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
		$data['date']= 'test';
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
		return $data;
	}
	/*function post_process($data)
	{
		return true;
	}*/
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