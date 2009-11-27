<?php
$root=((!empty($_SERVER['DOCUMENT_ROOT']))?$_SERVER['DOCUMENT_ROOT']:$_SERVER['PWD']);
include($root.'/includes/classes/actions.php');

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
			
			$this->sql='SELECT o.orders_id , cs.products_id, o.customers_email_address, c.customers_language , o.customers_id, p.products_name,o.customers_country
										FROM orders o
										JOIN custserv cs ON o.orders_id = cs.orders_id and cs.custserv_cat_id=3
										join customers c on c.customers_id = o.customers_id
										JOIN products_description p ON p.products_id = cs.products_id and p.language_id = c.customers_id
										
										LEFT JOIN automatic_emails_history ae ON '.$this->getTable().'.'.$this->getTableId().' = ae.id AND ae.mail_messages_id =  '.$this->getMailId().' AND ae.class_id='.$this->getId().' WHERE orders_status =12 and ae.mail_messages_id IS NULL
							AND now( ) > DATE_ADD( admindate, INTERVAL 5 DAY )  GROUP BY o.orders_id	ORDER BY o.orders_id DESC
			limit 1';
			//AND admindate > "2009-10-01"

			#echo $this->sql;
			
			$query=tep_db_query($this->sql);
			
			while($row=tep_db_fetch_array($query))
			{
				$language=$row['customers_language'];
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
					switch(strtolower($row['customers_country']))
					{
						case 'nederlands':
							$host='www.dvdpost.nl';
						break;
						case 21:
						default:
							$host='www.dvdpost.be';
						
					}
					$url='http://'.$host.'/actions.php?uniq_id='.$uniqid;
					$this->modif=array('[name]'=>$row['customers_name'],'[address]'=>$row['customers_street_address'].' '.$row['customers_city'].' '.$row['customers_postcode'],'[date]'=>$row['date_purcahsed'],'[url]'=>$url,'[titel]'=>$row['products_name']);
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