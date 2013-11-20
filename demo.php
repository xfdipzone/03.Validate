<?
	require_once('Validate.class.php');

	$obj = new Validate();
	
	$content = '123456';
	
	$obj->checkData($content,'empty');
?>