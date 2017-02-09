<?php

class Step extends Script{
	var $mail;
	var $data;
	
	CONST PERIOD_DAY='PERIOD_DAY_';
	CONST PERIOD_MONTH='PERIOD_MONTH_';
	CONST PERIOD_YEAR_FR='PERIOD_YEAR_';
	
	CONST AVANTAGE_FREE='AVANTAGE_FREE_';
	
	CONST AVANTAGE_OTHER='AVANTAGE_OTHER_';
	
	CONST STEP31='31';
	CONST STEP32='32';
	CONST STEP33='33';
	
	var $avantage_array;
	var $period_day;
	var $period_month;
	var $period_year;
	var $avantage_other;
	
	var $count_movies;
	var $count_bluray;
	var $count_vod;
	var $count_customer;
	
	
	function __construct() {
		parent::__construct();
	}
	public function execute($mail_id)
	{
		$sql_data='(SELECT c.customers_id,c.customers_id as id, customers_language , email,email as customers_email,activation_discount_code_id,activation_discount_code_type
        				 FROM customers c
        				 JOIN products_abo pa ON pa.products_id = `customers_abo_type`
        				 where date(now()) = date(DATE_ADD( date(created_at), INTERVAL 1 DAY )) and (customers_registration_step=33 or customers_registration_step=31 or customers_registration_step=32))';	
		$this->data = tep_db_query($sql_data);
	}

	function add_data_row($data)
	{
	  if($data['activation_discount_code_type'] == 'A')
	  {
	    $activation_sql = "SELECT `activation_code`.* FROM `activation_code` WHERE `activation_code`.`activation_id` = ".$data['activation_discount_code_id']." LIMIT 1";
      $activation_query = tep_db_query($activation_sql);
    	$promo = tep_db_fetch_array($activation_query);
    	$promo_text = $this->activation_text($promo, $data['customers_language']);
	  }
	  else
	  {
	    $discount_query = tep_db_query("SELECT `discount_code`.* FROM `discount_code` WHERE `discount_code`.`discount_code_id` = ".$data['activation_discount_code_id']." LIMIT 1",'db',true);
  		$promo = tep_db_fetch_array($discount_query);
      $promo_text = $this->discount_text($promo, $data['customers_language']);
	  }
		
    
    $data['root_url'] = 'http//www.plush.be/';
    
  	$data['promotion'] = $promo_text;
		return $data;
	}
	function discount_text($discount_values, $locale)
  {
    switch($locale)
    {
      case 1:
        $text = $discount_values['discount_text_fr'];
        break;
      case 2:
        $text = $discount_values['discount_text_nl'];
        break;
      case 3:
        $text = $discount_values['discount_text_en'];
        break;
    }
    return $text;
  }
  function activation_text($values, $locale)
  {
    switch($locale)
    {
      case 1:
        $text = $values['activation_text_fr'];
        break;
      case 2:
        $text = $values['activation_text_fr'];
        break;
      case 3:
        $text = $values['activation_text_fr'];
        break;
    }
    return $text;
  }
	
}
?>