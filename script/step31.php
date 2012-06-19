<?php

class Step31 extends Script{
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
	var $count_customer;
	
	
	function __construct() {
		parent::__construct();
		$this->count_movies = $this->get_count_movies('all');
		$this->count_bluray =$this->get_count_movies('bluray');
		$this->count_customer =$this->get_count_customers();
	}
	public function execute($mail_id)
	{
		$sql_data='(SELECT c.customers_id,c.customers_id as id, customers_language ,entityid ,customers_email_address,customers_email_address as customers_email,discount_code as promotion,discount_type as abo_type, discount_value as abo_value, discount_abo_validityto_type as type,discount_abo_validityto_value as value,abo_dvd_credit  ,pa.qty_credit
				 FROM customers c
				 JOIN discount_code dc ON c.`activation_discount_code_id` = dc.discount_code_id
				 JOIN products_abo pa ON pa.products_id = `customers_abo_type`
				 where date(now()) = date(DATE_ADD( customers_info_date_account_created, INTERVAL 3 DAY )) and (customers_registration_step='.self::STEP31.' or customers_registration_step='.self::STEP32.' or customers_registration_step='.self::STEP33.') AND `activation_discount_code_type` = "d" and dc.group_id !=152
							)union(
				SELECT c.customers_id,c.customers_id as id, customers_language,entityid, customers_email_address,customers_email_address as customers_email,activation_code as promotion,1 as abo_type, 0 as abo_value, validity_type as type,validity_value as value,abo_dvd_credit ,pa.qty_credit
				 FROM customers c
				 JOIN activation_code dc ON c.`activation_discount_code_id` = dc.activation_id
				 JOIN products_abo pa ON pa.products_id = `customers_abo_type`
				 where date(now()) = date(DATE_ADD( customers_info_date_account_created, INTERVAL 3 DAY )) and (customers_registration_step='.self::STEP31.' or customers_registration_step='.self::STEP32.' or customers_registration_step='.self::STEP33.') AND `activation_discount_code_type` = "a"  and dc.activation_group!=152 )';				
			
		$this->data = tep_db_query($sql_data);
	}

	function add_data_row($data)
	{
		$language=$data['customers_language'];
		switch(strtolower($data['entry_country_id']))
		{
			case '150':
				$host='www.dvdpost.nl';
				$logo='<img src="http://www.dvdpost.nl/images/www3/logonl.jpg" />';
			break;
			case 21:
			default:
				$host='www.dvdpost.be';
				$logo='<img src="http://www.dvdpost.be/images/www3/logo.jpg" />';
		}
		if($data['abo_value']==0)
		{	
			if($data['abo_dvd_credit']>0)	
				$credit=$data['abo_dvd_credit'];
			else
				$credit=$data['qty_credit'];
			switch($data['type'])
			{
				case 1:
					$key=self::PERIOD_DAY.$language;
				break;
				case 2:
					$key=self::PERIOD_MONTH.$language;
				break;
				case 3:
					$key=self::PERIOD_DAY.$language;
				break;
			}
			$period='<strong>'.$data['value'].' '.$this->get_key($key).'</strong>';
			$key=self::AVANTAGE_FREE.$language;
			$avantage=sprintf($this->get_key($key),$credit,$period);
		}
		else
		{
			$key=self::AVANTAGE_OTHER.$language;
			$avantage=$this->get_key($key);
		}
		$data['host']=$host;
		$data['logo_dvdpost']=$logo;
		$data['url']='http://'.$host.'/login_code.php?code=code31&email='.$data['customers_email_address'];
		$data['avantage']=$avantage;
		$data['site_link']='http://'.$host;
		$data['count_movies']=$this->count_movies[$language];
		$data['count_bluray']=$this->count_bluray[$language];
		$data['count_customers']=$this->count_customer[$language];
		return $data;
	}
	function get_count_movies($type='all')
	{
		if($type=='all')
			$sql="select count(products_id) as cpt from products where products_status >-1 and  `products_product_type` = 'movie'";
		else
			$sql="select count(products_id) as cpt from products where products_status >-1 and  `products_product_type` = 'movie' AND products_media = 'blueray'";
		$count_dvd_query=tep_db_query($sql);
		$row=tep_db_fetch_array($count_dvd_query);
		$cpt_catalog=ceil($row['cpt']/1000)*1000;
		$count[1] = number_format($cpt_catalog, 0, '.', ' ');
		$count[2] = number_format($cpt_catalog, 0, '.', '.');
		$count[3] = number_format($cpt_catalog, 0, '.', ',');
		return $count;
	}
	function get_count_customers()
	{
		$sql="SELECT count(1) as cpt FROM `customers` WHERE `customers_abo` =1";
		$count_dvd_query=tep_db_query($sql);
		$row=tep_db_fetch_array($count_dvd_query);
		$cpt_catalog=ceil($row['cpt']/1000)*1000;

		$count[1] = number_format($cpt_catalog, 0, '.', ' ');
		$count[2] = number_format($cpt_catalog, 0, '.', '.');
		$count[3] = number_format($cpt_catalog, 0, '.', ',');
		return $count;
	}
	
}
?>