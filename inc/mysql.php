<?php

//Подключаемся к БД Хост, Имя пользователя MySQL, его пароль, имя нашей базы
$connect = ($_SERVER["HTTP_HOST"] == 'lk') ? new mysqli("localhost", "lkadmin", "111111", "lk" ) : 
($_SERVER["HTTP_HOST"] == 'testlk.de-express.ru' ?  new mysqli("localhost", "u0941507_default", "43_!7aPt", "u0941507_test" ) : new mysqli("localhost", "u0941507_default", "43_!7aPt", "u0941507_lk" ));
//$connect = new mysqli("192.168.137.100", "u1103107_de_lk", "XhQCKqFXN1rt21", "db1103107_de_lk" );

//Кодировка данных получаемых из базы
$connect->query("SET NAMES 'utf8' ");


function query($query)
{
	global $connect;
	return $connect->query($query);
}
function affected_rows ()
{
	global $connect;
	return $connect->affected_rows;
}
function insert_id()
{
	global $connect;
	return $connect->insert_id;
}

function prepStr($strval)
{
	global $connect;
	return is_null($strval)? null : $connect->real_escape_string($strval);
}

function prepNum($numval)
{
	return floatval($numval);
}
function prepDate($dateval)
{
	return (is_date($dateval) ? $dateval : '');
}

//Получение значения системной настройки по имени
function getSetting(string $name, $connect)
{
    $res = query("SELECT value FROM settings WHERE name='$name'");
	if($row = $res->fetch_assoc())
	{
		mysqli_free_result($res);
		return $row['value'];
	}
	else
		return '';
}

//Получение списка системных настроек
function getSettings($connect)
{
	return query("SELECT name, description, value FROM settings ORDER BY name");
}
//Запись системной настройки
function setSetting($connect, string $name, string $value)
{
	$value = prepStr($value);
	$query = "UPDATE settings SET value='$value' WHERE name='$name'";
	query($query);
	if(affected_rows()>0)
	{
		logevent($connect, EVENT_TYPE_INFO, 'setSetting', "Установлено новое значение системной настройки $name: $value", null, null);
		return true;
	}
	return false;
}

//Получение значения пользовательской настройки текущего пользователя по имени
function getUserSetting(string $name, $connect, int $default=NULL)
{
    $res = query("SELECT value FROM usersettings WHERE name='$name' AND userid=".$_SESSION['userid']);
	if($row = $res->fetch_assoc())
	{
		mysqli_free_result($res);
		return $row['value'];
	}
	else
		return $default;
}
//Получение значения пользовательской настройки другого пользователя по имени
function getOtherUserSetting(string $name, $connect, int $userid, int $default=NULL)
{
	if(is_null($default)) $default = 0;
	$userid = prepNum($userid);
    $res = query("SELECT value FROM usersettings WHERE name='$name' AND userid=".$userid);
	if($row = $res->fetch_assoc())
	{
		mysqli_free_result($res);
		return $row['value'];
	}
	else
		return $default;
}
//Получение значения пользовательской настройки-строки текущего пользователя по имени
function getUserSettingStr(string $name, $connect, string $default=NULL)
{
	if(is_null($default)) $default = '';
    $res = query("SELECT value_str FROM usersettings WHERE name='$name' AND userid=".$_SESSION['userid']);
	if($row = $res->fetch_assoc())
	{
		mysqli_free_result($res);
		return htmlspecialchars($row['value_str']);
	}
	else
		return $default;
}
//Получение значения пользовательской настройки-строки другого пользователя по имени
function getOtherUserSettingStr(string $name, $connect, int $userid, string $default=NULL)
{
	if(is_null($default)) $default = '';
	$userid = prepNum($userid);
    $res = query("SELECT value_str FROM usersettings WHERE name='$name' AND userid=".$userid);
	if($row = $res->fetch_assoc())
	{
		mysqli_free_result($res);
		return htmlspecialchars($row['value_str']);
	}
	else
		return $default;
}

//Запись пользовательской настройки 
function setUserSettingValue($connect, int $userid, string $settingname, int $value)
{
	$value = prepNum($value);
	$query = "REPLACE usersettings (name, value, userid) VALUES ('$settingname', $value, $userid)";
	return query($query);
}
//Запись пользовательской настройки-строки
function setUserSettingValueStr($connect, int $userid, string $settingname, string $value)
{
	$value = prepStr($value);
	if($value=='')
		$query = "DELETE FROM usersettings WHERE name='$settingname' AND userid=$userid";
	else
		$query = "REPLACE usersettings (name, value, value_str, userid) VALUES ('$settingname', -1, '$value', $userid)";
	return query($query);
}

//получение данных пользователя по логину
function getUserByLogin($connect, string $login)
{
	$login = prepStr($login);
	return query("SELECT * FROM users WHERE login='$login' LIMIT 1");
}

//получение количества пользователей по логину (проверка наличия)
function countUserByLogin($connect, string $login)
{
	$login = prepStr($login);
	return query("SELECT count(*) as count FROM users WHERE login='$login'");
}

//получение данных пользователя по логину и почте
function getUserByLoginAndEmail($connect, string $login, string $email)
{
	$login = prepStr($login);
	$email = prepStr($email);
	return query("SELECT * FROM users WHERE login='$login' and email='$email' LIMIT 1");
}

