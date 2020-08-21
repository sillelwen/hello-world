<?php
$res = getOrderLogEvents($lineid);
if(!$res)
	die();
?>
		<h5>Журнал событий</h5>
		<table class="register list" width="100%">
			<colgroup>
			<col width="20%"/>
			<col/>
			<col width="20%"/>
			</colgroup>
			<thead>
				<tr><th>Дата и время</th><th>Описание события</th><th>Пользователь</th></tr>
			</thead>
			<tbody>
<?php
while($row = $res->fetch_assoc())
{
	$details = unserialize($row['details']);
	$detailsText = '';
	if(is_array($details))
	{
		foreach($details as $key=>$var)
		{
			if(is_array($var) && isset($var['columns']))
			{
				$detailsText.='<table class="list">';
				$detailsText.='<tr>';
				foreach($var['columns'] as $header)
					$detailsText.="<th>$header</th>";
				$detailsText.='</tr>';
				foreach($var as $key2=>$val)
				{
					if($key2 != 'columns')
					{
						$detailsText.='<tr>';
						foreach($var['columns'] as $colKey=>$header)
							$detailsText.='<td>'.$val[$colKey].'</td>';
						$detailsText.='</tr>';
					}
				}
				
				$detailsText.='</table>';
			}
			else
				$detailsText .= "<p>$var</p>";
		}
	}
	else
		$detailsText = $details;
	
	echo '<tr><td>'.dateTimeForPrint($row['creationDate']).'</td><td><strong>'.$row['message'].'</strong>';
	if(!empty($row['details'])) echo '<br/><br/>'.str_replace("\n", '<br/>', $detailsText);
	echo '</td><td>'.$row['login'].' ('.$row['companyName'].')</td></tr>';
}
if($res->num_rows==0)
	echo '<tr><td>'.$creationDate.'</td><td><strong>Отправление добавлено</strong></td><td>'.$login.' ('.$companyName.')</td></tr>';
?>
			</tbody>
		</table>