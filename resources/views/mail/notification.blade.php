<div>Ваша анкета на портале bazabab.ru {{$data->result}}</div>

@if ($data->good) 
<div>Теперь Ваша анкета видна всем пользователям на карте</div>
@else
<div>Причина такого решения :</div>
<div>{{$data->reason}}</div>
@endif
<div>
</div>

<i> Тут можно какую нибудь приписку оставить</i>