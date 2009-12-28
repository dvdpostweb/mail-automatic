<?php

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
			
			$this->sql='SELECT o.orders_id , cs.products_id,o.customers_name,o.customers_street_address,o.customers_city,o.customers_postcode, date_format(o.date_purchased,"%d/%m/%Y") as date_purchased ,o.customers_email_address, c.customers_language , o.customers_id, p.products_name,o.customers_country
										FROM orders o
										JOIN custserv cs ON o.orders_id = cs.orders_id and cs.custserv_cat_id=3
										join customers c on c.customers_id = o.customers_id
										JOIN products_description p ON p.products_id = cs.products_id and p.language_id = c.customers_language
										
										LEFT JOIN automatic_emails_history ae ON '.$this->getTable().'.'.$this->getTableId().' = ae.id AND ae.mail_messages_id =  '.$this->getMailId().' AND ae.class_id='.$this->getId().' WHERE orders_status =12 and ae.mail_messages_id IS NULL AND admindate > "2009-12-01"
							AND now( ) > DATE_ADD( admindate, INTERVAL 3 DAY )  GROUP BY o.orders_id 	ORDER BY o.orders_id DESC
			';
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
					$row['customers_id']=206183;
				}
				if($status==true){
					//$actions=new actions();
					//$uniqid=$actions->createKey($row['customers_id'],3,$row['orders_id']);
					$sql='insert into actions_key (customers_id ,actions_id , `key`,ref_id) values ('.$row['customers_id'].',"3",uuid(),'.$row['orders_id'].')';
					$status=tep_db_query($sql);
					if($status)
					{
						$id=tep_db_insert_id();
						$sql_select='select * from actions_key where id ='.$id;
						$query_select=tep_db_query($sql_select,'db_config',true);
						$row_select=tep_db_fetch_array($query_select);

						$uniqid=$row_select['key'];
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
					
					
					
						$custserv_message_query = tep_db_query("select * from custserv_auto_answer where language_id = '" . $language. "' and custserv_auto_answer_id = 21 ");  
						$custserv_message = tep_db_fetch_array($custserv_message_query);
						$modif=array('[customers_name]'=>$row['customers_name'],'[title]'=>$row['products_name'],'[mail]'=>$row['customers_email_address']);
						$html=$this->modif_attributes($custserv_message['messages_html'],$modif);
						$sql="INSERT INTO custserv (customers_id , language_id , custserv_cat_id , customer_date , subject,message,adminby ,admindate) VALUES ('" . $row['customers_id']. "', '" . $language . "', '6', now(), '" .addslashes($custserv_message['custserv_auto_answer_comment'])  ."','" . addslashes($html)."',99,now())" ;
					    $status=tep_db_query($sql);
						if(!$status)
						{
							echo "error: custserv (rollback) \n";
							tep_rollback();
						
						}
						else
						{
							$this->modif=array('[name]'=>$row['customers_name'],'[host]'=>$host,'[address]'=>$row['customers_street_address'].' '.$row['customers_postcode'].' '.$row['customers_city'],'[date]'=>$row['date_purchased'],'[url]'=>$url,'[titel]'=>$row['products_name']);
							if(empty($email))
							{
								$this->send_mail( $row['customers_email_address'], $row['customers_email_address'], 'delay@dvdpost.be', 'delay@dvdpost.be',$language,$this->modif);
							}
							else
							{
								$this->send_mail( $email, $email, 'delay@dvdpost.be', 'delay@dvdpost.be',$language,$this->modif);
								break;
							}
						}
					}
					else
					{
						echo "error: action (rollback) \n";
						tep_rollback();
					}
				}else{
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