{{--
SYSTEM NAME : PTAC SYSTEM
PROGRAM     : BCI10020				

Version   Date          CREATE BY     Detail
1.        2018-02-05    th.wuttipong  1. Initial Program
--}}

@php
	use App\LTEMenu;
@endphp

@extends('adminlte::page')
@section('title', 'BCI10020')
@section('css')
<style type="text/css">
/*[class*="col-"] {
    padding-top: 1rem;
    padding-bottom: 1rem;
}*/
.ui-autocomplete {
  z-index: 215000000 !important;
}
.login-dialog .modal-dialog {
    width: 400px;
}
.flex{
	display: -webkit-box;
	display: -moz-box;
	display: -ms-flexbox;
	display: -webkit-flex;
	display: flex;

	-webkit-flex-flow: row wrap;
	justify-content: flex-end;
}
input.upper { text-transform: uppercase; }
.alert{
	margin-bottom: 0;
}
</style>
@endsection

@section('content_header')
	{{LTEMenu::getMenu('BCI10020')}}
@endsection

@section('content')

<div class="box box-solid box-default">
	<div class="box-header"></div>
	<div class="box-body">
		<div class="pull-left text-left">
		
		</div>
		<div class="pull-right">
			<div class="flex">
				<div>
					<div class="input-group input-group" style="width: 400px;margin-right: 4px;">
						<input type="text" name="txtSearch" class="form-control pull-right" placeholder="Search">

						<div class="input-group-btn">
						  <button type="button" class="btn btn-primary" name="btnSearch">&nbsp;<i class="fa fa-search"></i>&nbsp;</button>
						</div>
					</div>				
				</div>				
				<div>
					<button class="btn btn-success" name="btnReport">Report</button>
					<button class="btn btn-success" name="create">Create</button>					
				</div>
			</div>
		</div>
	</div>
</div>		

<div class="row">
	<div class="col-lg-12">
		<div class="box box-solid box-info">
			<div class="box-header">Dislay Information</div>
			<div class="box-body">
				<table id="datatable" class="table table-bordered table-hover"></table>
			</div>
		</div>		
	</div>
</div>

@endsection

@section('js')
<!-- <script src="{{ asset('js/RegNumericRange.min.js') }}"></script> -->
<script type="text/javascript">
'use strict'

var $window = $(window)
var calcDataTableHeight = () => Math.round($window.height() * 0.58)
var option = {
	"sScrollY" : calcDataTableHeight(),
	'sScrollX'			: '100%',
	'sScrollXInner' 	: '100%',
    // "sPaginationType" : false,
     "lengthMenu" : [
		[10, 25, 50, 100, -1],
		[10, 25, 50, 100, "All"]
	],
	"iDisplayLength": 25,
    // "ordering" : false,
    // "sDom": 'l<"toolbar_D">frtip', //l<"toolbar_D">frti
    "sDom": 'l<"toolbar_D">Bfrtip', //frtip
    "columns" : [
    	{'title': 'MODEL CODE', 'data': 'BC15_MODELCD'},
    	{'title': 'TOPPICS CHECK', 'data': 'BC15_TOPICSCHK'},
    	{'title': 'FORMAT TYPE', 'data': 'BC15_FORMATTYP'},
    	{'title': 'PREFIXED', 'data': 'BC15_PREFIX'},
    	{'title': 'SUBFIXED', 'data': 'BC15_SUBFIX'},
    	{'title': 'POSTFIXED', 'data': 'BC15_POSTFIX'},
    	{'title': 'FIXED VALUE', 'data': 'BC15_FIXEDVAL'},
    	{'title': 'LENGH LOWER', 'data': 'BC15_LENGTHLOWER'},
    	{'title': 'LENG UPPER', 'data': 'BC15_LENGTHUPPER'},
    	{'data': null, 'width': '150px', 'class':'text-center',
    		render: (data, type, row) => {
    			let template = `
    				<button class="btn btn-info" name="view"><i class="fa fa-fw fa-eye"></i></button>
    				<button class="btn btn-warning" name="edit"><i class="fa fa-fw fa-edit"></i></button>
    				<button class="btn btn-danger" name="del"><i class="fa fa-fw fa-trash-o"></i></button>
    			`
    			return template
    		}
    	}
    ],
    "createdRow": function( nRow, aData, iDisplayIndex ){
    	$( nRow ).find(`button[name=view]`).on('click', {aData: aData}, onView)
    	$( nRow ).find(`button[name=edit]`).on('click', {aData: aData}, onEdit)
    	$( nRow ).find(`button[name=del]`).on('click', {aData: aData}, onDel)
    }
}

