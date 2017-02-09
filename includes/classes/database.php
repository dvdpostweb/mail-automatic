<?php 
$links=array();
  function tep_db_connect($server = DB_SERVER, $username = DB_SERVER_USERNAME, $password = DB_SERVER_PASSWORD, $database = DB_DATABASE, $link = 'db_link') {
    global $links, $$link;
	$db_link = mysql_connect($server, $username, $password);
    if ($db_link){
		mysql_select_db($database);
		array_push($links,$db_link);
	}
	
	return $db_link;	
  }




  function tep_db_close($link = 'db_link') {
    global $links;
	foreach($links as $connect)
	{
	    mysql_close($connect);
	}
  }

  function tep_db_error($query, $errno, $error) { 
	
	
	$data='<br /><br />session<br />';
	
	$data.='host -> '.$_SERVER['HTTP_HOST'].'<br />';
	$data.='user agent -> '.$_SERVER['HTTP_USER_AGENT'].'<br />';
	$data.='current page -> '.$_SERVER['SCRIPT_FILENAME'].'<br />';
	$data.='referer -> '.$_SERVER['HTTP_REFERER'].'<br />';
	$data.='query string -> '.$_SERVER['QUERY_STRING'].'<br />';
	if(count($_POST)>0){
		foreach($_POST as $db_data  => $value){
			if(is_string($value)){
				$data.='post -> '.$db_data.' -> '.$value.'<br />'; 
			}
		}
	}
	if(!empty($_COOKIE['customers_id']))
		$data.=$_COOKIE['customers_id'].'<br />';
	if(!empty($_COOKIE['email_address']))
		$data.=$_COOKIE['email_address'].'<br />';		
	
	
	
	
//		tep_mail('bugreport@dvdpost.be','bugreport@dvdpost.be','sql error prod','<font color="#000000"><b>' . $errno . ' - ' . $error . '<br><br>' . $query . '<br><br><small><font color="#ff0000">[TEP STOP]</font></small><br><br></b><br />'.$data.'</font>','bugreport@dvdpost.be','bugreport@dvdpost.be');
    //die('<font color="#000000"><b>' . $errno . ' - ' . $error . '<br><br>' . $query . '<br><br><small><font color="#ff0000">[TEP STOP]</font></small><br><br></b></font>');
  }
  function tep_db_error2($query) { 


	$data='<br /><br />session<br />';

	$data.='host -> '.$_SERVER['HTTP_HOST'].'<br />';
	$data.='user agent -> '.$_SERVER['HTTP_USER_AGENT'].'<br />';
	$data.='current page -> '.$_SERVER['SCRIPT_FILENAME'].'<br />';
	$data.='referer -> '.$_SERVER['HTTP_REFERER'].'<br />';
	$data.='query string -> '.$_SERVER['QUERY_STRING'].'<br />';
	if(count($_POST)>0){
		foreach($_POST as $db_data  => $value){
			if(is_string($value)){
				$data.='post -> '.$db_data.' -> '.$value.'<br />'; 
			}
		}
	}
	if(!empty($_COOKIE['customers_id']))
		$data.=$_COOKIE['customers_id'].'<br />';
	if(!empty($_COOKIE['email_address']))
		$data.=$_COOKIE['email_address'].'<br />';		
	/*if(tep_session_is_registered('customer_id')){
		$data.='cust_id'.$customer_id.'<br />';		
	}*/



	//	tep_mail('bugreport@dvdpost.be','bugreport@dvdpost.be','sql verif','<font color="#000000"><b><br><br>' . $query . '<br><br><small><font color="#ff0000">[TEP STOP]</font></small><br><br></b><br />'.$data.'</font>','bugreport@dvdpost.be','bugreport@dvdpost.be');
    //die('<font color="#000000"><b>' . $errno . ' - ' . $error . '<br><br>' . $query . '<br><br><small><font color="#ff0000">[TEP STOP]</font></small><br><br></b></font>');
  }
  function tep_db_query($query, $link = 'db_link',$force_master=false) {
    global $links,$memcache;
    #echo $query;
    #var_dump($links);
    
	$nb_connect=count($links);
	if(1==$nb_connect){
		$connect=$links[0];
	}else{
 		if (0===strpos(strtolower($query),'select') && $force_master ===false){
			if (DB_REDIRECT_ALL_RO === false) {
				$rand=rand(0,($nb_connect-1));
				$connect=$links[$rand];
			} else {
				$connect = $links[1];
			}
		}else{
			$connect=$links[0];
		}
	}
	
	if (stristr(strtolower($query),'select')===false)
	{
		if(stristr($query, 'customers_abo_discount_recurring_to_date') !== FALSE)
			tep_db_error2($query);
		if(stristr($query, 'activation_discount_code_id') !== FALSE)
			tep_db_error2($query);
	}	
    $result = mysql_query($query, $connect) or tep_db_error($query, mysql_errno(), mysql_error());
	
    return $result;
  }
  function tep_begin()
  {
	global $links; 
	$connect=$links[0];

	mysql_query("START TRANSACTION",$connect);
  }
  function tep_commit()
  {
    global $links; 
	$connect=$links[0];
	mysql_query("COMMIT",$connect);
  }
  function tep_rollback()
  {
	global $links; 
	$connect=$links[0];
	mysql_query("ROLLBACK",$connect); 
  }
  function tep_db_perform($table, $data, $action = 'insert', $parameters = '', $link = 'db_link') {
    reset($data);
    if ($action == 'insert') {
      $query = 'insert into ' . $table . ' (';
      while (list($columns, ) = each($data)) {
        $query .= $columns . ', ';
      }
      $query = substr($query, 0, -2) . ') values (';
      reset($data);
      while (list(, $value) = each($data)) {
        switch ((string)$value) {
          case 'now()':
            $query .= 'now(), ';
            break;
          case 'null':
            $query .= 'null, ';
            break;
          default:
            $query .= '\'' . tep_db_input($value) . '\', ';
            break;
        }
      }
      $query = substr($query, 0, -2) . ')';
    } elseif ($action == 'update') {
      $query = 'update ' . $table . ' set ';
      while (list($columns, $value) = each($data)) {
        switch ((string)$value) {
          case 'now()':
            $query .= $columns . ' = now(), ';
            break;
          case 'null':
            $query .= $columns .= ' = null, ';
            break;
          default:
            $query .= $columns . ' = \'' . tep_db_input($value) . '\', ';
            break;
        }
      }
      $query = substr($query, 0, -2) . ' where ' . $parameters;
    }
    return tep_db_query($query);
  }
