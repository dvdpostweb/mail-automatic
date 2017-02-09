<?php

class exp extends Script {
	var $data;
	function __construct() {
		parent::__construct();
	}
	public function execute($mail_id)
	{
		$sql_data='select p.email, p.email customers_email, 0 customers_id, locale_id customers_language 
    from streaming_codes s
    join prospects p on s.email = p.email
    where TIMESTAMPDIFF(minute, used_at,now()) >= 30 and TIMESTAMPDIFF(minute, used_at,now()) < 90 and (name like "EXP%" or name like "TEXP%" or name like "SEXP%" or name like "FEXP%" or name like "WEXP%" or name like "PEXP%" ) ';
		$this->data = tep_db_query($sql_data);
	}
	function add_data_row($data)
	{
		 if($data['customers_language'] == '1')
	    {
	      $list_lang = 'fr'; 
	    }
	    else if($data['customers_language'] == '2')
	    {
	      $list_lang = 'nl';
	    }
	    else
	    {
	      $list_lang = 'en';
	    }


	    $sql_list = "SELECT * FROM `products` p
	    JOIN `lists` l ON l.product_id = p.`products_id` 
	    join streaming_products sp on p.imdb_id = sp.imdb_id 
	    join `products_description` pd on p.products_id = pd.products_id and pd.language_id = ".$data['customers_language']."
	    WHERE (l.".$list_lang." = 1) and available =1 and source = 'alphanetworks' and status = 'online_test_ok' and ( (available_from < now() and expire_at > now()) or (available_backcatalogue_from < now() and expire_backcatalogue_at > now())) and country = 'BE' group by p.products_id order by l.id desc limit 4";
	    $query_list=tep_db_query($sql_list,'db_link',true);
	    $i=1;
	    
	  	while($p=tep_db_fetch_array($query_list))
	    {
	      $data['products_id'.$i] = $p['products_id'];
	      $data['products_id'.$i.'_name'] = $p['products_name'];
	      $data['products_id'.$i.'_img'] = $p['products_image_big'];
	      $i++;
	    }
		return $data;
	}
	
	/*function post_process($data)
	{
		return true;
	}*/

}
?>