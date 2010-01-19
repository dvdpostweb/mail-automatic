<?php

class Step31 extends Automatic {
	var $sql;
	var $modif;
	CONST PERIOD_DAY='PERIOD_DAY_';
	CONST PERIOD_MONTH='PERIOD_MONTH_';
	CONST PERIOD_YEAR_FR='PERIOD_YEAR_';
	
	CONST AVANTAGE_FREE='AVANTAGE_FREE_';
	
	CONST AVANTAGE_OTHER='AVANTAGE_OTHER_';
	
	CONST STEP31='31';
	CONST STEP32='32';
	CONST ACTION_AFTER_DAYS=3;
	CONST DATE_FROM='2010-01-01';
	
	
	var $avantage_array;
	var $period_day;
	var $period_month;
	var $period_year;
	var $avantage_other;
	
	
	function __construct() {
		
		$sql_langue='SELECT *
		FROM dvdpost_common.translation2
		WHERE `translation_page` = "automatic_mails"';
		$query_lang=tep_db_query($sql_langue);
		
		while($row=tep_db_fetch_array($query_lang))
		{
			$key=$row['translation_key'].'_'.$row['language_id'];
			$this->$key=$row['translation_value'];
			if($this->getDebug()==true){
				echo "\n".$key;
				echo "\n".$this->$key;
			}
			
		}
		
	}
	public function execute($email='')
	{
		
		$mail_id=$this->getMailId();
		$count_movies=$this->get_count_movies();
		
		$count_bluray=$this->get_count_movies('bluray');
		$count_customers=$this->get_count_customers();
		
		
		
		if(!empty($mail_id))
		{
			
			$this->sql='(SELECT c.customers_id,c.customers_id as id, customers_language ,entityid ,customers_email_address,discount_code as code,discount_type as abo_type, discount_value as abo_value, discount_abo_validityto_type as type,discount_abo_validityto_value as value,abo_dvd_credit  ,pa.qty_credit
			 FROM customers c
			 JOIN discount_code dc ON c.`activation_discount_code_id` = dc.discount_code_id
			 JOIN products_abo pa ON pa.products_id = `customers_abo_type`
			 LEFT JOIN automatic_emails_history ae ON '.$this->getTable().'.'.$this->getTableId().' = ae.id AND ae.mail_messages_id =  '.$this->getMailId().' AND ae.class_id='.$this->getId().' where customers_info_date_account_created> "'.self::DATE_FROM.'" and date(now()) > DATE_ADD( customers_info_date_account_created, INTERVAL '.self::ACTION_AFTER_DAYS.' DAY ) and (customers_registration_step='.self::STEP31.' or customers_registration_step='.self::STEP32.') AND `activation_discount_code_type` = "d" and ae.mail_messages_id IS NULL
						)union(
			SELECT c.customers_id,c.customers_id as id, customers_language,entityid, customers_email_address,activation_code as code,1 as abo_type, 0 as abo_value, validity_type as type,validity_value as value,abo_dvd_credit ,pa.qty_credit
			 FROM customers c
			 JOIN activation_code dc ON c.`activation_discount_code_id` = dc.activation_id
			 JOIN products_abo pa ON pa.products_id = `customers_abo_type`
			 LEFT JOIN automatic_emails_history ae ON '.$this->getTable().'.'.$this->getTableId().' = ae.id AND ae.mail_messages_id =  '.$this->getMailId().' AND ae.class_id='.$this->getId().' where customers_info_date_account_created> "'.self::DATE_FROM.'" and date(now()) > DATE_ADD( customers_info_date_account_created, INTERVAL '.self::ACTION_AFTER_DAYS.' DAY ) and (customers_registration_step='.self::STEP31.' or customers_registration_step='.self::STEP32.') AND `activation_discount_code_type` = "a" and  ae.mail_messages_id IS NULL )';
			if($this->getDebug()==true)
			{
				echo "\n".$this->sql."\n\n";
			}
			$query=tep_db_query($this->sql);
			
			while($row=tep_db_fetch_array($query))
			{
				$language=$row['customers_language'];
				
				if(empty($email))
				{
					
					$history_id=$this->mail_history($row['customers_id'],$row['customers_email_address'],$language,$mail_id);
					$status=$this->history($row['id']);
					
				}
				else
				{
					$history_id=0;
					$status=true;
				}
				if($status==true){
					switch(strtolower($row['entry_country_id']))
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
				$url='http://'.$host.'/login_code.php?code=code31&email='.$row['customers_email_address'];
				if($row['abo_value']==0)
				{	
					if($row['abo_dvd_credit']>0)	
						$credit=$row['abo_dvd_credit'];
					else
						$credit=$row['qty_credit'];
					switch($row['type'])
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
					
					$period='<strong>'.$row['value'].' '.$this->$key.'</strong>';
					$key=self::AVANTAGE_FREE.$language;
					$data=$this->$key;
					$avantage=sprintf($data,$credit,$period);
				}
				else
				{
					$key=self::AVANTAGE_OTHER.$language;
					$avantage=$this->$key;
				}
				
			$this->modif=array('$$$logo_dvdpost$$$'=>$logo,'$$$customers_name$$$'=>$row['customers_name'],'$$$promotion$$$'=>$row['code'],'$$$avantage$$$'=>$avantage,'$$$count_movies$$$'=>$count_movies[$language],'$$$count_bluray$$$'=>$count_bluray[$language],'$$$count_customers$$$'=>$count_customers[$language],'$$$url$$$'=>$url,'$$$mail_messages_sent_history_id$$$'=>$history_id,'$$$site_link$$$'=>'http://'.$host,'$$$customers_email$$$'=>$row['customers_email_address']);
			if($row['abo_value']>0)
			{		
				$this->modif['images/newsletters/relance_01_10/comedie_fr.jpg']='images/www3/languages/french/images/login_code/step31.jpg';
				$this->modif['images/newsletters/relance_01_10/comedie_en.jpg']='images/www3/languages/english/images/login_code/step31.jpg';
				$this->modif['images/newsletters/relance_01_10/comedie_nl.jpg']='images/www3/languages/dutch/images/login_code/step31.jpg';
			}
					if(empty($email))
					{
						$this->send_mail( $row['customers_email_address'], $row['customers_email_address'], 'dvdpost@dvdpost.be', 'dvdpost@dvdpost.be',$language,$this->modif);
					}
					else
					{
						$this->send_mail( $email, $email, 'dvdpost@dvdpost.be', 'dvdpost@dvdpost.be',$language,$this->modif);

						
						break;
					}
				}
				else
				{
					echo "error: history (rollback) \n";
					tep_rollback();
				}
			}
			
		}
		else
		{
			echo "error mail id null\n";
		}
	}
}
?>