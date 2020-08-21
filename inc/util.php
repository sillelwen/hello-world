<?php
date_default_timezone_set('Europe/Moscow');
//Поддержание сессии
/*ini_set('session.gc_maxlifetime', 604800);
ini_set('session.cookie_lifetime', 604800);
session_set_cookie_params(604800);*/
session_start();

if(file_exists('maintenance'))
{
	$_SESSION['last_refferer'] = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	header("Location: maintenance.php");
	die();
}
else
{
	$_SESSION['last_refferer'] = '';
}

//КОНСТАНТЫ
DEFINE('SYSTEM_USER_ID', 1);

DEFINE('ADDED_SEPARATE_FORM', 1);
DEFINE('ADDED_ONLINE_REGISTER', 2);
DEFINE('ADDED_FILE_UPLOAD', 3);
DEFINE('ADDED_ADMINZONE', 4);

DEFINE('EVENT_TYPE_AUDIT', 'audit');
DEFINE('EVENT_TYPE_INFO', 'info');
DEFINE('EVENT_TYPE_WARNING', 'warning');
DEFINE('EVENT_TYPE_ERROR', 'error');
DEFINE('EVENT_TYPE_ORDERDATA', 'orderdata');

DEFINE('DIRECTION_DELIVERY', 'delivery');
DEFINE('DIRECTION_RECEPTION', 'reception');
DEFINE('DIRECTION_BOTH', 'both');

DEFINE('DELIVERYTERMS_STANDART', 1);
DEFINE('DELIVERYTERMS_CHOOSE_DATE', 2);

DEFINE('INTERVALS_FROM_TO', 1);
DEFINE('INTERVALS_PRESET', 2);

DEFINE('COLUMNS_DEFAULT', '1;3;4;7;8;10;11');
DEFINE('COLUMNS_PRINT_DEFAULT', '1;4;5;6;9;11');
DEFINE('COLUMNS_WIDTH_DEFAULT', '0;;23;;;11;11;;56;;;14;42');
DEFINE('COLUMNS_COMMONREG_DEFAULT', '1;2;3;4;5;6;8;10;11');

DEFINE('BATCH_CLOSE_HASH', '$2y$10$53Y3tDp3/jViY3Eq0DQZWuAu3gf.9sB696P.R7Ap46r4HHaYn9pTy');
DEFINE('BATCH_STATUSUPDATE_HASH', '$2y$10$TjS2KJiSyYdUHSdSA3/pM./fItCLBSaOh2oAXkC5huZkVIMPacqFq');

include_once('mysql.php');

if( !isset($_SESSION['last_access']) || (time() - $_SESSION['last_access']) > 60 )
  $_SESSION['last_access'] = time();
if(isset($_SESSION['userid'])) $_SESSION['userid'] = intval($_SESSION['userid']);

//сохранение в сессии основных данных пользователя после авторизации
function setsessionloginparams($row)
{
	$_SESSION['userid'] = $row['id'];
	$_SESSION['login'] = $row['login'];
	$_SESSION['companyName'] = $row['companyName'];
	$_SESSION['email'] = $row['email'];
}

//чтение пользовательских настроек, хранимых в сессии
function sessionreadsettings($connect)
{
    $_SESSION['rows'] = getUserSetting('rows', $connect, 20);	
}

//генерация 10-символьного буквенно-цифрового пароля
function generatePassword()
{
	$chars="qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
	$max=10;
	$size=StrLen($chars)-1;
	$password=null;
    while($max--)
		$password.=$chars[rand(0,$size)];
	return $password;
}

//вывод пунктов для выпадающего списка - выбор из справочника
function listOptions($tablename, $connect, $value=null)
{
	$res = getAvailableValues($connect, $tablename);
	while($row = $res->fetch_assoc())
	{
		$result .= '<option value="'.$row['id'].'"';
		if($value!==null && ($value==$row['id'] || (is_array($value) && in_array($row['id'], $value)))) $result .= ' selected';
		$result .= '>'.htmlspecialchars($row['value']).'</option>';
	}
	return $result;
}
//вывод пунктов для выпадающего списка регионов
function listRegions($connect, $reception=false, $delivery=false, $value=null)
{
	$res = getAvailableRegions($connect, $reception, $delivery);
	while($row = $res->fetch_assoc())
	{
		$result .= '<option value="'.$row['id'].'"';
		if($value!==null && ($value==$row['id'] || (is_array($value) && in_array($row['id'], $value)))) $result .= ' selected';
		$result .= '>'.htmlspecialchars($row['value']).'</option>';
	}
	return $result;
}
//вывод пунктов для выпадающего списка типов оплаты с учётом типа договора
function listPaymentTypes($connect, $value=null, $userid=null)
{
	$res = getAvailablePaymentTypes($connect, $userid);
	while($row = $res->fetch_assoc())
	{
		$result .= '<option value="'.$row['id'].'"';
		if($value!==null && ($value==$row['id'] || (is_array($value) && in_array($row['id'], $value)))) $result .= ' selected';
		$result .= '>'.htmlspecialchars($row['value']).'</option>';
	}
	return $result;
}

