<?php

class Survey extends Automatic {
	var $sql;
	var $modif;
	function __construct() {
		
	}
	public function execute($email='')
	{
		$mail_id=$this->getMailId();
		
		if(!empty($mail_id))
		{
			
			$this->sql='SELECT cas.id,c.customers_id, customers_language ,entry_country_id,customers_email_address FROM customers_abo_stop cas
			 join customers c on cas.customers_id = c.customers_id 
			 join address_book a on a.customers_id = c.customers_id and a.address_book_id = c.customers_default_address_id
			 LEFT JOIN automatic_emails_history ae ON '.$this->getTable().'.'.$this->getTableId().' = ae.id AND ae.mail_messages_id =  '.$this->getMailId().' AND ae.class_id='.$this->getId().' where date_stop>"2009-09-01" and now( ) > DATE_ADD( date_stop, INTERVAL 10 DAY ) and ae.mail_messages_id IS NULL';
			//AND admindate > "2009-10-01"

			#echo $this->sql."\n";
			
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
					$sql='insert into actions_key (customers_id ,actions_id , `key`) values ('.$row['customers_id'].',"4",uuid())';
					tep_db_query($sql);
					$id=tep_db_insert_id();
					$sql_select='select * from actions_key where id ='.$id;
					$query_select=tep_db_query($sql_select);
					$row_select=tep_db_fetch_array($query_select);
					
					$uniqid=$row_select['key'];
					
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
					$url='http://'.$host.'/actions.php?uniq_id='.$uniqid;
					
					$this->modif=array('$$$logo_dvdpost$$$'=>$logo,'[url]'=>$url);
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