//получение логина пользователя по id
function getUserByID($connect, string $userid)
{
	$userid = prepNum($userid);
	return query("SELECT login, email, companyName FROM users WHERE id = $userid");
}

//получение название фирмы по по id
function getCompanyNameByID($connect, string $userid)
{
	$userid = prepNum($userid);
	if($res = query("SELECT login, email, companyName FROM users WHERE id = $userid"))
	{
		if($res->num_rows == 0)
			return null;
		else
		{
			$row= $res->fetch_assoc();
			mysqli_free_result($res);
			return $row['companyName'];
		}
	}
	else
		return null;
}

//Проверка наличия права у заданного пользователя по ID права
function hasRight(int $userid, int $rightid, $connect)
{
    $res = query("SELECT hasRight($userid, $rightid) as hasright");
	$row = $res->fetch_assoc();
    return $row['hasright'];
}


//Проверка наличия права у заданного пользователя по имени
function hasNamedRight(int $userid, string $rightname, $connect)
{
	$query = "SELECT hasNamedRight($userid, '$rightname') as hasright";
    $res = query($query);
	$row = $res->fetch_assoc();
	mysqli_free_result($res);
    return $row['hasright'];
}

//добавление нового пользователя
function addNewUser (string $login, string $email, string $companyName, int $contractType, string $password, $connect)
{
	$login = prepStr($login);
	$email = prepStr($email);
	$companyName = prepStr($companyName);
	$contractType = prepNum($contractType);
	
	$query = "INSERT INTO users (login, email, companyName, password_hash) VALUES ('$login', '$email', '$companyName', '".password_hash($password, PASSWORD_DEFAULT)."')";
	if(query($query)===TRUE)
	{
		if($contractType==0)
			$rights="'login', 'admin', 'setstatus', 'viewregisters'";
		else
			$rights="'login', 'addregister', 'viewregisters'";
		$query = "INSERT INTO userrights (userid, rightid) SELECT users.id, rights.id FROM users, rights WHERE users.login = '$login' AND rights.name IN ($rights)";
		if (query($query))
		{
			$query = "INSERT INTO usersettings (name, value, userid) SELECT 'contractType', $contractType, users.id FROM users WHERE users.login = '$login'";
			return query($query)===TRUE;
		}
		else
			return FALSE;
	}
	else
		return FALSE;
}

//добавление в базу записи о загруженном пользователем файле
function addFileToUser (int $userid, string $filename, $connect)
{
	$query = "INSERT INTO userfiles (userid, filename, creationDate) VALUES ('$userid', '$filename', NOW())";
    return (query($query)===TRUE);
}

//получение доступных для выбора значений справочника
function getAvailableValues($connect, string $tablename)
{
	return query("SELECT id, value FROM $tablename WHERE enabled=TRUE");
}

//получение доступных для выбора значений справочника типов оплаты
function getAvailablePaymentTypes($connect, int $userid=NULL)
{
	if(is_null($userid)) $userid = $_SESSION['userid'];
	$query = "SELECT paymenttypes.id, paymenttypes.value
	FROM
	usersettings
	INNER JOIN
	contractpaymenttypes
	ON contractpaymenttypes.contracttype = usersettings.value AND contractpaymenttypes.enabled = 1
	INNER JOIN paymenttypes
	ON paymenttypes.id = contractpaymenttypes.paymenttype AND paymenttypes.enabled = 1
	WHERE usersettings.name='contractTYpe'
	AND usersettings.userid = $userid";
	return query($query);
}

//получение доступных для выбора значений справочника регионов 
function getAvailableRegions($connect, bool $reception=NULL, bool $delivery=NULL)
{
	$where = '';
	if ($reception) $where .= ' AND reception=1';
	if ($delivery) $where .= ' AND delivery=1';
	return query("SELECT id, value FROM regions WHERE enabled=TRUE$where");
}

//получение списка значений enum
function getEnumValues($connect, string $tablename, string $fieldname)
{
	$res = query("SHOW COLUMNS FROM $tablename WHERE field = '$fieldname'");
	$row = $res->fetch_assoc();
	return explode("','",preg_replace("/(enum|set)\('(.+?)'\)/","\\2",$row['Type']));
}

//получение значения из справочника по ID
function getValueName(string $tablename, $connect, $id)
{
	if($res = query("SELECT id, value FROM $tablename WHERE id='$id'"))
	{
		$row = $res->fetch_assoc();
		return htmlspecialchars($row['value']);
	}
	else
		return '';
}
//получение массива значений из справочника по ID
function getValueNames(string $tablename, $connect, $ids)
{
	if(!is_array($ids)) $ids = explode(';', $ids);
	$result = array();
	$query = "SELECT id, value FROM $tablename WHERE id IN (".implode(', ', $ids).")";
	
	if($res = query($query))
	{
		while($row = $res->fetch_assoc())
			$result[$row['id']] = htmlspecialchars($row['value']);
	}
	return $result;
}

//запись события в журнал
function logevent($connect, $type, string $source, string $message, int $objectid=null, string $details=null)
{
	$objectid = (is_null($objectid) || !is_numeric($objectid)) ? 'NULL' : $objectid;
	$message=prepStr($message); $details=prepStr($details);
	$message=mb_substr($message, 0, 140);
	$query = "CALL logevent('$type', '$source', '".$_SESSION['userid']."', '".getUserIpAddr()."', '$message', $objectid, '$details')";
	//die($query);
	return query($query);
}

