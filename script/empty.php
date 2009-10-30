<?php


class Empty extends Automatic {
	private $mail=array();

	function __construct() {
	}
	public function execute()
	{
		$mail_id=$this->getMailId();
		if(!empty($mail_id))
		{
			$sql='';
			$query=tep_db_query($sql);
			while($row=tep_db_fetch_array($query))
			{
				$language=$row['customers_language'];
				$history_id=$this->mail_history($row['customers_id'],$row['customers_email_address'],$language,$mail_id);
				$modif=array();
				$this->send_mail( $row['customers_email_address'], $row['customers_email_address'], 'noreply@dvdpost.be', 'noreply@dvdpost.be',$language,$modif);
			}
			
		}
		else
		{
			echo "error mail id null\n";
		}
	}
}
?>