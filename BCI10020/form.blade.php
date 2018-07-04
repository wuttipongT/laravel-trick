<div class="form-group row">
	<label for="" class="col-sm-3 text-right">MODEL CODE : </label>
	<div class="col-sm-9">
		@if ($disabled)
			{!! Form::text('BC15_MODELCD', null, ['id'=>'BC15_MODELCD', 'class' => 'form-control', 'onkeyup' => 'this.value = this.value.toUpperCase()']) !!}
		@else
			{!! Form::text('BC15_MODELCD', null, ['id'=>'BC15_MODELCD', 'class' => 'form-control', 'onkeyup' => 'this.value = this.value.toUpperCase()', 'readonly'=>'readonly']) !!}
		@endif
	</div>
</div>

<div class="form-group row">
	<label for="" class="col-sm-3 text-right">TOPPICS CHECK : </label>
	<div class="col-sm-9">
		{!! Form::select('BC15_TOPICSCHK', $BC_TOPPICCTL, null,['id'=>'BC15_TOPICSCHK', 'class' => 'form-control', 'disabled' => 'disabled', 'onkeyup' => 'this.value = this.value.toUpperCase()']) !!}
	</div>
</div>

<div class="form-group row">
	<label for="" class="col-sm-3 text-right">FORMAT TYPE : </label>
	<div class="col-sm-9">
		@if ($disabled)
		{!! Form::select('BC15_FORMATTYP', ['' => '-- Select --', 'FI' => 'FI', 'SN' => 'SN', 'KEEP' => 'KEEP'], null, ['id'=>'BC15_FORMATTYP', 'class' => 'form-control', 'disabled' => 'disabled', 'onkeyup' => 'this.value = this.value.toUpperCase()']) !!}
		@else
		{!! Form::select('BC15_FORMATTYP', ['' => '-- Select --', 'FI' => 'FI', 'SN' => 'SN', 'KEEP' => 'KEEP'], null, ['id'=>'BC15_FORMATTYP', 'class' => 'form-control', 'onkeyup' => 'this.value = this.value.toUpperCase()']) !!}		
		@endif
	</div>
</div>

<div class="form-group row">
	<label for="" class="col-sm-3 text-right">FIXED VALUE : </label>
	<div class="col-sm-9">
		@if ($disabled)
			{!! Form::text('BC15_FIXEDVAL', null, ['id'=>'BC15_FIXEDVAL', 'class' => 'form-control', 'readonly' => 'readonly', 'onkeyup' => 'this.value = this.value.toUpperCase()']) !!}
		@else
			@if($BC_MODELTOPIC->BC15_FORMATTYP != 'SN')
				{!! Form::text('BC15_FIXEDVAL', null, ['id'=>'BC15_FIXEDVAL', 'class' => 'form-control', 'onkeyup' => 'this.value = this.value.toUpperCase()']) !!}
			@else
				<div class="input-group input-group">
					{!! Form::text('BC15_FIXEDVAL', null, ['id'=>'BC15_FIXEDVAL', 'class' => 'form-control', 'onkeyup' => 'this.value = this.value.toUpperCase()']) !!}
					<div class="input-group-btn">
					  <div class="btn btn-default" name="btnFixed" value="SN">&nbsp;<i class="fa fa-edit" value="SN"></i>&nbsp;</div>
					</div>
				</div>
			@endif
		@endif
	</div>
</div>

<div class="form-group row">
	<label for="" class="col-sm-3 text-right">LENGH LOWER : </label>
	<div class="col-sm-9">
		@if ($disabled)
			{!! Form::number('BC15_LENGTHLOWER', null, ['id'=>'BC15_LENGTHLOWER', 'class' => 'form-control', 'readonly' => 'readonly']) !!}
		@else
			{!! Form::number('BC15_LENGTHLOWER', null, ['id'=>'BC15_LENGTHLOWER', 'class' => 'form-control']) !!}
		@endif
	</div>
</div>

<div class="form-group row">
	<label for="" class="col-sm-3 text-right">LENG UPPER : </label>
	<div class="col-sm-9">
		@if ($disabled)
			{!! Form::number('BC15_LENGTHUPPER', null, ['id'=>'BC15_LENGTHUPPER', 'class' => 'form-control', 'readonly' => 'readonly']) !!}
		@else
			{!! Form::number('BC15_LENGTHUPPER', null, ['id'=>'BC15_LENGTHUPPER', 'class' => 'form-control']) !!}
		@endif
	</div>
</div>

{!! Form::hidden('BC15_ID') !!}

{!! Form::hidden('BC15_PROGRAMID', 'BCI10020') !!}

{!! Form::hidden('BC15_PREFIX') !!}
{!! Form::hidden('BC15_SUBFIX') !!}
{!! Form::hidden('BC15_POSTFIX') !!}