//обновление пользовательской настройки количества выводимых строк на страницу
function updateUserSettingRows(int $rows, $connect)
{
	$rows = prepNum($rows);
	if($_SESSION['rows']!=$rows)
	{
		if($res = setUserSettingValue($connect, $_SESSION['userid'], 'rows', $rows))//query("CALL setUserSetting('rows', $rows, ".$_SESSION['userid'].')'))
			$_SESSION['rows'] = $rows;
	}
}

//получение строк реестра по списку id только для текущего пользователя
function getUserRegisterRowsByIDs($connect, array $rowIDs)
{
	$query = "SELECT register.id, waybill, creationDate, receptionDate, IFNULL(deliveryDate,
					concat(DATE(receptionDate), ' - ', deliveryterms.value)) as deliveryDate, weight, places, CONCAT(regionD.value, ',\n', register.deliveryCity, CASE WHEN register.deliveryCity <> '' THEN ', ' ELSE '' END, register.address) as address,
					sum, orderstatuses.value as status, addressee, contactFIO, contactPhone1, contactPhone2,
					concat(LPAD(timeSpanFrom DIV 2, 2, '0'), ':', LPAD(MOD(timeSpanFrom, 2)*30, 2, '0'), ' - ', LPAD(timeSpanTo DIV 2, 2, '0'), ':', LPAD(MOD(timeSpanTo, 2)*30, 2, '0')) as deliveryInterval,
					CONCAT_WS(' &#215; ', dimension1, dimension2, dimension3) as dimensions,
					regionR.code as receptionRegionCode, regionD.code as deliveryRegionCode, receptionContactFIO,
					regionR.value as receptionRegion, regionD.value as deliveryRegion, register.address as deliveryAddress, deliveryCity, receptionCity, receptionAddress,
					CONCAT(regionR.code, regionD.code, '&ndash;', LPAD(register.id, 7, '0')) as rowid,
					paymenttypes.value as paymentType, note, simple
		FROM register
		LEFT JOIN orderstatuses ON register.status = orderstatuses.id
		LEFT JOIN deliveryterms ON register.deliveryDay = deliveryterms.id
		LEFT JOIN regions as regionR ON regionR.id = receptionRegion
		LEFT JOIN regions as regionD ON regionD.id = deliveryRegion
		LEFT JOIN paymenttypes ON register.paymentTypeID = paymenttypes.id
		LEFT JOIN usersettings ON usersettings.userid = ".$_SESSION['userid']." AND usersettings.name = 'contractType'
		LEFT JOIN waybilltype ON waybilltype.contractType = usersettings.value AND waybilltype.paymentType = register.paymentTypeID
		WHERE register.id IN (".implode(',', $rowIDs).')
		AND register.userid='.$_SESSION['userid'];
	return query($query);
}

//проверка совпадения дат отправки и статусов для набора строк реестра
function checkUserRegisterRowsMatch($connect, array $rowIDs)
{
	$query = 'SELECT COUNT(DISTINCT(receptionDate)) AS dates, COUNT(DISTINCT(status)) AS statuses
				FROM register
				WHERE register.id IN ('.implode(',', $rowIDs).') AND register.userid='.$_SESSION['userid'];
	if($res = query($query))
	{
		$row = $res->fetch_assoc();
		return ($row['dates']==1) && ($row['statuses']==1);
	}
	else
		return false;
}


