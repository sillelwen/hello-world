<?php include_once ("../inc/util.php");
DEFINE ('EVENTSOURCE', 'system/batchclosedate');
if(empty($_GET['param']))
	die();
if(empty(getSetting('enableAutoBatchCloseDate', $connect)))
	die('disabled');
if(password_verify($_GET['param'], BATCH_CLOSE_HASH))
{
	BatchCloseDate(date('Y-m-d'),SYSTEM_USER_ID);
	die('done');
}
$connect->close();
?>