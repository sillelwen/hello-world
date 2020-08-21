<?php include_once ("../inc/util.php");
include_once ("../inc/quickrun.php");
DEFINE ('EVENTSOURCE', 'system/batchqrstatusget');
if(empty($_GET['param']))
	die();
if(empty(getSetting('enableAutoBatchQRStatusGet', $connect)))
	die('disabled');



//массовое обновление статусов Бегунка по выборке отправлений
function BatchQuickRunStatusesUpdate($res)
{
	if(empty($res) || !$res) return false;
	$idsForStatusUpdate = array();
	if($res->num_rows>0)
	{
		while($row = $res->fetch_assoc())
		{
			if(is_date($row['deliveryDate']) && $quickrunStatusData = getQuickrunDeliveryStatus($row['id'], $row['receptionRegionCode'], $row['deliveryRegionCode'], $row['deliveryDate']))
			{
				storeQuickRunStatus($row['id'], $quickrunStatusData['quickrun_id'], $quickrunStatusData['quickrun_status'], $quickrunStatusData['quickrun_status_id']);
				if($quickrunStatusData['quickrun_status_id'] == QUICKRUN_STATUS_DELIVERED_ID)
					$idsForStatusUpdate[] = $row['id'];
			}
		}
	}
	if(empty($_SESSION['userid'])) $_SESSION['userid'] = SYSTEM_USER_ID;
	return updateStatuses($idsForStatusUpdate, 4); //TODO: убрать хардкод, сделать таблицу сопоставления статусов
}


if(password_verify($_GET['param'], BATCH_STATUSUPDATE_HASH))
{
	$errors = BatchQuickRunStatusesUpdate(getOrderIDsForQuickRunCheck());
	
	if(!empty($errors))
	{
		$statusOK = false;
		$message = "Ошибки в отправлениях: ".implode(', ', $errors);
	}
	elseif($errors===false)
	{
		$statusOK = false;
		$message = "Не удалось получить список отправлений";
	}
	else
	{
		$statusOK = true;
		$message = "Успешно обновлено";
	}
	jobStatusSet('quickRunStatuses', $statusOK, $message);
	die('done');
}
$connect->close();
?>