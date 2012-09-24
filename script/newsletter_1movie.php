<?php

class newsletter_1movie extends Script {
	var $data;
	function __construct() {
		parent::__construct();
	}
	public function execute($mail_id)
	{
		$sql_data='select count(*)nb,email customers_email,0 as customers_id,language_id customers_language, group_concat(product_id) products_id from `public_newsletters` n
    left join public_newsletter_products p on n.id = p.public_newsletter_id
    join products pr on pr.products_id = p.product_id
    left join `customers` c on c.customers_email_address = n.email
    where products_type= "dvd_norm" and products_status !=-1 and product_id is not null and n.created_at between date(DATE_ADD( now(), INTERVAL -1 DAY )) and date(now()) group by n.id having nb=1';
		$this->data = tep_db_query($sql_data);
	}
	function add_data_row($data)
	{
	  $products = split(',', $data['products_id']);
	  $i = 0;
	  while ($i < count($products)) {
       $a = $products[$i];
       $sql_subdata= "select ifnull(cast(cast((cast((rating_users/rating_count)*2 AS SIGNED)/2) as decimal(2,1))*10 AS SIGNED),0) rating,p.products_id, products_name,categories_name, products_year,countries_name, directors_name,directors_id,products_description,pt.trailers_id,products_image_big, streaming_products.id streaming_products_id
              from products p
              join products_description pd on p.products_id = pd.products_id and pd.language_id = ".$data['customers_language']."
              join products_to_categories pa on p.products_id = pa.products_id
              join `categories_description` c on c.categories_id = pa.categories_id and c.language_id = ".$data['customers_language']."
              left join products_countries on products_countries_id = countries_id
              left join directors on products_directors_id = directors_id
              left join products_trailers pt on p.products_id = pt.products_id and pt.language_id=".$data['customers_language']."
              left join streaming_products on streaming_products.imdb_id = p.imdb_id
          	  where p.products_id = ".$a." and ((streaming_products.status = 'online_test_ok' and ((streaming_products.available_from <= date(now()) and streaming_products.expire_at >= date(now())) or (streaming_products.available_backcatalogue_from <= date(now()) and streaming_products.expire_backcatalogue_at >= date(now()))) and available = 1) or (p.vod_next=1 or streaming_products.imdb_id is null))
                 group by p.products_id;";
       echo $sql_subdata;
       $data_sub = tep_db_query($sql_subdata);
       $row = tep_db_fetch_array($data_sub);
       var_dump($row);
       $i++;
       $data['product_id'.$i]= $row['products_id'];
       $data['image'.$i]= $row['products_image_big'];
       $data['director_id'.$i]= $row['directors_id'];
       $data['director_name'.$i]= $row['directors_name'];
       $data['title'.$i] = $row['products_name'];
       $data['category'.$i] = $row['categories_name'];
       $data['year'.$i] = $row['products_year'];
       $data['country'.$i] = $row['countries_name'];
       $data['trailer_display'.$i] = $row['trailers_id'] > 0 ?  'inline' : 'none';
       $data['streaming_display'.$i] = $row['streaming_products_id'] > 0 ?  'inline' : 'none';
       $data['star'.$i] = $row['rating'];
       $data['description'.$i] = $row['products_description'];
       
       $sql_actors = 'select * from actors a
       join products_to_actors pa on a.actors_id = pa.actors_id where products_id = '.$a;
       $data_act = tep_db_query($sql_actors);
       $actors = '';
       if($data['customers_language']==1)
       {
         $locale = 'fr';
       }
       else if($data['customers_language']==2)
       {
         $locale = 'nl';
       }
       else
       {
         $locale = 'en';
       }
       $j=0;
       while($row_act = tep_db_fetch_array($data_act))
       {
         $j++;
         if($j>1)
         {
           $actors .= ', ';
         }
         $actors .= '<a href="http://public.dvdpost.com/'.$locale.'/actors/'.$row_act['actors_id'].'/products" style="COLOR: rgb(43,56,64); FONT-SIZE: 14px; TEXT-DECORATION: underline">'.$row_act['actors_name'].'</a>';
       }
       $data['actors_data'.$i] = $actors;
       
       
    }
		return $data;	
	}
	/*function post_process($data)
	{
		return true;
	}*/

}
?>