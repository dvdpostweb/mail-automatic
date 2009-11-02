<?php


class Delay extends Automatic {
	var $sql;
	var $modif;
	function __construct() {
		$this->sql='SELECT DISTINCT o.orders_id, c.products_id, customers_email_address, c.language_id, o.customers_id, p.products_name
		FROM orders o
		JOIN custserv c ON o.orders_id = c.orders_id and c.custserv_cat_id=3
		JOIN products_description p ON p.products_id = c.products_id
		AND p.language_id = c.language_id
		WHERE orders_status =12
		AND now( ) > DATE_ADD( admindate, INTERVAL 5
		DAY )
		AND admindate > "2009-10-01"
		ORDER BY o.orders_id DESC';
		$this->modif=array('[titre]'=>$row['products_name']);
	}
	public function test($email)
	{
		$mail_id=$this->getMailId();
		if(!empty($mail_id))
		{
			echo "test\n";
			$query=tep_db_query($this->sql);
			$row=tep_db_fetch_array($query);
			$language=$row['language_id'];
			$history_id=$this->mail_history($row['customers_id'],$row['customers_email_address'],$language,$mail_id);
			$this->send_mail( $email, $email, 'noreply@dvdpost.be', 'noreply@dvdpost.be',$language,$this->modif);
			
		}
		else
		{
			echo "error mail id null\n";
		}
	}
	public function execute()
	{
		$mail_id=$this->getMailId();
		if(!empty($mail_id))
		{
			
			$query=tep_db_query($this->sql);
			while($row=tep_db_fetch_array($query))
			{
				$language=$row['language_id'];
				$history_id=$this->mail_history($row['customers_id'],$row['customers_email_address'],$language,$mail_id);
				$modif=array('[titre]'=>$row['products_name']);
				$this->send_mail( $row['customers_email_address'], $row['customers_email_address'], 'noreply@dvdpost.be', 'noreply@dvdpost.be',$language,$this->modif);
			}
			
		}
		else
		{
			echo "error mail id null\n";
		}
	}
}
?>