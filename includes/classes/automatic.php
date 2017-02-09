<?php


class Automatic {
	
	private $db = null; //reference to our DB connection
	private $db_config = array(); //all available DB configs (e.g. test,development, production)
	private $ENV = "test"; //default (can also be one 'test', 'production')
	private $debug = false; //default (can also be one 'test', 'production')
	
	private $verbose=false;
	private $task_options;
	private $script;
	
	private $mail_id;
	private $mail_content;
	private $id;
	private $name;
	private $email_test;
	private $table_select;
	private $id_select;
	
	

	
	function __construct($db, $argv) {
		$this->db_config=$db;
		$this->parse_args($argv);
		tep_db_connect($this->db_config[$this->ENV]['host'], $this->db_config[$this->ENV]['user'] ,$this->db_config[$this->ENV]['password'], $this->db_config[$this->ENV]['database']);
		
	}
	
	public function get_count_movies($type='all')
	{
		if($type=='all')
			$sql="select count(products_id) as cpt from products where products_status >-1 and  `products_product_type` = 'movie'";
		else
			$sql="select count(products_id) as cpt from products where products_status >-1 and  `products_product_type` = 'movie' AND products_media = 'blueray'";
		$count_dvd_query=tep_db_query($sql);
		$row=tep_db_fetch_array($count_dvd_query);
		$cpt_catalog=ceil($row['cpt']/1000)*1000;
		if($type=='all')
			$cpt_catalog=25000;
		
			$cpt_movies[1] = number_format($cpt_catalog, 0, '.', ' ');
			$cpt_movies[2] = number_format($cpt_catalog, 0, '.', '.');
			$cpt_movies[3] = number_format($cpt_catalog, 0, '.', ',');
			
		return $cpt_movies;
	}
	public function get_count_customers()
	{
		$sql="SELECT count(1) as cpt FROM `customers` WHERE `customers_abo` =1";
		$count_dvd_query=tep_db_query($sql);
		$row=tep_db_fetch_array($count_dvd_query);
		$cpt_catalog=ceil($row['cpt']/1000)*1000;
		
			$cpt_movies[1] = number_format($cpt_catalog, 0, '.', ' ');
			$cpt_movies[2] = number_format($cpt_catalog, 0, '.', '.');
			$cpt_movies[3] = number_format($cpt_catalog, 0, '.', ',');
			
		return $cpt_movies;
	}
	public function execute(){
		$sql='SELECT * FROM automatic_emails where status="active" and date_format(now(),"%H")=exe_time';
		$query=tep_db_query($sql);
		while($row=tep_db_fetch_array($query))
		{
			
			if(is_file('./script/'.$row['script_name']))
			{
				include_once('./script/'.$row['script_name']);
				$class=explode('.',$row['script_name']);
				$class_name=$class[0];
				$depart=$this->timer();
		 
				$this->script = new $class_name();
				$this->script->setId($row['id']);
				
				$this->script->verbose=$this->verbose;
				$this->script->debug=$this->debug;
				
				$this->script->name=$row['name'];
				$this->script->setTable($row['table_select']);
				$this->script->setTableId($row['id_select']);
				$this->script->setMailId($row['mail_id']);
				$this->script->initialize();
				if(!empty($this->email_test))
					$this->script->execute($this->email_test);
				else
					$this->script->execute();
				$this->script->finish();
				$fin=$this->timer();
				 $delai=number_format($fin - $depart,7);
				 if($this->verbose==true)
					echo "temps d'execution: ",$delai," secondes.\n"; 
		 	}
			else
			{
				echo "le script ".$row['script_name']." est manquant dans le repertoire script\n";
			}
		}
	}
	public function finish()
	{
		if($this->verbose==true)
		{
			echo "script -> ".$this->name." mail envoyé -> ".$this->getCount()."\n";
		}
		$sql='update automatic_emails set count_mail='.$this->getCount().' where id= '.$this->getId();
		tep_db_query($sql);
	}
	public function setCount()
	{
		$this->count_mails++;
	}
	public function getCount()
	{
		return $this->count_mails;
	}
	public function setId($id)
	{
		$this->id=$id;
	}
	public function getId()
	{
		return $this->id;
	}
	public function setTableId($id)
	{
		$this->id_select=$id;
	}
	public function getTableId()
	{
		return $this->id_select;
	}
	public function setTable($id)
	{
		$this->table_select=$id;
	}
	public function getTable()
	{
		return $this->table_select;
	}
	public function setMailId($id)
	{
		$this->mail_id=$id;
	}
	public function getMailId()
	{
		return $this->mail_id;
	}
	public function getDebug()
	{
		return $this->debug;
	}
	public function modif_attributes($html,$modif)
	{
		foreach($modif as $key => $value)
		{
			$html=str_replace($key,$value,$html);
		}
		return $html;
		
	}
	private  function timer()
	 {
	 $time=explode(' ',microtime());
	 return $time[1] + $time[2];
	 }
	
