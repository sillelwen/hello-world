<div id="header" generatedOn="<?=date('d-m-Y H:i:s')?>">
<?php
switch($_SERVER["HTTP_HOST"])
{
	case 'lk':
		$logoclass = 'class="logo_local"';
		break;
	case 'testlk.de-express.ru':
		$logoclass = 'class="logo_test"';
		break;
	default:
		$logoclass = '';
		break;		
}
?>
			<a href="/" id="logo"<?=$logoclass?>">
			</a>
			<div id="contacts_flex_container">
				<div class="contacts"><a href="mailto:logist@dedal-express.ru">&#9993; logist@dedal-express.ru </a></div>
				<div class="contacts"><a href="tel:+79112194916">&#128222; +7-911-219-49-16</a></div>
			</div>
		</div>
		<div id="navpane">
			<div><a href="/">На главную</a></div>
<?php
		if(isset($_SESSION['userid']))
		{
		?>
			<div>Вы вошли как: <strong><?=htmlspecialchars($_SESSION['companyName'])?></strong></div>
			<div><a href="/logout.php">Выйти</a></div>
		<?php
		}
		?>
		</div>
		<div id="contents">
