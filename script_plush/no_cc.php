<?php

class no_cc extends Script {
	var $data;
	function __construct() {
		parent::__construct();
	}
	public function execute($mail_id)
	{
		$sql_data='select c.*, concat(customers_firstname," ",customers_lastname) customers_name,email customers_email from customers c
    join (select a.*,max(date) created_at from abo a where action in(1,6,8) group by customerid) a on c.customers_id = customerid
    where customers_abo = 1 and `customers_abo_payment_method`= 0 and date(a.created_at) = date(date_add(now(), interval -10 day))';
		$this->data = tep_db_query($sql_data);
	}
	function add_data_row($data)
	{  
	  
	  $sql = "select * 
        from home_products h
        join products_description pd on pd.products_id = h.product_id
        where  locale_id = ".$data['customers_language']." and kind ='tvod' and country = (select if(entry_country_id=124,'lu', if(entry_country_id=150,'nl','be')) from customers c left join address_book a on a.customers_id = c.customers_id and a.address_book_id = c.customers_default_address_id where c.customers_id = ".$data['customers_id'].") group by h.product_id
        order by id asc limit 4";
    $data_sub = tep_db_query($sql);
    $i = 1;
    while($row = tep_db_fetch_array($data_sub))
    {
      $data['product_id'.$i]= $row['products_id'];
      $data['products_image'.$i]= $row['products_image_big'];
      $i ++;
    }
		if (strtoupper($data['customers_gender'])=='F')
		{
			$key='TEXT_FEMALE_GENDER_'.$data['customers_language']; 			
		}
		else
		{
			$key='TEXT_MALE_GENDER_'.$data['customers_language']; 			
		}
		$data['gender_simple'] = $this->get_key($key);
		return $data;
	}
	/*function post_process($data)
	{
		return true;
	}*/

}
?>