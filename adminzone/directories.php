<?php include_once ("../inc/util.php");
if(!isset($_SESSION['userid']))
{
	header("Location: ../login.php");
	die();
}
elseif(!hasNamedRight($_SESSION['userid'], 'admin', $connect))
{
	header("Location: ../login.php");
	die();
}
elseif(!hasNamedRight($_SESSION['userid'], 'changesystemsettings', $connect))
{
	header("Location: ../adminzone.php");
	die();
}
?>
<!DOCTYPE html>
<html>
	<head>
		<?php include_once('../inc/metalinks.php');?>
		<title>Администрирование - Справочники</title>
	</head>
	<body>
		<?php include_once ("../inc/header.php");?>
		<h3>Справочники</h3>
		<p><a href="../adminzone.php">Администрирование</a>&nbsp;&gt;&nbsp;Справочники</p>
		<p>Значения в справочниках не следует удалять, чтобы не нарушать целостность данных для уже имеющихся записей в базе, однако их можно скрыть, сняв галочку, чтобы в будущем они не были доступны для выбора. Скрытое значение можно вернуть вновь, установив галочку.</p>
		<p>Переименование значения в справочнике коснётся всех имеющихся записей, ссылающихся на это значение.</p>
		<p>Для установки зависимостей перейдите в раздел <a href="matrices.php">Матрицы</a></p>
		<div class="tableformpart">
		<?php
		$directorytypes = ['statuses'=>'orderstatuses', 'payment'=>'paymenttypes', 'contract'=>'contracttypes']; //'terms'=>'deliveryterms', 
		$headers = ['statuses'=>'Статусы', 'payment'=>'Типы оплаты', 'contract'=>'Типы договоров']; //'terms'=>'Срочность', 
		if(isset($_POST['new_value']) && trim($_POST['new_value'])!='' && isset($_POST['directorytype']) && array_key_exists($_POST['directorytype'], $directorytypes))
		{
			if(addDirectoryRow($directorytypes[$_POST['directorytype']], $_POST['new_value']))
				$message .= '<p class="message">В справочник &laquo;'.$headers[$_POST['directorytype']].'&raquo; успешно добавлена строка.';
			else
				$message .= '<p class="message">Произошла ошибка при добавлении строки в справочник &laquo;'.$headers[$_POST['directorytype']].'&raquo;.';
		}
		foreach($directorytypes as $key=>$value)
		{
		?>
		<form method="POST">
			<input type="hidden" name="directorytype" value="<?=$key?>"/>
			<table>
				<thead>
					<tr>
						<th colspan="3"><?=$headers[$key]?></th>
					</tr>
					<tr>
						<td>ID</td>
						<td>Название</td>
						<td>&#10003;</td>
					</tr>
				</thead>
				<tbody>
			<?php
			$query = 'SELECT * FROM '.$value;
			$res = $connect->query($query);
			while($row = $res->fetch_assoc())
			{
				if($_POST['directorytype'] == $key)
				{
					$q1=''; $q2='';
					if(isset($_POST['value'][intval($row['id'])]) && $_POST['value'][intval($row['id'])]!=$row['value'])
					{
						$val = $_POST['value'][intval($row['id'])];
						$q1 = " value = '".prepStr($val)."'";
					}
					$en = (isset($_POST['enabled'][$row['id']]) ? intval($_POST['enabled'][$row['id']]) : '0');
					if(isset($_POST['value'][$row['id']]) && $en != $row['enabled'])
					{
						$q2 = ($q1!='' ? ', ' : '')." enabled = '$en'";
					}
					if($q1!='' OR $q2!='')
					{
						$q = "UPDATE $value SET".$q1.$q2.' WHERE id='.$row['id'];
						query($q);
						if(affected_rows()>0)
						{
							$changes = '';
							if($q1!='')
							{
								$changes .= ' Было: '.$row['value'].'. Стало: '.$val.'.';
								$row['value'] = $val;
							}
							if($q2!='')
							{
								$changes .= ' Доступно: '.($en ? 'да' : 'нет').'.';
								$row['enabled'] = $en;
							}
							logevent($connect, EVENT_TYPE_INFO, 'directories', 'Обновлена строка справочника '.$headers[$key].'.'.$changes, $row['id'], null);
							$message .= '<p class="message">Строка '.$row['id'].' справочника &laquo;'.$headers[$key].'&raquo; успешно обновлена.';
						}
						else
							$message .= '<p class="message">Произошла ошибка при попытке обновления значения строки '.$row['id'].' справочника &laquo;'.$headers[$key].'&raquo;.';
					}
				}
				echo '<tr><td>'.$row['id'].'</td><td><input type="text" name="value['.$row['id'].']" value="'.htmlspecialchars($row['value']).'"/></td><td><input type="checkbox" value="1" name="enabled['.$row['id'].']"'.($row['enabled'] ? ' checked' : '').'/></td></tr>';
			}
			?>
				<tr><td>+</td><td><input name="new_value" placeholder="Добавить новое"/></td><td></td></tr>
				</tbody>
			</table>
			<input type="submit" value="Сохранить"/>
		</form>
		<?php }?>
		
		<form method="POST">
			<input type="hidden" name="directorytype" value="regions"/>
			<table>
				<thead>
					<tr>
						<th colspan="6">Регионы</th>
					</tr>
					<tr>
						<td>ID</td>
						<td>Название</td>
						<td>Код</td>
						<td>Забор</td>
						<td>Доставка</td>
						<td>&#10003;</td>
					</tr>
				</thead>
				<tbody>
				<?php
				if(isset($_POST['new_value']) && trim($_POST['new_value'])!='' && isset($_POST['directorytype']) && $_POST['directorytype']=='regions')
				{
					$q = "INSERT INTO regions (value, code, reception, delivery) VALUES ('".prepStr($_POST['new_value'])."', ".intval($_POST['new_code']).", ".intval($_POST['new_reception']).", ".intval($_POST['new_delivery']).")";
					query($q);
					if(affected_rows()>0)
					{
						logevent($connect, EVENT_TYPE_INFO, 'directories', 'Добавлено значение в справочник Регионы: '.htmlspecialchars($_POST['new_value']).
									'. Код: '.intval($_POST['new_code']).'. Забор: '.(intval($_POST['new_reception'])?'да':'нет').
									'. Доставка: '.(intval($_POST['new_delivery'])?'да':'нет').'.', insert_id(), null);
						$message .= '<p class="message">В справочник &laquo;Регионы&raquo; успешно добавлена строка.';
					}
					else
						$message .= '<p class="message">Произошла ошибка при добавлении строки в справочник &laquo;Регионы&raquo;.';
				}
				$res = $connect->query("SELECT * FROM regions");
				while($row=$res->fetch_assoc())
				{
					if($_POST['directorytype'] == 'regions')
					{
						$q='';
						$log = 'Обновлена строка справочника Регионы.';
						$fields = Array('value'=>'Название', 'code'=>'Код');
						foreach($fields as $field=>$caption)
						{
							if(isset($_POST[$field][intval($row['id'])]) && $_POST[$field][intval($row['id'])]!=$row[$field])
							{
								$val = $_POST[$field][intval($row['id'])];
								$q .= (empty($q) ? '' : ', ')." $field = '".prepStr($val)."'";
								$log.=' '.$caption.': '.prepStr(htmlspecialchars($val)).'.';
								$row[$field] = $val;
							}
						}
						$flags = Array('reception'=>'Забор', 'delivery'=>'Доставка', 'enabled'=>'Доступно');
						foreach($flags as $field=>$caption)
						{
							$en = (isset($_POST[$field][$row['id']]) ? $_POST[$field][$row['id']] : '0');
							if(isset($_POST['value'][$row['id']]) && $en != $row[$field])
							{
								$q .= (empty($q) ? '' : ', ')." $field = '$en'";
								$log.=' '.$caption.': '.($en?'да':'нет').'.';
							}
							$row[$field] = $en;
						}
						if(!empty($q))
						{
							$q = "UPDATE regions SET".$q.' WHERE id='.$row['id'];
							if($connect->query($q))
							{
								foreach(array_merge($fields, $flags) as $field)
									$row[$field] = $_POST[$field][$row['id']];
								logevent($connect, EVENT_TYPE_INFO, 'directories', $log, $row['id'], null);
								$message .= '<p class="message">Строка '.$row['id'].' справочника &laquo;Регионы&raquo; успешно обновлена.';
							}
							else
								$message .= '<p class="message">Произошла ошибка при попытке обновления значения строки '.$row['id'].' справочника &laquo;Регионы&raquo;.';
						}
						
					}
					echo '<tr>';
					echo '<td>'.$row['id'].'</td><td><input type="text" name="value['.$row['id'].']" value="'.htmlspecialchars($row['value']).'" maxlength="30"/></td>'.
						 '<td><input type="number" name="code['.$row['id'].']" value="'.$row['code'].'" min="0" max="99"/></td>'.
						 '<td><input type="checkbox" value="1" name="reception['.$row['id'].']"'.($row['reception'] ? ' checked' : '').'/></td>'.
						 '<td><input type="checkbox" value="1" name="delivery['.$row['id'].']"'.($row['delivery'] ? ' checked' : '').'/></td>'.
						 '<td><input type="checkbox" value="1" name="enabled['.$row['id'].']"'.($row['enabled'] ? ' checked' : '').'/></td>';
					echo '</tr>';
				}
				?>
				
					<tr>
						<td>+</td><td><input type="text" name="new_value" placeholder="Добавить новый регион" maxlength="30"/></td>
						<td><input type="number" name="new_code" value="" min="0" max="99"/></td>
						<td><input type="checkbox" value="1" name="new_reception"/></td>
						<td><input type="checkbox" value="1" name="new_delivery"/></td>
						<td></td>
					</tr>
				</tbody>
			</table>
			<input type="submit" value="Сохранить"/>
		</form>
		
		
		
		<?="<div class=\"message\" onclick=\"this.style.display='none';\">$message</div>"?>
		</div>
<?php
 //phpinfo(32);
 //закрываем соединение с БД
 $connect->close();
 include_once ("../inc/footer.php");
?>