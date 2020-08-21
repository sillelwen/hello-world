<form method="get">
Введите адрес в СПб: <input type="text" name="address" value="<?=$_GET['address']?>"/><br/>
<input type="submit" value="Искать"/>
</form>
<?php
if(isset($_GET['address']))
{
	$query = 'http://kladr-api.ru/api.php?cityId=7800000000000&oneString=1&withParent=1&query='.urlencode($_GET['address']);
	$json = file_get_contents($query);
	echo "<p><b>$query</b></p>\r\n";
	echo '<pre>';
	var_dump(json_decode($json));
	echo '</pre>';
}
?>