//вывод выпадающего списка для выбора времени интервала доставки (с 9:00-22:30, по 9:30-23:00)
function buildTimeList($name, $value=null, $type='from')
{
	$bottom = ($type=='to') ? 19 : 18;
	$top = ($type=='from') ? 45 : 46;
	if(empty($value))
		$value = ($type=='from') ? 18 : 46;
	$res = "<select name=\"$name\">";
	for($i=$bottom; $i<=$top; $i++)
	{
		$res .= "<option value=\"$i\"";
		if($value==$i) $res .= ' selected';
		$res .= '>'.intdiv($i,2).':'. str_pad((($i%2)*30),2,0).'</option>';
	}
	$res .= '</select>';
	return $res;
}

//Вывод списка на основе возможных значений перечисления (enum)
function listOptionsEnum($tablename, $fieldname, $connect, $value=null)
{
	$options = getEnumValues($connect, $tablename, $fieldname);
	foreach($options as $key=>$option)
	{
		$result .= "<option value=\"$option\"";
		if($value!==null && $value==$option) $result .= ' selected';
		$result .= ">$option</option>";
	}
	return $result;
}

//отправка почтового сообщения
function sendMailAttachment($mailTo, $emailFrom, $nameFrom, $subject_text, $message, $filename=null, $data=null)
{
	$preferences = array(
    "output-charset" => "utf-8",
    "line-length" => 76,
    "line-break-chars" => "\n"
	);

	$to = $mailTo;

	$EOL = "\n"; // ограничитель строк, некоторые почтовые сервера требуют \n - подобрать опытным путём
	$boundary     = "--".md5(uniqid(time()));  // любая строка, которой не будет ниже в потоке данных. 

	$subject= '=?utf-8?B?' . base64_encode($subject_text) . '?=';

	$headers    = "MIME-Version: 1.0;$EOL";   
	$headers   .= "Content-Type: multipart/mixed; charset=utf-8; boundary=\"$boundary\"$EOL";  
	//$From = iconv ('utf-8', 'windows-1251',$From);
	$headers   .= "From: ".'=?utf-8?B?'.base64_encode($nameFrom).'?='." <$emailFrom>$EOL";
	$headers   .= "Reply-To: ".'=?utf-8?B?'.base64_encode($nameFrom).'?='." <$emailFrom>$EOL"; 

	$multipart  = "--$boundary$EOL";   
	$multipart .= "Content-Type: text/html; charset=utf-8$EOL";   
	$multipart .= "Content-Transfer-Encoding: base64$EOL";   
	$multipart .= $EOL; // раздел между заголовками и телом html-части 
	$multipart .= chunk_split(base64_encode($message));   

	if($filename!=null)
	{
		#начало вставки файла
		if(is_null($data))
		{
			$file = fopen($filename, "rb");
			$data = fread($file,  filesize( $filename ) );
			fclose($file);
		}
		$NameFile = basename($filename); // в этой переменной надо сформировать имя файла (без всякого пути);
		$multipart .=  "$EOL--$boundary$EOL";   
		$multipart .= "Content-Type: application/octet-stream; name=\"$NameFile\"$EOL";   
		$multipart .= "Content-Transfer-Encoding: base64$EOL";   
		$multipart .= "Content-Disposition: attachment; filename=\"$NameFile\"$EOL";   
		$multipart .= $EOL; // раздел между заголовками и телом прикрепленного файла 
		$multipart .= chunk_split(base64_encode($data));   
		#>>конец вставки файла
	}

	$multipart .= "$EOL--$boundary--$EOL";

	 //Отправляем письмо
	if(!mail($to, $subject, $multipart, $headers)){
		return FALSE;
	}
	else{
		return TRUE;
	}

}

