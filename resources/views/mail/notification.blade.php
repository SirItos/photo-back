<div>Ваша анкета на портале bazabab.ru {{$data->result}}</div>

@if ($data->good)
<div>Теперь Ваша анкета видна всем пользователям на карте</div>
@else
<div>Причина такого решения :</div>
<div>{{$data->reason}}</div>
@endif
<div>
</div>

<table style="margin-top:50px" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td> <img src="https://back.bazabab.ru/storage/mail_logo.jpg" width="100" /></td>
    <td> <span style="margin-left:10px"><i> Люди-самый ценный ресурс. Спасибо, что вы с нами!</i></span></td>
  </tr>
</table>