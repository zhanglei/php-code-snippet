<?php
session_start();
if(!isset($_POST['client'])) {
	$requst_url = parse_url($redirect);
	$_POST['client'] =  $requst_url['host'];
}
$conn = mysqli_connect('127.0.0.1','root','','test');
if ($conn->connect_errno) {
    printf("Connect failed: %s\n", $mysqli->connect_error);
    exit();
} 
$res = $conn->query('select * from user_2 where name = "' . $_POST['username'] . '" AND password="' . md5($_POST['password']) . '"');
if($conn->error) {
	echo $conn->error;
} else if($res) {
	$rows = $res->fetch_assoc();
	$ip = get_ip();
	$expire = time()+3600;
	$ticket = md5($rows['id'] . $rows['password'] . md5(rand(0,999)));
	setcookie('TICKET',$ticket,$expire);
	$conn->query('insert into user_ticket(uid,ip,client,ticket,expire) values(' . $rows['id'] . ',"' . $ip . '","' . $_POST['client'] . '","' . $ticket . '",' . $expire . ')');
	echo 'SUCCESS<BR/>';
	if(isset($_POST['redirect'])) {
		$redirect = $_POST['redirect'];
	} else {
		$redirect = $_SERVER['HTTP_REFERER'];
	}
	if(isset($redirect)) {
		/*
		if(strpos($redirect,'?') !== FALSE) {
			$redirect .= '&ticket=' . $ticket;
		} else {
			$redirect .= '?ticket=' . $ticket;
		}
		*/
		header('Location:' . $redirect);
	}
} else {
	echo 'ERROR';
}

function get_ip() { 
	if (isset($_SERVER["HTTP_CLIENT_IP"]))
		$ip = $_SERVER["HTTP_CLIENT_IP"]; 
	else if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
		$ip = $_SERVER["HTTP_X_FORWARDED_FOR"]; 
	else if ($_SERVER["REMOTE_ADDR"]) 
		$ip = $_SERVER["REMOTE_ADDR"]; 
	else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'])
		$ip = $_SERVER['REMOTE_ADDR']; 
	else 
		$ip = NULL; 
	return $ip; 
}