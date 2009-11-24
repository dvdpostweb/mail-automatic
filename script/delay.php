<?php
include('./classes/actions.php');

class Delay extends Automatic {
	var $sql;
	var $modif;
	function __construct() {
		
	}
	public function execute($email='')
	{
		$mail_id=$this->getMailId();
		
		if(!empty($mail_id))
		{
			
			$this->sql='SELECT DISTINCT o.orders_id , c.products_id, o.customers_email_address, c.language_id, o.customers_id, p.products_name
							FROM orders o
							JOIN custserv c ON o.orders_id = c.orders_id and c.custserv_cat_id=3
							JOIN products_description p ON p.products_id = c.products_id and p.language_id = 1
							LEFT JOIN automatic_emails_history a ON '.$this->getTable().'.'.$this->getTableId().' = a.id AND a.mail_messages_id =  '.$this->getMailId().'
							WHERE orders_status =12 and a.mail_messages_id IS NULL
							AND now( ) > DATE_ADD( admindate, INTERVAL 5 DAY ) 	ORDER BY o.orders_id DESC
			limit 1';
			//AND admindate > "2009-10-01"

			//echo $this->sql;
			
			$query=tep_db_query($this->sql);
			
			while($row=tep_db_fetch_array($query))
			{
				$language=$row['language_id'];
				if(empty($email))
				{
					
					$history_id=$this->mail_history($row['customers_id'],$row['customers_email_address'],$language,$mail_id);
					$status=$this->history($row['orders_id']);
					
				}
				else
				{
					$history_id=0;
					$status=true;
				}
				if($status==true){
					$actions=new actions();
					$uniqid=$actions->createKey($row['customers_id'],3,$row['orders_id']);
					$url='http://'.$host.'/actions.php?uniq_id='.$uniqid;
					$this->modif=array('[titre]'=>$row['products_name'],'[url]'=>$url);
					if(empty($email))
					{
						$this->send_mail( $row['customers_email_address'], $row['customers_email_address'], 'noreply@dvdpost.be', 'noreply@dvdpost.be',$language,$this->modif);
					}
					else
					{
						$this->send_mail( $email, $email, 'noreply@dvdpost.be', 'noreply@dvdpost.be',$language,$this->modif);
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