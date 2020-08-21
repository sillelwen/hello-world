	<table class="waybill">
		<thead>
			<col width="17%"/>
			<col width="13%"/>
			<col width="19%"/>
			<col width="17%"/>
			<col width="17%"/>
			<col width="17%"/>
		</thead>
		<tbody>
			<tr>
				<td colspan="2" class="nr"><?=$rowID?></td>
				<td>Номер заказа</td>
				<td colspan="3"><?=$waybill?></td>
			</tr>
			<tr>
				<td colspan="6">
					ОТ КОГО (отправитель)
				</td>
			</tr>
			<tr>
				<td colspan="2">Наименование компании&#8209;отправителя</td>
				<td colspan="4"><?=$_SESSION['companyName']?></td>
			</tr>
			<tr>
				<td>Регион</td>
				<td colspan="2"><?=$receptionRegion?></td>
				<td class="nospace">Город/нас. пункт</td>
				<td colspan="2"><?=$receptionCity?></td>
			</tr>
			<tr><td colspan="6"></td></tr>
			<tr>
				<td colspan="4">Заказ передан</td>
				<td class="date">Дата</td>
				<td class="date"></td>
			</tr>
			<tr class="high">
				<td>ФИО представителя отправителя</td>
				<td colspan="2"><?=$receptionContactFIO?></td>
				<td>Подпись</td>
				<td colspan="2">&nbsp;</td>
			</tr>
			<tr><td colspan="6" class="bold"></td></tr>
			<tr>
				<td colspan="6" class="bold">КОМУ (получатель)</td>
			</tr>
			<tr>
				<td colspan="2">Наименование компании&#8209;получателя (для&nbsp;юрлиц)</td>
				<td colspan="4"><?=$addressee?></td>
			</tr>
			<tr>
				<td colspan="2">ФИО получателя</td>
				<td colspan="4"><?=$contactFIO?></td>
			</tr>
			<tr>
				<td colspan="2">Телефон получателя</td>
				<td colspan="4"><?=$contactPhone?></td>
			</tr>
			<tr>
				<td>Регион</td>
				<td colspan="2"><?=$deliveryRegion?></td>
				<td class="nospace">Город/нас. пункт</td>
				<td colspan="2"><?=$deliveryCity?></td>
			</tr>
			<tr>
				<td colspan="2">Адрес получателя</td>
				<td colspan="4"><?=$address?></td>
			</tr>
			<tr class="high">
				<td>Комментарий:</td>
				<td colspan="5"><?=str_replace("\n", "<br/>",$note)?></td>
			</tr>
			<tr><td colspan="6" class="bold"></td></tr>
			<tr>
				<td colspan="6" class="bold">ИНФОРМАЦИЯ ОБ ОТПРАВЛЕНИИ</td>
			</tr>
			<tr>
				<td colspan="2">Плановая дата доставки</td>
				<td class="date"><?=is_null($deliveryDate) ? $deliveryDay : date('d.m.Y', strtotime($deliveryDate))?></td>
				<td colspan="2">Плановый интервал доставки</td>
				<td><?=$timeSpan?></td>
			</tr>
			<tr><td colspan="6"></td></tr>
			<tr>
				<td colspan="3" class="right">Вес, кг</td>
				<td><?=$weight?></td>
				<td>Кол-во мест</td>
				<td><?=$places?></td>
			</tr>
			<tr><td colspan="6"></td></tr>
			<tr>
				<td>Габариты, см</td>
				<td colspan="2">
				<?=$dimensions?>
				</td>
				<td class="sum"><strong>К оплате</strong></td>
				<td class="sum" colspan="2"><big><?=number_format($sum, 2, ',', ' ')?></big> рублей</td>
			</tr>
			<tr><td colspan="6"></td></tr>
			<tr>
				<td colspan="4">Заказ получен, комплект и внешний вид товара проверен,<br/>претензий не имею</td>
				<td class="date">Дата</td>
				<td></td>
			</tr>
			<tr class="high bold">
				<td>ФИО получателя</td>
				<td colspan="2"></td>
				<td>Подпись</td>
				<td colspan="2"></td>
			</tr>
		</tbody>
	</table>