	public function send_mail($to_name, $to_email_address,  $from_email_name, $from_email_address,$language,$modif){
			$email_subject=$this->mail_content[$language]['messages_subject'];
 			$email_text=$this->mail_content[$language]['messages_html'];
			$email_text=$this->modif_attributes($email_text,$modif);
		    $message = new email(array('X-Mailer: osC mailer'));
		    $text = strip_tags($email_text);
		    $message->add_html($email_text, $text);
		    $message->build_message();
			
		    $status=$message->send($to_name, $to_email_address, $from_email_name, $from_email_address, $email_subject);
			if($status==true)
			{
		    	$this->setCount();
				tep_commit();
			}
			else
			{
				echo "error : mail (rollback)\n";
				tep_rollback();
			}
	}
	public function history($id)
	{
		$sql='insert into automatic_emails_history (id, mail_messages_id,class_id) values ('.$id.','.$this->getMailId().','.$this->getId().')';
		return	tep_db_query($sql);
	}
	public function initialize()
	{
		$mail_id=$this->getMailId();
		var_dump($mail_id);
		if(!empty($mail_id))
		{
			$sql='SELECT * FROM mail_messages where  mail_messages_id='.$mail_id;
			echo $sql;
			$query=tep_db_query($sql);
			while($row=tep_db_fetch_array($query))	
			{
				$mail[$row['language_id']]=$row;
			}
			$this->mail_content=$mail;
		}
		else
		{
			echo "error initiliaze \n";
		}
	}
		
	public function mail_history($customers_id,$customers_email,$languages_id,$mail_id)
	{
		tep_begin();
		$sql_insert="INSERT INTO `mail_messages_sent_history` (`mail_messages_sent_history_id` ,`date` ,`customers_id` ,`mail_messages_id`,`language_id` ,	`mail_opened` ,	`mail_opened_date` ,`customers_email_address`)
		VALUES (NULL , now(), ".$customers_id.", '".$mail_id."', $languages_id, '0', NULL , '".$customers_email."'	);";
		
		tep_db_query($sql_insert);
		$mail_hystory_id=tep_db_insert_id();
		return $mail_hystory_id;
	}
	private function parse_args($argv) {
		
		$num_args = count($argv);
		$options = array();
		for($i = 1; $i < $num_args;$i++) {
			$arg = $argv[$i];
			if(strpos($arg, '=') !== FALSE) {
				list($key, $value) = split("=", $arg);
				$options[$key] = $value;
			if($key == 'ENV') {
				$this->ENV = $value;
			}
			if($key == 'DEBUG') {
				$this->debug = (($value=="1")?true:false);
			}
			if($key == 'VERBOSE') {
				$this->verbose =(($value=="1")?true:false);
			}
			if($key == 'EMAIL') {
				$this->email_test =$value;
			}
		}
		$this->task_options = $options;
		}		
	}	
	
}
?>