;(function(document, window, index){

	var dt = $('#datatable').dataTable( option )
	var onNext = ( event ) => {
		if( event.keyCode == 13 ){
			let dialog = event.data.dialog

			dialog.$modal.off('keyup')
			$(event.target).closest('.form-group').next().find('input').focus()

		    setTimeout(function (){
				dialog.$modal.on('keyup', {dialog: dialog}, dialogEvent)
	        }, 600)

		}
	}
	$('button[name=create]').on('click', () => {
		$.get(`{{ route('BCI10020.create') }}`, applyForm, 'text').fail( onFail )

		function applyForm( resHtml ){

			var form = $( $.parseHTML( resHtml ) )
			let dialog = BootstrapDialog.show({
	             title: '<i class="fa fa-pencil"></i> Create',
	             type: BootstrapDialog.TYPE_SUCCESS,
	             message: form,
	             buttons: [{
	                label: 'Save',
	                cssClass: 'btn-primary',
	                hotkey: 13, // Enter.
	                action: save
	             },
	             {
	                label: 'Cancel',
	                cssClass: 'btn-default',
	                //hotkey: 13, // Enter.
	                action: function( dialog ) {
	                    dialog.close()
	                }
	             }
	           ],
	           onshown: (v) => {
			        form.find('input[name=BC15_MODELCD]').focus()    	
	           }
	         })

			form.find('input[name=BC15_MODELCD]').autocomplete({
		      source: function( request, response ) {
				
				let url = '{{ route("BCI10020.destroy", ":id") }}'
				url = url.replace(':id', request.term)

		        $.ajax( {
		          url: url,
		          dataType: 'json',
		          data: {
		            term: request.term,
		            type: 'autocomplete',
		            max: 20
		          },
		          success: function( res ) {
		            response( res.data.map( d => d.BC10_MODELCD.toUpperCase() ) )
		          },
		          error: onFail
		        })
		      },
		      minLength: 1,
		      select: function( event, ui ) {
		        // log( "Selected: " + ui.item.value + " aka " + ui.item.id );
	
		        form.find('*[readonly=readonly]').removeAttr('readonly')
		        form.find('*[disabled=disabled]').removeAttr('disabled')
		        form.find('input[name=BC15_TOPICSCHK]').focus()
		      }
		    })

			form.find('input[name=BC15_MODELCD]').on('keydown', (event) => {
				if(event.keyCode == 13){
					dialog.$modal.off('keyup')

					let url = '{{ route("BCI10020.destroy", ":id") }}'
					url = url.replace(':id', event.target.value)
					let box = {
       					term: event.target.value,
		            	type: 'autocomplete',
		            	max: 1						
					}
					$.get(url, box, getModel, 'json')

					function getModel(response){
						let d = response.data

						if(d.length <= 0)
							errorAlert('<i class="fa fa-warning"></i> Error information', `${event.target.value} Model not exists! Please recheck`, undefined, onClose)

						else{
			        		form.find('[name=BC15_TOPICSCHK]').prop('readonly', false).focus()					
						}

						function onClose(){
						    setTimeout(function (){
								$(event.target).focus().select()							
					        }, 350)		
						}
					}

				}
			})

			form.find('[name=BC15_TOPICSCHK]').on('change', {form: form, dialog: dialog}, TOPICSCHK)
			form.find('[name=BC15_FORMATTYP]').on('change', {form: form, onNext: onNext, dialog: dialog}, popupFixed)
			form.find('input[name=BC15_PREFIX]').on('keydown', {dialog: dialog}, onNext)
			form.find('input[name=BC15_SUBFIX]').on('keydown', {dialog: dialog}, onNext)
			form.find('input[name=BC15_POSTFIX]').on('keydown', {dialog: dialog}, onNext)
			form.find('input[name=BC15_FIXEDVAL]').on('keydown', {dialog: dialog}, onNext)
			form.find('input[name=BC15_LENGTHLOWER]').on('keydown', {dialog: dialog}, onNext)

			form.find('input[name=BC15_LENGTHUPPER]').on('change', {form: form}, onLengthUpper)

			function save( dialog ){
				// if( 
				// 	!form.find('input[name=BC15_MODELCD]').val() ||
				// 	!form.find('input[name=BC15_TOPICSCHK]').val() ||
				// 	form.find('input[name=BC15_MODELCD]') == '-1' ||
				// 	!form.find('input[name=BC15_FIXEDVAL]').val()
				//  )
				// 	return
				let data = form.serializeArray()
				$.post(form.attr('action'), data, store,'json')
				.fail( onFail )

				function store( response ){
					if(response.success){
						toastr['success']( response.message )
						dataTableView()
						dialog.close()
						console.log( response.data )
					}
				}
			}
		}

	})

	$('button[name=btnSearch]').on('click', (event) => {
		let box = {
			txtSearch: $('input[name=txtSearch]').val()
		}

		dataTableView( box )
	})

	$('input[name=txtSearch]').on('keydown', event => {
		if(event.keyCode == 13){
			let box = {
				txtSearch: $(event.target).val()
			}

			dataTableView( box )
		}
	})

	$('button[name=btnReport]').on('click', () => {
		window.location.href = `{{ route('BCI10020.show', -1) }}?type=report&txtSearch=${$('input[name=txtSearch]').val()}`
	})

	dataTableView()

	function onFail(jqXhr, json, errorThrown){
        var errors = jqXhr.responseJSON
        var errorsHtml= ''
        $.each( errors, function( key, value ) {
        	if(typeof(value) == 'string')
        		errorsHtml += '<li>' + value + '</li>'
        	else
            	errorsHtml += '<li>' + value[0] + '</li>'
        })
        errorAlert( "Error " + jqXhr.status, errorsHtml || "Error " + jqXhr.status +': '+ errorThrown )
        toastr.error( errorsHtml , "Error " + jqXhr.status +': '+ errorThrown)		
	}

	function dataTableView(box = {}){
		$.get(`{{ route('BCI10020.show', -1) }}`, box, show, 'json').fail( onFail )

		function show( response ){

			if( dt.fnSettings().aoData.length > 0 )
				dt.fnClearTable()

			if( response.length > 0 )
				dt.fnAddData( response )
		}
	}

	function onLengthUpper( event ){
		let form = event.data.form
		let txtReplace = event.target.value.length > 0 ? genAlphabet(event.target.value.length) : 'nnn'
		let format = form.find('input[name=BC15_FIXEDVAL]').val()
		let len = event.target.defaultValue.length > 0 ? event.target.defaultValue.length : 3
		let pattern = genAlphabet(len)
		form.find('input[name=BC15_FIXEDVAL]').val( format.replaceAll(pattern, txtReplace) )
	}

	function popupFixed( event ){
		event.preventDefault()
		let [form, onNext, dialogMain] = [event.data.form, event.data.onNext, event.data.dialog]
		let val = event.type == 'change' ? event.target.value : $(event.target).attr('value')
		// dialogMain.$modal.off('keyup')
		let input = $(`{!! Form::text('BC15_FIXEDVAL', null, ['id'=>'BC15_FIXEDVAL', 'class' => 'form-control', 'onkeyup' => 'this.value = this.value.toUpperCase()']) !!}`)
		let inputFn = (readonly) => readonly ? input.prop('readonly', true) : input.prop('readonly', false)
		let wrapper =  (input) => `
			<label for="" class="col-sm-3 text-right">FIXED VALUE : </label>
			<div class="col-sm-9">			
				${input[0].outerHTML}
			</div>
		`
		let inputGroup = $(`
		<label for="" class="col-sm-3 text-right">FIXED VALUE : </label>
		<div class="col-sm-9">			
			<div class="input-group input-group">
				{!! Form::text('BC15_FIXEDVAL', null, ['id'=>'BC15_FIXEDVAL', 'class' => 'form-control', 'onkeyup' => 'this.value = this.value.toUpperCase()', 'readonly'=>'readonly']) !!}
				<div class="input-group-btn">
				  <div class="btn btn-default" name="btnFixed" value="SN">&nbsp;<i class="fa fa-edit" value="SN"></i>&nbsp;</div>
				</div>
			</div>
		</div>
		`)

		inputGroup.find('[name=btnFixed]').on('click', {form: form, onNext: onNext, dialog: dialogMain}, popupFixed)

		if( val == 'SN' ){
			form.find('input[name=BC15_LENGTHLOWER]').prop('readonly', false)
			form.find('input[name=BC15_LENGTHUPPER]').prop('readonly', false)

			// form.find('input[name=BC15_FIXEDVAL]').prop('readonly', true)
			form.find('input[name=BC15_FIXEDVAL]').closest('.form-group').empty().append(inputGroup)
			let [prefix, subfix, postfix] = [
				form.find('input[name=BC15_PREFIX]').val(),
				form.find('input[name=BC15_SUBFIX]').val(),
				form.find('input[name=BC15_POSTFIX]').val()
			]

			let hotKey = function( event ){
				if(event.keyCode == 13){
	            	let $modalBody = event.data.v.$modalBody
	            	let btn = $modalBody.find('button[name=ok]').focus().click()
	            }
			}

	    	var dialog = new BootstrapDialog({
	            message: function(dialogRef){
	                var $message = $(`
	                				                	
						<div class="form-group row">
							<div class="col-sm-12">
								<input type="text" class="form-control" name="BC15_PREFIX" placeholder="PREFIXED" value="${prefix}" onkeyup= "this.value = this.value.toUpperCase()">
							</div>
						</div>

						<div class="form-group row">
							<div class="col-sm-12">
								<input type="text" class="form-control" name="BC15_SUBFIX" placeholder="SUBFIXED" value="${subfix}" onkeyup= "this.value = this.value.toUpperCase()">
							</div>
						</div>

						<div class="form-group row">
							<div class="col-sm-12">
								<input type="text" class="form-control" name="BC15_POSTFIX" placeholder="POSTFIXED" value="${postfix}" onkeyup= "this.value = this.value.toUpperCase()">
							</div>
						</div>

						<div class="form-group row">
							<div class="col-sm-12">
								<div class="alert alert-info text-center">
									<h1 class="format-first" style="display:inline-block">${prefix}</h1>
									<h1 class="format-second" style="display:inline-block">${subfix}</h1>
									<h1 class="format-third" style="display:inline-block">${postfix}</h1>											
								</div>
								<small><strong>Notice:</strong> Input SN at least one value.</small>
								<p><small>SN is running number.</small></p>
							</div>
						</div>

						<div class="form-group row">
							<div class="col-sm-12 text-center">
								<button class="btn btn-primary" name="ok">Submit</button>
								<button class="btn btn-default" name="cancel">Cancel</button>
							</div>
						</div>

	                `)
	    
	                let bindResult = function( event ){
	                	$message.find( event.data.id ).text(event.target.value.toUpperCase())

	                	if(event.target.value.toUpperCase() == 'SN'){
	                		if(form.find('input[name=BC15_LENGTHLOWER]').val()){
	                			let txtReplace = genAlphabet(form.find('input[name=BC15_LENGTHLOWER]').val().length)
	                			$message.find( event.data.id ).text( txtReplace )
	                		}else
	                			$message.find( event.data.id ).text('nnn')
	                	}
	                }

	                $message.find('[name=BC15_PREFIX]').on('input', {id: '.format-first'}, bindResult)
	                $message.find('[name=BC15_SUBFIX]').on('input', {id: '.format-second'}, bindResult)
	                $message.find('[name=BC15_POSTFIX]').on('input', {id: '.format-third'}, bindResult)

	                $message.find('[name=BC15_PREFIX]').on('keydown', {dialog: dialog}, onNext)
	                $message.find('[name=BC15_SUBFIX]').on('keydown', {dialog: dialog}, onNext)
	                $message.find('[name=BC15_POSTFIX]').on('keydown', {dialog: dialog}, (event) => {
	                	onNext(event)
	                	if(event.keyCode == 13){
	                	 	$message.find('button[name=ok]').focus().select()
	                	}
	                	
	                })

	                $message.find('button[name=ok]').on('click', (event) => {
	                	event.preventDefault()
	               		if(
	               			!$message.find('[name=BC15_PREFIX]').val() ||
	               			!$message.find('[name=BC15_SUBFIX]').val() ||
	               			!$message.find('[name=BC15_POSTFIX]').val()
	               		)
	               			return

	               		let arr = [];
	               		$message.find('input').each( (indx, elm) => {
	               			arr.push( elm.value )
	               		})
	               		
	               		if($.inArray('SN', arr) == -1){
	               			toastr.error("Please enter value 'SN' at least one value!")
	               			errorAlert('<i class="fa fa-warning"></i> Confirm information!' , `Please enter value 'SN' at least one value!`)
	               			return 
	               		}
	                	form.find('input[name=BC15_PREFIX]').val( $message.find('[name=BC15_PREFIX]').val() )
	                	form.find('input[name=BC15_SUBFIX]').val( $message.find('[name=BC15_SUBFIX]').val() )
	                	form.find('input[name=BC15_POSTFIX]').val( $message.find('[name=BC15_POSTFIX]').val() )
	                	
	                	
	                	form.find('input[name=BC15_FIXEDVAL]').val( 
	                		$message.find('.format-first').text() + 
	                		$message.find('.format-second').text() +
	                		$message.find('.format-third').text()
	                	 )

	                	dialog.close()

				        setTimeout(function (){
				          form.find('input[name=BC15_LENGTHLOWER]').focus()
				        }, 350)

	                })

	                $message.find('button[name=cancel]').on('click', (event) => {
	                	event.preventDefault()
	                	dialog.close()

	                	setTimeout(function (){
				          form.find('select[name=BC15_FORMATTYP]').focus()
				        }, 350)
	                	
	                })			                
	                // $message.append($button);
	        
	                return $message
	            },
	            closable: true,
	            cssClass: 'login-dialog',
	            // closeByKeyboard: false,
	            //hotkey: 13, Enter.
	            onshown: function( v ){
	            	// $(document).bind('keypress', {v: v}, hotKey)
	            	// v.$modalBody.find('input[value=SN]').focusout()
	            	v.$modalBody.find('[name=BC15_PREFIX]').trigger('input', [{id: '.format-first'}])
	            	v.$modalBody.find('[name=BC15_SUBFIX]').trigger('input', [{id: '.format-second'}])
	            	v.$modalBody.find('[name=BC15_POSTFIX]').trigger('input', [{id: '.format-third'}])
			        v.$modalBody.find('input[name=BC15_PREFIX]').focus()
	    
	            },
	            onhide: function( v ){
	            	// $(document).unbind('keypress', {v: v}, hotKey)
	            }
	        })
	        dialog.realize()
	        dialog.getModalHeader().hide()
	        dialog.getModalFooter().hide()
	        // dialog.getModalBody().css('wdith', '300px');
	        // dialog.getModalBody().css('color', '#fff');
	        dialog.open()
		}else if(val == 'KEEP'){
			// form.find('input[name=BC15_FIXEDVAL]').val('').prop('readonly', true)
			form.find('input[name=BC15_LENGTHLOWER]').val('').prop('readonly', true)
			form.find('input[name=BC15_LENGTHUPPER]').val('').prop('readonly', true)
			// dialogMain.$modalFooter.find('button.btn-primary').focus()

        	form.find('input[name=BC15_PREFIX]').val( '' )
        	form.find('input[name=BC15_SUBFIX]').val( '' )
        	form.find('input[name=BC15_POSTFIX]').val( '' )
        	form.find('input[name=BC15_FIXEDVAL]').val( '' )

			form.find('input[name=BC15_FIXEDVAL]').closest('.form-group').empty().append(wrapper(inputFn(true)))

		}else{
			// form.find('input[name=BC15_FIXEDVAL]').prop('readonly', false).focus()
			form.find('input[name=BC15_LENGTHLOWER]').val('').prop('readonly', true)
			form.find('input[name=BC15_LENGTHUPPER]').val('').prop('readonly', true)
			form.find('input[name=BC15_FIXEDVAL]').closest('.form-group').empty().append(wrapper(inputFn(false)))
			form.find('input[name=BC15_FIXEDVAL]').focus()

			form.find('input[name=BC15_PREFIX]').val( '' )
        	form.find('input[name=BC15_SUBFIX]').val( '' )
        	form.find('input[name=BC15_POSTFIX]').val( '' )
        	form.find('input[name=BC15_FIXEDVAL]').val( '' )
		}

	  //   setTimeout(function (){
			// dialogMain.$modal.on('keyup', {dialog: dialog}, dialogEvent)
   //      }, 600)			
	}

	window.onView = function( event ){
		let d = event.data.aData

		let url = '{{ route("BCI10020.show", ":id") }}'
		url = url.replace(':id', d.BC15_ID)

		$.get(url, show, 'text')
		.fail( onFail)

		function show( response ){
			let form = $.parseHTML( response )

			BootstrapDialog.show({
	             title: '<i class="fa fa-eye"></i> View',
	             type: BootstrapDialog.TYPE_INFO,
	             message: form,
	             buttons: [
		             {
		                label: 'Cancel',
		                cssClass: 'btn-default',
		                //hotkey: 13, // Enter.
		                action: function( dialog ) {
		                    dialog.close()
		                }
		             }
	           ]
	         })
		}
	}

	window.onEdit = function( event ){
		let d = event.data.aData

		let url = '{{ route("BCI10020.edit", ":id") }}'
		url = url.replace(':id', d.BC15_ID)

		$.get(url, edit, 'text')
		.fail( onFail )

		function edit( response ){
			let form = $( $.parseHTML( response ) )

			let dialog = BootstrapDialog.show({
	             title: '<i class="fa fa-fw fa-edit"></i> Edit',
	             type: BootstrapDialog.TYPE_WARNING,
	             message: form,
	             buttons: [{
	                label: 'Update',
	                cssClass: 'btn-primary',
	                hotkey: 13, // Enter.
	                action: save
	             },
	             {
	                label: 'Cancel',
	                cssClass: 'btn-default',
	                //hotkey: 13, // Enter.
	                action: function( dialog ) {
	                    dialog.close()
	                }
	             }
	           ],
	          onshown: () => {
	          	if(form.find('[name=BC15_FORMATTYP]').val() == 'SN')
	          		form.find('input[name=BC15_FIXEDVAL]').prop('readonly', true)

	          	if(form.find('[name=BC15_FORMATTYP]').val() == 'KEEP'){
					form.find('input[name=BC15_FIXEDVAL]').val('').prop('readonly', true)
					form.find('input[name=BC15_LENGTHLOWER]').val('').prop('readonly', true)
					form.find('input[name=BC15_LENGTHUPPER]').val('').prop('readonly', true)	          		
	          	}

	          	if(form.find('[name=BC15_FORMATTYP]').val() == 'FI'){
	          		form.find('input[name=BC15_LENGTHLOWER]').val('').prop('readonly', true)
					form.find('input[name=BC15_LENGTHUPPER]').val('').prop('readonly', true)
	          	}

		        form.find('select[name=BC15_FORMATTYP]').focus()
	          }
	         })

			// form.find('[name=BC15_FORMATTYP]').on('change', {form: form, onNext: onNext, dialog: dialog}, popupFixed)
			form.find('div[name=btnFixed]').on('click', {form: form, onNext: onNext, dialog: dialog}, popupFixed)
			
			form.find('input[name=BC15_MODELCD]').on('keydown', {dialog: dialog}, onNext)
			form.find('input[name=BC15_TOPICSCHK]').on('change', {form: form, dialog: dialog}, TOPICSCHK)
			form.find('input[name=BC15_FIXEDVAL]').on('keydown', {dialog: dialog}, onNext)
			form.find('input[name=BC15_LENGTHLOWER]').on('keydown', {dialog: dialog}, onNext)
			form.find('input[name=BC15_LENGTHUPPER]').on('keydown', {dialog: dialog}, onNext)
			form.find('select[name=BC15_FORMATTYP]').on('change', {form: form, onNext: onNext}, popupFixed)

			form.find('input[name=BC15_LENGTHUPPER]').on('change', {form: form}, onLengthUpper)

			function save( dialog ){

				$.ajaxSetup({
				    headers: {
				        'X-CSRF-TOKEN': "{{ csrf_token() }}"
				    }
				})

				let data = form.serializeArray()
			    $.ajax({
	                url: form.attr('action'),
	                type: 'PATCH',//'PUT',
	                data: data,
	                success: update,
     				// cache: false,
                    dataType: 'json',
                    // processData: false,
                    // contentType: false,
	                error: onFail
		        })

				function update( response ){
					if(response.success){
						toastr['success']( response.message )
						let box = {
							type: 'id',
							txtSearch: form.find('input[name=BC15_ID]').val()
						}
						dataTableView( box )
						dialog.close()
						console.log( response.data )
					}
				}
			}
		}
	}

	function dialogEvent( event ){
        var dialog = event.data.dialog
        if (typeof dialog.registeredButtonHotkeys[event.which] !== 'undefined') {
            var $button = $(dialog.registeredButtonHotkeys[event.which]);
            !$button.prop('disabled') && !$button.is(':focus') && $button.focus().trigger('click');
        }
	}

	function TOPICSCHK( event ){

			event.preventDefault()
			let form = event.data.form
			let dialog = event.data.dialog
			dialog.$modal.off('keyup')
			let url = "{{ route('BCI10020.show', ':id') }}"
			url = url.replace(':id', form.find('input[name=BC15_MODELCD]').val() )
			let box = {
				type: 'unique',
				BC15_TOPICSCHK: $(event.target).val()
			}
			
			$.get(url, box, unique, 'json').fail( onFail )
			.always( () => {
				setTimeout(function (){
					dialog.$modal.on('keyup', {dialog: dialog}, dialogEvent)
				}, 600)
			})
			
			function unique( response ){
				if(response.bool){
					toastr.error('field is required unique key!')
					errorAlert('<i class="fa fa-warning"></i> Error information!', 'field is required unique key!', undefined, ()=> {
						
						setTimeout(function (){
							$(event.target).focus().select()
						}, 350)
					})
					form.find('.form-group').each(function(index, el) {
						if( index > 1 && (!$(el).find('input').is(':disabled') || !$(el).find('select').is(':disabled'))){
							$(el).find('input').prop('disabled', true)
							$(el).find('select').prop('disabled', true)
						}
					})
					$(event.target).focus().select()
				}else{
					form.find('input').prop('disabled', false)
					form.find('select').prop('disabled', false)
					form.find('select[name=BC15_FORMATTYP]').focus()
				}
							
			}
	}

	window.onDel = function( event ){
		let d = event.data.aData

		BootstrapDialog.show({
             title: '<i class="fa fa-warning"></i> Confirm information!',
             type: BootstrapDialog.TYPE_DANGER,
             size: BootstrapDialog.SIZE_SMALL,
             message: `<h5>Are your sure? <small>delete ${d.BC15_MODELCD}</small></h5>`,
             closable: true,
             buttons: [{
                label: 'Ok',
                cssClass: 'btn-primary',
                hotkey: 13, // Enter.
                action: del
             },
             {
                label: 'Cancel',
                cssClass: 'btn-default',
                //hotkey: 13, // Enter.
                action: function( dialog ) {
                    dialog.close();
                }
             }
           ]
         })

		function del( dialog ){
			$.ajaxSetup({
			    headers: {
			        'X-CSRF-TOKEN': "{{ csrf_token() }}"
			    }
			})

			let url = '{{ route("BCI10020.destroy", ":id") }}'
			url = url.replace(':id', d.BC15_ID)

		    $.ajax({
		        url: url,
		        type: 'DELETE',//'PUT',
		        success: destroy,
					// cache: false,
		        dataType: 'json',
		        // processData: false,
		        // contentType: false,
		        error: onFail
		    })

		    function destroy( response ){
				if(response.success){
					toastr['success']( response.message )
					dataTableView()
					dialog.close()
					console.log( response.data )
				}
		    }
		}
	}

	function genAlphabet(len){
		let txtReplace = []
		for(var i = 0; i < len; ++i){
			txtReplace.push('n')
		}

		return txtReplace.join('')
	}

	function genRegNumRang(minValue, maxValue){
		return new Promise( (resolve, reject) => {
	        RegNumericRange(minValue, maxValue, {
	          MatchWholeWord: false,
	          MatchWholeLine: false,
	          MatchLeadingZero: true,
	          showProcess: false
	        }).generate(function(result){
	            if(result.success)
	            	resolve(result.data.pattern)
	            else
	            	reject(result.message)
	            
	        })
		})
	}

	String.prototype.replaceAll = function (find, replace) {
	    var str = this
	    return str.replace(new RegExp(find.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&'), 'g'), replace)
	}


	$(".main-sidebar").on("webkitTransitionEnd transitionend oTransitionEnd", function (event) {
	  if (event.originalEvent.propertyName == "width") {
	    // alert("width changed!");
			// var oSettings = dt.fnSettings()
	  		// oSettings.oScroll.sXInner = '150%'; 
	  		dt.fnDraw()
	  }
	  // if (event.originalEvent.propertyName == "height") {
	  //   alert("height changed!");
	  // }
	})


})(document, window, 0);
</script>
@endsection
