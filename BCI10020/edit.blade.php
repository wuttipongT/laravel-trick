{!! Form::model($BC_MODELTOPIC, ['method' => 'PATCH', 'route' => ['BCI10020.update', $BC_MODELTOPIC->BC15_ID], 'onsubmit' => 'return false;']) !!}
	@include('BASE.BCI10020.form', ['disabled' => false])
{!! Form::close() !!}