//получение IP клиента
function getUserIpAddr(){
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
        //ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        //ip pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }else{
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

//добавление стрелок для сортировки таблицы
function arrows($link, $current, $sortby, $direction)
{
	$result = ($direction=='ASC' && $sortby==$current) ? '<span class="sortArrow">&#x25B2;</span>' : '<a href="'.$link.'sortby='.$current.'" class="sortArrow">&#x25B2;</a>';
	$result .= ($direction=='DESC' && $sortby==$current) ? '<span class="sortArrow">&#x25BC;</span>' : '<a href="'.$link.'sortby='.$current.'&back" class="sortArrow">&#x25BC;</a>';
	return $result;
}
function arrowsPOST($current, $sortby, $direction)
{
	$result = ($direction=='ASC' && $sortby==$current) ? '<span class="sortArrow">&#x25B2;</span>' : "<a class=\"sortArrow\" href=\"#\" onclick=\"sort($current, 0);\">&#x25B2;</a>";
	$result .= ($direction=='DESC' && $sortby==$current) ? '<span class="sortArrow">&#x25BC;</span>' : "<a class=\"sortArrow\" href=\"#\" onclick=\"sort($current, 1);\">&#x25BC;</a>";
	return $result;
}

function is_date($str){
    return is_numeric(strtotime($str));
}

//вывод ссылок разбивки на страницы для варианта с POST
function pages($current, $total)
{
	$result = '';
	
	if($total>1)
	{
		if($current > 3)
			$result .= '&nbsp;<a href="#" onclick="setpage(1)" class="pagelink">&laquo;</a>';
		for($i=max(1, $current-2); $i<=min($current+2, $total); $i++)
			$result .= ($i==$current) ? '&nbsp;<span class="currentpage">'.$i.'</span>' : '&nbsp;<a href="#" onclick="setpage('.$i.')" class="pagelink">'.$i.'</a>';
		if($current < $total-2)
			$result .= '&nbsp;<a href="#" onclick="setpage('.$total.')" class="pagelink">&raquo;</a>';
		return $result;
	}
}

//вывод ссылок разбивки на страницы для варианта с GET
function pageslink($current, int $total, string $link, int $rows, int $sortby, string $direction)
{
	$result = '';
	if($total>1)
	{
		$back = $direction=='DESC' ? '&back' : '';
		$link = $link."sortby=$sortby$back&rows=$rows&page=";
		if($current > 3)
			$result .= "&nbsp;<a href=\"$link"."1\" class=\"pagelink\">&laquo;</a>";
		for($i=max(1, $current-2); $i<=min($current+2, $total); $i++)
			$result .= ($i==$current) ? '&nbsp;<span class="currentpage">'.$i.'</span>' : "&nbsp;<a href=\"$link$i\" class=\"pagelink\">$i</a>";
		if($current < $total-2)
			$result .= "&nbsp;<a href=\"$link$total\" class=\"pagelink\">&raquo;</a>";
		return $result;
	}
}

//форматирование значения даты из поля ввода перед записью в БД
function dateForBase($strdate)
{
	return (is_null($strdate) || !is_date($strdate)) ? 'NULL' : "'".$strdate."'";
}

//форматирование даты из формы или базы данных для вывода в формате d.m.Y
function dateForPrint($strDate)
{
	return (!empty($strDate) && is_date($strDate)) ? date_format(date_create($strDate), 'd.m.Y') : $strDate;
}
//форматирование даты и времени из формы или базы данных для вывода в формате d.m.Y
function dateTimeForPrint($strDate)
{
	return (!empty($strDate) && is_date($strDate)) ? date_format(date_create($strDate), 'd.m.Y H:i:s') : $strDate;
}
//преобразование логического значения для печати - да или нет
function boolForPrint(bool $boolVal)
{
	return $boolVal ? 'да' : 'нет';
}

//приводит значение timeSpanFrom/To к человекочитаемому виду времени
function formatTimeVal($val)
{
	return intdiv($val,2).':'.str_pad((($val%2)*30),2,0);
}

//выводит и обрабатывает настройки столбцов печатной формы реестра
function printregister_settings($connect, $postParams=null, $userid=0)
{
	if ($userid==0) $userid = $_SESSION['userid'];
	echo '<form method="POST" name="frmColumns" id="frmColumns" class="gridform filterform">';
	echo '<div class="valueslist advanced"><h4>Настройки печатной формы</h4>';

	$sortfieldnames = [1=>'Номер заказа', 2=>'ID печатной формы', 3=>'Дата забора', 4=>'Дата доставки', 5=>'Вес (кг)', 6=>'Кол-во мест', 7=>'Габариты (см)', 8=>'Адрес', 9=>'Время доставки', 10=>'Тип оплаты', 11=>'Сумма с клиента', 12=>'Примечание'];

	if(isset($_POST['setColumns']))
	{
		$columns = isset($_POST['chkColumns']) ? array_keys(array_filter($_POST['chkColumns'], function($v) {return $v == 1;})) : array();
		if(count($columns)==0)
		{
			$message = "<p class=\"message\">Нельзя отключить отображение всех столбцов.</p>";
			$columns = explode(';', getOtherUserSettingStr('columns_print', $connect, $userid, COLUMNS_PRINT_DEFAULT));
		}
		else
		{
			setUserSettingValueStr($connect, $userid, 'columns_print', implode(';',$columns));
			$message = "<p class=\"message\">Список столбцов успешно сохранён.</p>";
		}
		$columnsW = isset($_POST['nColumnsWidth']) ? $_POST['nColumnsWidth'] : array();
		if(count($columnsW)==0)
		{
			//$message = "<p class=\"message\">Нельзя отключить отображение всех столбцов.</p>";
			$columnsW = explode(';', getOtherUserSettingStr('columns_width', $connect, $userid, COLUMNS_WIDTH_DEFAULT));
		}
		else
		{
			$columnsW[0] = '0';
			ksort ($columnsW);
			setUserSettingValueStr($connect, $userid, 'columns_width', implode(';',$columnsW));
			$message = "<p class=\"message\">Ширина столбцов успешно сохранена.</p>";
		}
	}
	else
	{
		$columns = explode(';', getOtherUserSettingStr('columns_print', $connect, $userid, COLUMNS_PRINT_DEFAULT));
		$columnsW = explode(';', getOtherUserSettingStr('columns_width', $connect, $userid, COLUMNS_WIDTH_DEFAULT));
	}

	for($i=1;$i<=count($sortfieldnames);$i++)
	{
		echo '<input type="checkbox" name="chkColumns['.$i.']" id="chkColumns['.$i.']" value="1"'.(in_array($i,$columns) ? ' checked' : '').'/><label for="chkColumns['.$i.']">'.$sortfieldnames[$i].'</label><input type="number" min="5" max="280" name="nColumnsWidth['.$i.']" id="nColumnsWidth['.$i.']" value="'.$columnsW[$i].'" title="Ширина в мм"/>';
	}
	
	if(!empty($postParams))
	{
		if(!is_array($postParams))
			$postParams = [$postParams];
		
		foreach($postParams as $paramname)
		{
			if(is_array($_POST[$paramname]))
			{
				foreach($_POST[$paramname] as $key=>$val)
					echo hiddenField($paramname.'['.$key.']', $val);
			}
			else
				echo hiddenField($paramname, $_POST[$paramname]);
		}
	}
	
	echo '<input type="submit" name="setColumns" value="Применить"/></div></form>';

	return $message;
}

function hiddenField($name, $val)
{
	return '<input type="hidden" name="'.$name.'" value="'.$val.'" />';
}

function explode_dates(string $delimiter, string $value)
{
	$result = array();
	foreach(explode($delimiter, $value) as $val)
	{
		if(is_date($val))
			$result[] = $val;
	}
	return $result;
}
function implode_datesForPrint(string $delimiter, array $array)
{
	$result = ''; $i=1;
	foreach($array as $val)
	{
		if(is_date($val))
			$result .= ($result=='' ?  '' : $delimiter).dateForPrint($val);
	}
	return $result;
}
function explode_numbers(string $delimiter, string $value=null)
{
	$result = array();
	foreach(explode($delimiter, $value) as $val)
	{
		if(is_numeric($val))
			$result[] = $val;
	}
	return $result;
}
function implode_numbers(string $delimiter, array $array)
{
	$result = '';
	foreach($array as $val)
	{
		if(is_numeric($val))
			$result .= ($result=='' ?  '' : $delimiter).$val;
	}
	return $result;
}

function getDirectionName(string $direction)
{
	$res = array(DIRECTION_DELIVERY=>"доставка", DIRECTION_RECEPTION=>"забор", DIRECTION_BOTH=>"забор/доставка")[$direction];
	if(is_null($res)) $res = "неизвестно";
	return $res;
}

?>