function tep_db_cache($sqlQuery,$expired_time=0,$random=0) 
{
	global $memcache;
	$memcache_key=md5($sqlQuery);
	if(isset($memcache))
		$rows=$memcache->get($memcache_key);
		//$rows=false;
	else 
	{
		$rows=false;
		$data='sql -> '.$sqlQuery.'<br />';
		$data.='host -> '.$_SERVER['HTTP_HOST'].'<br />';
		$data.='user agent -> '.$_SERVER['HTTP_USER_AGENT'].'<br />';
		$data.='current page -> '.$_SERVER['SCRIPT_FILENAME'].'<br />';
		$data.='referer -> '.$_SERVER['HTTP_REFERER'].'<br />';
		$data.='query string -> '.$_SERVER['QUERY_STRING'].'<br />';
		tep_mail('bugreport@dvdpost.be','bugreport@dvdpost.be','memcache error',$data,'bugreport@dvdpost.be','bugreport@dvdpost.be');
	}
	if($rows!==false && $rows !="")
	
		if($random==0)
			return $rows;
		else
		{
			$new_rows=array();
			$nb_rows=count($rows);
			if($random>$nb_rows)
				return $rows;
			
			$ids=array_rand($rows,$random);
			for($i=0;$i<$random;++$i)
				$new_rows[]=$rows[$ids[$i]];
				
			return $new_rows;
		}
	else
	{
		$rows=array();
		$db_query= tep_db_query($sqlQuery);
		while($row=tep_db_fetch_array($db_query))
		{
			$rows[]=$row;
		}
		if(isset($memcache))
			$memcache->set($memcache_key,$rows,MEMCACHE_COMPRESSED,$expired_time);
		if($random==0)
			return $rows;
		else
		{
			$new_rows=array();
			$nb_rows=count($rows);
			if($random>$nb_rows)
				return $rows;

			$ids=array_rand($rows,$random);
			for($i=0;$i<$random;++$i)
				$new_rows[]=$rows[$ids[$i]];

			return $new_rows;
		}
	}
}  
function tep_db_cache_asso($sqlQuery,$asso='id',$expired_time=0,$random=0) 
{
	global $memcache;
	$memcache_key=md5($sqlQuery);
	if(isset($memcache))
		$rows=$memcache->get($memcache_key);
	else 
	{
		$rows=false;
		$data='sql -> '.$sqlQuery.'<br />';
		$data.='host -> '.$_SERVER['HTTP_HOST'].'<br />';
		$data.='user agent -> '.$_SERVER['HTTP_USER_AGENT'].'<br />';
		$data.='current page -> '.$_SERVER['SCRIPT_FILENAME'].'<br />';
		$data.='referer -> '.$_SERVER['HTTP_REFERER'].'<br />';
		$data.='query string -> '.$_SERVER['QUERY_STRING'].'<br />';
		tep_mail('bugreport@dvdpost.be','bugreport@dvdpost.be','memcache error',$data,'bugreport@dvdpost.be','bugreport@dvdpost.be');
	}
	if($rows!==false)
	
		if($random==0)
			return $rows;
		else
		{
			$new_rows=array();
			$nb_rows=count($rows);
			if($random>$nb_rows)
				return $rows;
			
			$ids=array_rand($rows,$random);
			for($i=0;$i<$random;++$i)
				$new_rows[$ids[$i]]=$rows[$ids[$i]];
			
			return $new_rows;
		}
	else
	{
		$rows=array();
		$db_query= tep_db_query($sqlQuery);
		while($row=tep_db_fetch_array($db_query))
		{
			$rows['id'.$row[$asso]]=$row;
		}
		if(isset($memcache))
			$memcache->set($memcache_key,$rows,MEMCACHE_COMPRESSED,$expired_time);
		if($random==0)
			return $rows;
		else
		{
			$new_rows=array();
			$nb_rows=count($rows);
			if($random>$nb_rows)
				return $rows;
			
			$ids=array_rand($rows,$random);
			for($i=0;$i<$random;++$i)
				$new_rows[$ids[$i]]=$rows[$ids[$i]];
			
			return $new_rows;
		}
	}
}
function tep_db_rand($rows,$random=0)
{
	if($random==0)
		return $rows;
	else
	{
		$new_rows=array();
		$nb_rows=count($rows);
		
		if($random>$nb_rows)
			return $rows;
			
		$ids=array_rand($rows,$random);
		for($i=0;$i<$random;++$i)
			$new_rows[$ids[$i]]=$rows[$ids[$i]];
			
		return $new_rows;
	}
}
  function tep_db_fetch_array($db_query) {
    return mysql_fetch_array($db_query, MYSQL_ASSOC);
  }

  function tep_db_num_rows($db_query) {
    return mysql_num_rows($db_query);
  }

  function tep_db_data_seek($db_query, $row_number) {
    return mysql_data_seek($db_query, $row_number);
  }

  function tep_db_insert_id() {
	global $links;
    return mysql_insert_id($links[0]);
  }

  function tep_db_free_result($db_query) {
    return mysql_free_result($db_query);
  }

  function tep_db_fetch_fields($db_query) {
    return mysql_fetch_field($db_query);
  }

  function tep_db_output($string) {
    return stripslashes($string);
  }

  function tep_db_input($string) {
    return addslashes($string);
  }

  function tep_db_prepare_input($string) {
    if (is_string($string)) {
      return trim(stripslashes($string));
    } elseif (is_array($string)) {
      reset($string);
      while (list($key, $value) = each($string)) {
        $string[$key] = tep_db_prepare_input($value);
      }
      return $string;
    } else {
      return $string;
    }
  }
?>

