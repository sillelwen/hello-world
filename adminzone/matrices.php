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
		<title>Администрирование - Матрицы</title>
		<script>
		function check(e){
		  var chk = e.target.getElementsByTagName("input")[0];
		  chk.checked = !chk.checked;
		}
			
		function attachClicks(){
			var i;
			var els = document.getElementsByTagName('td');


			for(i=0 ; i<els.length ; i++){
			  els[i].addEventListener("click", check, false);
			}
		};
		</script>
	</head>
	<body onload="attachClicks();">
		<?php include_once ("../inc/header.php");?>
		<h3>Матрицы</h3>
		<p><a href="../adminzone.php">Администрирование</a>&nbsp;&gt;&nbsp;<a href="directories.php">Справочники</a>&nbsp;&gt;&nbsp;Матрицы</p>
		<p>Установите галочки в необходимых местах и нажмите «Сохранить».</p>
		<p>Внимание! Снятие некоторых отметок может сильно нарушить функционирование системы!</p>
		<div class="tableformpart">
		<?php
		$contractpaymenttypes = ['name'=>'contractpaymenttypes', 'header'=>'Доступность типов оплаты',
						'directoryX'=>'contracttypes', 'fieldX'=>'contracttype', 'headerX'=>'Тип договора',
						'directoryY'=>'paymenttypes', 'fieldY'=>'paymenttype', 'headerY'=>'Тип оплаты',
						'fieldZ'=>'enabled'];
		$waybilltypes = ['name'=>'waybilltype', 'header'=>'Упрощённая накладная',
						'directoryX'=>'contracttypes', 'fieldX'=>'contracttype', 'headerX'=>'Тип договора',
						'directoryY'=>'paymenttypes', 'fieldY'=>'paymenttype', 'headerY'=>'Тип оплаты',
						'fieldZ'=>'simple'];
		$editablefields = ['name'=>'editablefields', 'header'=>'Редактируемость полей',
						'directoryX'=>'orderstatuses', 'fieldX'=>'status', 'headerX'=>'Статус',
						'directoryY'=>'fields', 'fieldY'=>'field', 'headerY'=>'Поле',
						'fieldZ'=>'editable'];
		$matrices = [$contractpaymenttypes, $waybilltypes, $editablefields];
		foreach($matrices as $matrix)
		{
			$values = array(); $cols = array();
			
			$onupdate = (isset($_POST['matrixtype']) && $_POST['matrixtype']==$matrix['name']);
			//получение данных и отображение матрицы
			$query = 'SELECT * FROM '.$matrix['name'];
			$res = $connect->query($query);
			while($row = $res->fetch_assoc())
				$values[$row[$matrix['fieldX']]][$row[$matrix['fieldY']]] = $row[$matrix['fieldZ']];
		?>
		<form method="POST">
			<input type="hidden" name="matrixtype" value="<?=$matrix['name']?>"/>
			
			<h4><?=$matrix['header']?></h4>
			<table class="matrix">
				<thead>
			<?php
				$query = 'SELECT id, value FROM '.$matrix['directoryX'];
				$res = $connect->query($query);
				echo '<tr><th rowspan="2">'.$matrix['headerY'].'</th><th colspan="'.($res->num_rows).'">'.$matrix['headerX'].'</th></tr>';
				while($row=$res->fetch_assoc())
					$cols[$row['id']]=$row['value'];
				echo '<tr>';
				foreach($cols as $id=>$value)
					echo '<th>'.htmlspecialchars($value).'</th>';
				echo '</tr>';
			?>
				</thead>
				<tbody>
			<?php
			$query = 'SELECT id, value FROM '.$matrix['directoryY'];
			$res = $connect->query($query);
			while($row=$res->fetch_assoc())
			{
				echo '<tr><th>'.htmlspecialchars($row['value']).'</th>';
				foreach($cols as $id=>$value)
				{
					$chk = isset($values[$id][$row['id']]) ? $values[$id][$row['id']] : 0;
					if($onupdate)
					{
						$postChk = (isset($_POST['chk'][$id][$row['id']])) ? $_POST['chk'][$id][$row['id']] : 0;
						if($chk!=$postChk)
						{
							$query = 'REPLACE INTO '.$matrix['name'].' ('.$matrix['fieldX'].', '.$matrix['fieldY'].', '.$matrix['fieldZ'].') VALUES('.$id.', '.$row['id'].', '.$postChk.')';
							//echo $query;
							query($query);
							if(affected_rows()>0)
							{
								$log = 'Обновлена матрица '.$matrix['header'].'. '.$matrix['headerX'].': '.$value.'. '.$matrix['headerY'].': '.$row['value'].'. Значение: '.($postChk?'да':'нет').'.';
								logevent($connect, EVENT_TYPE_INFO, 'matrices', $log, intval($id.'0'.$row['id']), null);
								$chk=$postChk;
							}
							else
								$message="<p class=\"message\">Произошла ошибка при обновлении значения.</p>";
						}
					}
					echo '<td><input type="checkbox" value="1" name="chk['.$id.']['.$row['id'].']"'.($chk==1 ? ' checked' :'').'/></td>';
				}
				echo '</tr>';
			}
			?>
				</tbody>
			</table>
			<input type="submit" value="Сохранить"/>
		</form>
		<?php }?>
				
		
		<?="<div class=\"message\" onclick=\"this.style.display='none';\">$message</div>"?>
		</div>
<?php
 //phpinfo(32);
 //закрываем соединение с БД
 $connect->close();
 include_once ("../inc/footer.php");
?>