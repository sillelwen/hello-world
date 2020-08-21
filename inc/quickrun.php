<?php
//Интеграция с системой «Бегунок»
//КОНСТАНТЫ
DEFINE('QUICKRUN_AUTHORIZATION_CODE', 'b47c4bb9-783a-4712-9c43-3f8cdd38cffb');
DEFINE('QUICKRUN_BASE_URL', 'http://www.quickrun.ru/api/1.0');
DEFINE('QUICKRUN_STATUS_DELIVERED_ID', 1);

//получение статуса конкретного заказа
function getQuickrunDeliveryStatus(int $rowid, int $receptionRegionCode, int $deliveryRegionCode, string $deliveryDate)
{
	// создаем параметры контекста
	$options = array(
		'http' => array(  
					'method'  => 'GET',  // метод передачи данных
					'header'  => "Content-type: application/x-www-form-urlencoded\r\nAuthorization: ".QUICKRUN_AUTHORIZATION_CODE
				)  
	); 
	$context  = stream_context_create($options);  // создаём контекст потока
	$fullid = str_pad($receptionRegionCode, 2, '0', STR_PAD_LEFT).str_pad($deliveryRegionCode, 2, '0', STR_PAD_LEFT).'-'.str_pad($rowid, 7, '0', STR_PAD_LEFT);
	$result = file_get_contents(QUICKRUN_BASE_URL.'/client/orders/'.$deliveryDate.'/'.$fullid, false, $context); //отправляем запрос
	$result = json_decode($result);
	
	if($result->success && count($result->result)>0)
	{
		$quickrun_id = $result->result[0]->id;
		$quickrun_status = $result->result[0]->delivery->state->name;
		$quickrun_status_id = $result->result[0]->delivery->state->id;
		if(isset($quickrun_status) && isset($quickrun_status_id) && isset($quickrun_id))
			return array('quickrun_id'=>$quickrun_id, 'quickrun_status'=>$quickrun_status, 'quickrun_status_id'=>$quickrun_status_id);
		else
			return false;
	}
	else
		return false;
}
//получение статуса конкретного заказа по id Бегунка
function getQuickrunDeliveryStatusByID(string $quickrun_id)
{
	// создаем параметры контекста
	$options = array(
		'http' => array(  
					'method'  => 'GET',  // метод передачи данных
					'header'  => "Content-type: application/x-www-form-urlencoded\r\nAuthorization: ".QUICKRUN_AUTHORIZATION_CODE
				)  
	); 
	$context  = stream_context_create($options);  // создаём контекст потока
	$result = file_get_contents(QUICKRUN_BASE_URL.'/client/orders/byId/'.$quickrun_id, false, $context); //отправляем запрос
	$result = json_decode($result);

	if($result->success && count($result->result)>0)
	{
		$quickrun_id = $result->result->id;
		$quickrun_status = $result->result->delivery->state->name;
		$quickrun_status_id = $result->result->delivery->state->id;
		if(isset($quickrun_status) && isset($quickrun_status_id) && isset($quickrun_id))
			return array('quickrun_id'=>$quickrun_id, 'quickrun_status'=>$quickrun_status, 'quickrun_status_id'=>$quickrun_status_id);
		else
			return false;
	}
	else
		return false;
}

//получение списка заказов на заданную дату
function getQuickrunOrdersList(string $deliveryDate)
{
	// создаем параметры контекста
	$options = array(
		'http' => array(  
					'method'  => 'GET',  // метод передачи данных
					'header'  => "Content-type: application/x-www-form-urlencoded\r\nAuthorization: ".QUICKRUN_AUTHORIZATION_CODE
				)  
	); 
	$context  = stream_context_create($options);  // создаём контекст потока
	$i=0;
	$orders = array();
	do
	{
		$result = file_get_contents(QUICKRUN_BASE_URL.'/client/orders/'.$deliveryDate.'?skip='.($i*50).'&take=50', false, $context); //отправляем запрос
		$result = json_decode($result);
		foreach($result->result as $res)
			$orders[] = $res;
		$i++;
	}
	while(!empty($result->result));
	
	return $orders;
}
function getQuickrunDeliveredOrders(string $deliveryDate)
{
	$orders = getQuickrunOrdersList($deliveryDate);
	foreach($orders as $order)
	{
		if($order->delivery->state->id == QUICKRUN_STATUS_DELIVERED_ID)
			$deliveredorders[] = $order;
	}
	return $deliveredorders;
}

/*echo '<pre>';
var_dump(getQuickrunDeliveryStatusByID('f3b252ae-307c-4005-82b2-f51bc2cd2ec7'));
echo '</pre>';*/


?>