//получение строк реестра по списку id
function getRegisterRowsByIDs($connect, array $rowIDs)
{
	$query = "SELECT register.id, waybill, creationDate, receptionDate, IFNULL(deliveryDate, deliveryterms.value) as deliveryDate, weight, places, deliveryCity, address, sum, orderstatuses.value as status,
					concat(LPAD(timeSpanFrom DIV 2, 2, '0'), ':', LPAD(MOD(timeSpanFrom, 2)*30, 2, '0'), ' - ', LPAD(timeSpanTo DIV 2, 2, '0'), ':', LPAD(MOD(timeSpanTo, 2)*30, 2, '0')) as deliveryInterval,
					regionR.code as receptionRegionCode, regionD.code as deliveryRegionCode, regionD.value as deliveryRegion,
					paymenttypes.value as paymentType, paymentTypeID, contactPhone1, contactPhone2, addressee, contactFIO,  CONCAT(dimension1, ' x ', dimension2, ' x ', dimension3) as dimensions,
					users.companyName, note
		FROM register
		LEFT JOIN orderstatuses ON register.status = orderstatuses.id
		LEFT JOIN paymenttypes on register.paymentTypeID = paymenttypes.id
		LEFT JOIN deliveryterms ON register.deliveryDay = deliveryterms.id
		LEFT JOIN regions as regionR ON regionR.id = receptionRegion
		LEFT JOIN regions as regionD ON regionD.id = deliveryRegion
		LEFT JOIN users ON register.userid = users.id
		WHERE register.id IN (".implode(',', $rowIDs).')';
	//die($query);
	return query($query);
}

//получение данных строки реестра по id пользователя и строки
function getUserRegisterRowByID($connect, int $userID, int $rowID)
{
	$query = 'SELECT waybill, receptionDate, addressee, deliveryCity, address, contactFIO, contactPhone1, contactPhone2,
				places, weight, deliveryterms.value as deliveryDay, register.deliveryDay as deliveryDayID, deliveryDate, timeSpanFrom, timeSpanTo,
				dimension1, dimension2, dimension3, receptionCity, receptionAddress, receptionContactFIO,
				paymenttypes.value as paymentType, paymentTypeID, sum, note, creationDate, orderstatuses.value as status, register.status as statusID,
				regionR.value as receptionRegion, regionD.value as deliveryRegion,
				regionR.code as receptionRegionCode, regionD.code as deliveryRegionCode,
				regionR.id as receptionRegionID, regionD.id as deliveryRegionID, deleted, IFNULL(receptiondates.date, 0) as dateClosed,
				direction, takePapers, needNotification
		FROM register
		LEFT JOIN deliveryterms on register.deliveryDay = deliveryterms.id
		LEFT JOIN paymenttypes on register.paymentTypeID = paymenttypes.id
		LEFT JOIN orderstatuses on register.status = orderstatuses.id
		LEFT JOIN regions as regionR ON regionR.id = receptionRegion
		LEFT JOIN regions as regionD ON regionD.id = deliveryRegion
		LEFT JOIN receptiondates ON receptiondates.userid = register.userid AND receptiondates.date = register.receptionDate
		WHERE register.userid='.$userID.' AND register.id='.$rowID;
	return query($query);
}
//получение данных строки реестра по id строки
function getRegisterRowByID($connect, int $rowID)
{
	$query = 'SELECT userid, users.login, users.companyName, waybill, receptionDate, addressee, deliveryCity, address, contactFIO, contactPhone1, contactPhone2,
				places, weight, deliveryterms.value as deliveryDay, register.deliveryDay as deliveryDayID, deliveryDate, timeSpanFrom, timeSpanTo,
				dimension1, dimension2, dimension3, receptionCity, receptionAddress, receptionContactFIO,
				paymenttypes.value as paymentType, paymentTypeID, sum, note, creationDate, orderstatuses.value as status, register.status as statusID,
				regionR.value as receptionRegion, regionD.value as deliveryRegion,
				regionR.code as receptionRegionCode, regionD.code as deliveryRegionCode,
				regionR.id as receptionRegionID, regionD.id as deliveryRegionID, deleted
		FROM register
		INNER JOIN users ON register.userid = users.id
		LEFT JOIN deliveryterms on register.deliveryDay = deliveryterms.id
		LEFT JOIN paymenttypes on register.paymentTypeID = paymenttypes.id
		LEFT JOIN orderstatuses on register.status = orderstatuses.id
		LEFT JOIN regions as regionR ON regionR.id = receptionRegion
		LEFT JOIN regions as regionD ON regionD.id = deliveryRegion
		WHERE register.id='.$rowID;
	return query($query);
}

function updateRegisterRowStatus($connect, int $rowid, int $status)
{
	$query = "CALL updateRegisterRowStatus($rowid, $status)";
	if($res=query($query))
	{
		$row = $res->fetch_assoc();
		$res->data_seek(0);
		mysqli_free_result($res);
		mysqli_next_result($connect);
		logevent($connect, EVENT_TYPE_ORDERDATA, EVENTSOURCE, 'Изменён статус отправления', $rowid,  serialize(htmlspecialchars($row['oldStatusName']).' &rarr; '.htmlspecialchars($row['newStatusName'])));
		return true;
	}
	else
		return false;
	//return query('UPDATE register SET status = '.$status.' WHERE id='.$rowid);
}

function closeDate($connect, $date, int $userID=null)
{
	if(!is_date($date)) return false;
	if(is_null($userID)) $userID = $_SESSION['userid'];
	
	if(isDateClosed($connect, $date, $userID)) return true;
	
	$fl = true;
	$rowIDs = array();
	$query = "SELECT id FROM register WHERE userid=$userID and receptionDate='$date' and status=0 and deleted=0";
	if(($res = query($query)) && $res->num_rows>0)
	{
		while($row = $res->fetch_assoc())
		{
			$rowIDs[]=$row['id'];
			$fl &= updateRegisterRowStatus($connect, $row['id'], 1);
		}
	}
	if($fl)
	{
		$query = "REPLACE INTO receptiondates (date, userid) VALUES('$date', $userID)";
		if(query($query))
		{
			sendClosedDayRegister($connect, dateForPrint($date), $userID, $rowIDs);
			
			return logevent($connect, EVENT_TYPE_INFO, EVENTSOURCE, 'Выполнено закрытие дня: '.dateForPrint($date), $userID);
		}
		else
			return false;
	}
	else return false;
}
function sendClosedDayRegister($connect, $dates, int $userID, array $rowIDs)
{
	$mailMode = 1;
	$filename = 'register_'.$userID.'_'.$dates.'.xml';
	if($userID==$_SESSION['userID'])
		$companyName = $_SESSION['companyName'];
	else
		$companyName = getCompanyNameByID($connect, $userID);
	$login = "система Личного кабинета";
	ob_start();
	include('print/register_excel.php');
	$data = ob_get_clean();
	$emailbody = '<p>Пользователь <b>'.$companyName.'</b> выполнил<br/>
				закрытие дня <b>'.$dates.'</b>.<br/>
				Теперь для логистов Dedal-express доступна информация по данным заказам в Личном кабинете.</p>
				<p>Всего заказов в реестре: <b>'.count($rowIDs).'</b>.</p>
				<p>Файл реестра во вложении.</p>';
				//die($data);
	sendMailAttachment(getEmailForFiles($connect, $userID), getSetting('emailFrom', $connect), getSetting('nameFrom', $connect), 'Реестр заказов компании '.$companyName.' на '.$dates, $emailbody, $filename, $data);
}

function isDateClosed($connect, $date, int $userID=null)
{
	if(!is_date($date)) return false;
	if(is_null($userID)) $userID = $_SESSION['userid'];
	$query = "SELECT count(*) as cnt FROM receptiondates WHERE userid=$userID AND date='$date'";
	if($res = query($query))
	{
		if($res->fetch_assoc()['cnt']>0)
		{
			return true;
		}
	}
	return false;	
}

function getUsersTimeSpanByID($connect, int $userid, int $timeSpanId)
{
	return query("SELECT timeSpanFrom, timeSpanTo FROM deliveryintervals WHERE id=$timeSpanId AND userid=$userid");
}

function getUsersTimesSpanByUserID($connect, int $userid)
{
	return query("SELECT id, timeSpanFrom, timeSpanTo FROM deliveryintervals WHERE userid=$userid ORDER BY timeSpanFrom, timeSpanTo");
}

function AddRegisterRow($connect, int $userID, string $waybill, $receptionDate, string $addressee, string $address, string $contactFIO, string $contactPhone1, string $contactPhone2,
						int $places, float $weight, int $dimension1, int $dimension2, int $dimension3, int $deliveryDay, $deliveryDate, int $timeSpanFrom, int $timeSpanTo, int $paymentTypeID, float $sum, string $note,
						int $receptionRegion, string $receptionCity, string $receptionAddress, string $receptionContactFIO, int $deliveryRegion, string $deliveryCity,
						int $type=NULL, string $direction = NULL, bool $takePapers = false, bool $needNotification = false)
{
	if(is_null($type)) $type = ADDED_SEPARATE_FORM;
	if(is_null($direction)) $direction = DIRECTION_DELIVERY;
	$waybill = prepStr($waybill);
	$addressee = prepStr($addressee);
	$address = prepStr($address);
	$contactFIO = prepStr($contactFIO);
	$contactPhone1 = prepStr($contactPhone1);
	$contactPhone2 = prepStr($contactPhone2);
	$note = prepStr($note);
	$receptionCity = prepStr($receptionCity);
	$receptionAddress = prepStr($receptionAddress);
	$receptionContactFIO = prepStr($receptionContactFIO);
	$deliveryCity = prepStr($deliveryCity);
	$direction = prepStr($direction);
	$takePapers = intval($takePapers);
	$needNotification = intval($needNotification);
						
	$defaultstatus = 0;
	$rowid = 0;
	
	$query = "CALL AddRegisterRow ($userID, '$waybill', ".dateForBase($receptionDate).", '$addressee', '$address', '$contactFIO', '$contactPhone1', '$contactPhone2', $places, $weight, $dimension1, $dimension2, $dimension3, $deliveryDay, ".dateForBase($deliveryDate).", $timeSpanFrom, $timeSpanTo, $paymentTypeID, $sum, '$note', $receptionRegion, '$receptionCity', '$receptionAddress', '$receptionContactFIO', $deliveryRegion, '$deliveryCity', $defaultstatus, $type, '$direction', $takePapers, $needNotification);";
	//die($query);
	if($res = query($query))
	{
		$rowid = $res->fetch_assoc()['rowid'];
		$res->data_seek(0);
		mysqli_free_result($res);
		mysqli_next_result($connect);
		logevent($connect, EVENT_TYPE_ORDERDATA, 'AddRegisterRow', 'Отправление добавлено', $rowid, null);
	}
	//else
		//echo mysqli_error($connect);
	return $rowid;
}

function getDeliveryIntervalsForUser($connect, int $userid)
{
	return query('SELECT id, timeSpanFrom, timeSpanTo FROM deliveryintervals WHERE userid='.$userid.' ORDER BY timeSpanFrom, timeSpanTo');
}

function addUserDeliveryInterval($connect, int $userid, int $from, int $to)
{
	return query("INSERT INTO deliveryintervals (timeSpanFrom, timeSpanTo, userid) VALUES ($from, $to, $userid)");
}

function deleteUserDeliveryInterval($connect, int $userid, int $intervalid)
{
	$query = "DELETE FROM deliveryintervals WHERE id=$intervalid AND userid=$userid";
	return query($query);
}

function getUsersList($connect, string $search)
{
	$search = prepStr($search);
	$where = ($search=='') ? '' : " AND login LIKE '%".$search."%' OR companyName  LIKE '%".$search."%' OR email LIKE '%".$search."%'";
	$query = "SELECT users.id, login, companyName, email,
				IFNULL(usersettings.value_str, CONCAT('<span class=\"grey\">', settings.value, '</span>')) as notificationsEmail,
				IFNULL(contracttypes.value, '') as contractType, IFNULL(contracttypes.id, -1) as contractTypeID
				FROM users
				LEFT JOIN usersettings
				ON users.id = usersettings.userid AND usersettings.name='notificationsEmail'
				LEFT JOIN usersettings as usersettings2
				ON users.id = usersettings2.userid AND usersettings2.name='contractType'
				LEFT JOIN contracttypes
				ON usersettings2.value = contracttypes.id,
				settings WHERE settings.name='emailForFiles' $where ORDER BY id";
	return query($query);
}

function getUserRights($connect, int $userid)
{
	return query("SELECT rights.id, rights.name, rights.description, NOT ISNULL(userrights.userid) as hasright FROM userrights RIGHT JOIN rights ON rights.id = userrights.rightid AND userrights.userid = $userid ORDER BY rights.name");
}

function addUserRight($connect, int $userid, int $rightid)
{
	$query = "INSERT INTO userrights (userid, rightid) VALUES ($userid, $rightid)";
	return query($query);
}

function deleteUserRight($connect, int $userid, int $rightid)
{
	$query = "DELETE FROM userrights WHERE userid=$userid AND rightid=$rightid";
	return query($query);
}

function addPasswordResetRow($connect, int $userid, string $userIP, string $token)
{
	$token = prepStr($token);
	return query("INSERT INTO passwordreset (userid, clientip, token, requestdate) VALUES ($userid, '$userIP', '$token', NOW())");
}
function deletePasswordResetRows($connect, int $userid)
{
	return query("DELETE FROM passwordreset WHERE userid=$userid OR requestdate < CURDATE() - INTERVAL 1 DAY");
}
function getPasswordResetData($connect, string $token)
{
	$token = prepStr($token);
	return query("SELECT passwordreset.*, users.login, users.email FROM passwordreset INNER JOIN users ON users.id = passwordreset.userid WHERE token='$token' AND requestdate >= CURDATE() - INTERVAL 1 DAY");
}
function updateUserPassword($connect, int $userid, string $password)
{
	return query("UPDATE users SET password_hash='".password_hash($password, PASSWORD_DEFAULT)."' WHERE id=$userid");
}
function CurrentUserPasswordVerify($connect, string $p)
{
	if($res = query('SELECT password_hash FROM users WHERE id='.$_SESSION['userid']))
	{
		$row = $res->fetch_assoc();
		return password_verify($p, $row['password_hash']);
	}
	else
		return false;
}
function UpdateCurrentUserProfile($connect, string $cn, string $e)
{
	$cn = prepStr($cn);
	$e = prepStr($e);
	$query = "UPDATE users SET companyName='$cn', email='$e' WHERE id=".$_SESSION['userid'];
	return query($query);
}
function UpdateOtherUserProfile($connect, string $cn, string $e, int $userid)
{
	$cn = prepStr($cn);
	$e = prepStr($e);
	$query = "UPDATE users SET companyName='$cn', email='$e' WHERE id=".$userid;
	return query($query);
}

function getEmailForFiles($connect, int $userid)
{
	$query = "SELECT IFNULL(usersettings.value_str, settings.value) as value FROM settings LEFT JOIN usersettings ON usersettings.userid=$userid AND usersettings.name='notificationsEmail' WHERE settings.name='emailForFiles'";
	$res = query($query);
	$row = $res->fetch_assoc();
	mysqli_free_result($res);
    return htmlspecialchars($row['value']);
}

//возвращает 1 для упрощённой накладной, 0 для полной
function getWaybillType($connect, int $rowID)
{
	$query = "SELECT simple FROM waybilltype
				INNER JOIN usersettings ON usersettings.userid = '".$_SESSION['userid']."' AND usersettings.name='contractType' AND waybilltype.contractType = usersettings.value 
				INNER JOIN register ON waybilltype.paymentType = register.paymentTypeID
				WHERE register.id = $rowID";

			
	if(($res = query($query)) && $res->num_rows > 0)
	{
		$row=$res->fetch_assoc();
		mysqli_free_result($res);
		return $row['simple'];
	}
	else
		return 0;	
}

//Получение всех адресов отправления пользователя
function getUserAddresses($connect, int $userid)
{
	$query = "SELECT regions.value as regionName, addresses.* FROM addresses LEFT JOIN regions ON regions.id = addresses.region WHERE userid=$userid";
	return query($query);
}
//Добавление адреса отправления пользователя
function addUserAddress($connect, int $userid, int $region, string $cityName, string $address)
{
	$cityName = prepStr($cityName);
	$address = prepStr($address);
	$query = "INSERT INTO `addresses`(`userid`, `region`, `cityName`, `address`) VALUES ($userid, $region, '$cityName', '$address')";
	return query($query);
}
//Удаление адреса отправления пользователя
function deleteUserAddress($connect, int $userid, int $addressid)
{
	$query = "DELETE FROM addresses WHERE userid=$userid  AND id=$addressid";
	return query($query);
}

//Получение списка полей, которые можно редактировать, по статусу
function getEditableFields($connect, int $status)
{
	$result = array();
	$query = 'SELECT fieldName
				FROM editablefields
				INNER JOIN fields
				ON fields.id = editablefields.field
				WHERE status='.$status.' AND editable=1';
				//echo($query);
	if($res = query($query))
		while($row=$res->fetch_assoc())
			$result[] = $row['fieldName'];
	mysqli_free_result($res);
	return $result;	
}

//получение строк реестра по условиям: статус, отправитель, даты отправления и доставки
function getCommonRegisterRows($connect, array $orderStatuses, string $company, $minReceptionDate, $maxReceptionDate, $minDeliveryDate, $maxDeliveryDate)
{
	$conditions = array();

	if(count($orderStatuses) == 1)
		$conditions[] = 'status ='.prepNum($orderStatuses[0]);
	elseif(count($orderStatuses) > 1)
		$conditions[] = 'status IN('.implode(', ', $orderStatuses).')';
	else
		$conditions[] = 'status != 0';
	
	$minDeliveryDate = prepDate($minDeliveryDate); $maxDeliveryDate = prepDate($maxDeliveryDate); 

	if(!empty($minDeliveryDate) && !empty($maxDeliveryDate))
		$conditions[] = "deliveryDate BETWEEN '$minDeliveryDate' AND '$maxDeliveryDate'";
	elseif(!empty($minDeliveryDate))
		$conditions[] = "deliveryDate >= '$minDeliveryDate'";
	elseif(!empty($maxDeliveryDate))
		$conditions[] = "deliveryDate <= '$maxDeliveryDate'";

	$minReceptionDate = prepDate($minReceptionDate); $maxReceptionDate = prepDate($maxReceptionDate); 
	if(!empty($minReceptionDate) && !empty($maxReceptionDate))
		$conditions[] = "receptionDate BETWEEN '$minReceptionDate' AND '$maxReceptionDate'";
	elseif(!empty($minReceptionDate))
		$conditions[] = "receptionDate >= '$minReceptionDate'";
	elseif(!empty($maxReceptionDate))
		$conditions[] = "receptionDate <= '$maxReceptionDate'";

	if(!empty($company))
	{
		$company = prepStr($company);
		$conditions[] = "(users.companyName LIKE '%$company%' OR users.login LIKE '%$company%')";
	}
	
	$conditions = implode(' AND ', $conditions);
	
	$query = "SELECT register.id, waybill, creationDate, receptionDate, IFNULL(deliveryDate, deliveryterms.value) as deliveryDate, weight, places, deliveryCity, address, sum, orderstatuses.value as status,
					concat(LPAD(timeSpanFrom DIV 2, 2, '0'), ':', LPAD(MOD(timeSpanFrom, 2)*30, 2, '0'), ' - ', LPAD(timeSpanTo DIV 2, 2, '0'), ':', LPAD(MOD(timeSpanTo, 2)*30, 2, '0')) as deliveryInterval,
					regionR.code as receptionRegionCode, regionD.code as deliveryRegionCode, regionD.value as deliveryRegion,
					paymenttypes.value as paymentType, contactPhone1, contactPhone2, addressee, contactFIO,  CONCAT(dimension1, ' x ', dimension2, ' x ', dimension3) as dimensions,
					users.companyName, note
		FROM register
		LEFT JOIN orderstatuses ON register.status = orderstatuses.id
		LEFT JOIN paymenttypes on register.paymentTypeID = paymenttypes.id
		LEFT JOIN deliveryterms ON register.deliveryDay = deliveryterms.id
		LEFT JOIN regions as regionR ON regionR.id = receptionRegion
		LEFT JOIN regions as regionD ON regionD.id = deliveryRegion
		LEFT JOIN users ON register.userid = users.id
		WHERE deleted = FALSE AND $conditions";
	//die($query);
	return query($query);
}

//получает записи лога, относящиеся к изменениям данных отправления
function getOrderLogEvents(int $objectid)
{
	$query = "SELECT log.*, logdetails.details, users.login, users.companyName FROM
				log
				LEFT JOIN
				logdetails
				ON log.id = logdetails.id
				LEFT JOIN users
				ON log.userid = users.id
				WHERE log.objectid = $objectid
				AND log.type ='".EVENT_TYPE_ORDERDATA."'
				ORDER BY creationDate DESC";
	return query($query);
}

//получает список ID отправлений по дате забора для заданного или текущего пользователя
function getUserOrderIDsByReceptionDate($connect, string $receptionDate, int $userID = null)
{
	if(!is_date($receptionDate)) return false;
	if(is_null($userID)) $userID = $_SESSION['userid'];
	
	$query = "SELECT id FROM register
				WHERE receptionDate = '$receptionDate'
				AND userid = $userID";
	
	if($res = query($query))
	{
		$IDs = array();
		while($row = $res->fetch_assoc())
			$IDs[] = $row['id'];
		return $IDs;
	}
	else
		return false;
}

//помечает отправление как удалённое (только регистрирующиеся отправления текущего пользователя)
function deleteRow(int $rowid)
{
	$query = "UPDATE register
				SET deleted = 1
				WHERE register.userid=".$_SESSION['userid']."
				AND register.id=$rowid
				AND status=0";
	query($query);
	if(affected_rows()>0)
	{
		logevent($connect, EVENT_TYPE_ORDERDATA, 'deleteRow', 'Отправление удалено', $rowid, null);
		return true;
	}
	else
		return false;
}

//восстанавливает удалённое отправление, если дата забора ещё не прошла и не закрыта (только регистрирующиеся отправления текущего пользователя)
function restoreRow(int $rowid)
{
	$query = "UPDATE register
				LEFT JOIN receptiondates ON receptiondates.userid = register.userid AND receptiondates.date = register.receptionDate
				SET deleted = 0
				WHERE register.userid=".$_SESSION['userid']."
				AND register.id=$rowid
				AND receptionDate>=CURDATE()
				AND receptiondates.date IS NULL
				AND status=0";
				//die($query);
	query($query);
	if(affected_rows()>0)
	{
		logevent($connect, EVENT_TYPE_ORDERDATA, 'deleteRow', 'Отправление восстановлено', $rowid, null);
		return true;
	}
	else
		return false;
}

function addDirectoryRow(string $directorytype, string $new_value)
{
	$directorytype = prepStr($directorytype);
	$new_value = prepStr($new_value);
	$headers = ['orderstatuses'=>'Статусы', 'paymenttypes'=>'Типы оплаты', 'contracttypes'=>'Типы договоров', 'deliveryterms'=>'Срочность']; 
	$query = "INSERT INTO $directorytype (value) VALUES ('$new_value')";
	query($query);
	if(affected_rows()>0)
	{
		$header = $headers[$directorytype]; if(empty($header)) $header = $directorytype;
		logevent($connect, EVENT_TYPE_INFO, 'addDirectoryRow', 'Добавлено значение в справочник '.htmlspecialchars($header).': '.htmlspecialchars($new_value), insert_id(), null);
		return true;
	}
	else
		return false;
}

//добавляет статус из «Бегунка» в таблицу quickrunconnector
function storeQuickRunStatus(int $rowid, string $quickrun_id, string $quickrun_status, int $quickrun_status_id)
{
	$quickrun_status = prepStr($quickrun_status);
	$quickrun_id = prepStr($quickrun_id);

	$query = "REPLACE INTO quickrunconnector
				(orderid, quickrun_id, quickrun_status, quickrun_status_id, updatedOn)
				VALUES
				($rowid, '$quickrun_id', '$quickrun_status', $quickrun_status_id, NOW())";
				
	return query($query);
}
//получает данные для запроса статуса из «Бегунка» по списку id отправлений
function getOrdersForQuickrunByIDs(array $rowIDs)
{
	$query = 'SELECT register.id, regionR.code as receptionRegionCode, regionD.code as deliveryRegionCode, deliveryDate
		FROM register
		LEFT JOIN regions as regionR ON regionR.id = receptionRegion
		LEFT JOIN regions as regionD ON regionD.id = deliveryRegion
		WHERE register.id IN ('.implode(',', $rowIDs).')';
	return query($query);
}
//получает список ID заказов из ЛК по списку номеров из Бегунка
function getOrdersByFullIDs(array $fullIDs)
{
	$query = "SELECT register.id, regionR.code as receptionRegionCode, regionD.code as deliveryRegionCode, deliveryDate
		FROM register
		LEFT JOIN regions as regionR ON regionR.id = receptionRegion
		LEFT JOIN regions as regionD ON regionD.id = deliveryRegion
		WHERE CONCAT(regionR.code, regionD.code, '-', LPAD(register.id, 7, '0')) IN ('".implode("', '", $fullIDs)."')";
	return query($query);
}

//групповое обновление статуса
function updateStatuses(array $rowIDs, int $newStatusId)
{
	//echo "<pre>";
	//var_dump($rowIDs);
	//echo "</pre>";
	global $connect;
	$errors = array();
	foreach($rowIDs as $rowid)
	{
		if(!updateRegisterRowStatus($connect, $rowid, $newStatusId))
			$errors[] = $rowid;
	}
	return $errors;
}

function BatchCloseDate(string $receptionDate, int $userID = null)
{
	if(!is_date($receptionDate)) return false;
	if(is_null($userID)) $userID = $_SESSION['userid'];
	return query("CALL BatchCloseDate('$receptionDate', '$userID')");
}

//Статус работы - обновление
function jobStatusSet($jobname, $statusOK, $message)
{
	$query = "INSERT INTO
			_jobs (jobName, statusOK, message)
			VALUES('$jobname', '$statusOK', '$message')";
	return query($query);
}

//получение последней даты проверки статусов в Бегунке
function getQuickRunLastCheckDate()
{
	$query = "SELECT DATE(_jobs.finishedOn) as finishedOn
		FROM _jobs
		WHERE jobName = 'quickRunStatuses' AND statusOK=TRUE
		ORDER BY finishedOn DESC
		LIMIT 1";
	$res = query($query);
	if($res->num_rows > 0)
		return $res->fetch_assoc()['finishedOn'];
	else
	{
		$date = new DateTime(); $date->sub(new DateInterval('P1D'));
		return $date->format('Y-m-d');// date('Y-m-d');
	}
}

//получение номеров отправлений для группового обновления статуса из Бегунка
function getOrderIDsForQuickRunCheck()
{
	$date = getQuickRunLastCheckDate();
	$query = "SELECT register.id, regionR.code as receptionRegionCode, regionD.code as deliveryRegionCode, deliveryDate, CONCAT(regionR.code, regionD.code, '-', LPAD(register.id, 7, '0')) as fullid
			FROM
			register
			LEFT JOIN regions as regionR ON regionR.id = receptionRegion
			LEFT JOIN regions as regionD ON regionD.id = deliveryRegion
			LEFT JOIN quickrunconnector ON register.id = quickrunconnector.orderid
			WHERE (deliveryDate BETWEEN CAST('$date' as DATE) AND CURDATE() OR quickrun_status_id=2)
			AND status IN (1,2,3)";
			//echo $query;
	return query($query);
}

?>