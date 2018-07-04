<?php
namespace App\Traits;

/**
 * summary
 */
use Illuminate\Http\Request;
use App\EDICTRCOLHD;
use App\EDICTRCOLDT;
use Response;
use DB;
use Config;
use Validator;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use App\SFC_EDI_EXE_LOG;
use App\SFC_EDI_ERROR_LOG;
use Carbon\Carbon;
trait QuantaValidate
{
    /**
     * summary
     */

	protected $data = [];
	protected $priority = [
		'required' => 1,
		'unique' => 2,
		'exists' => 3,
		"digits" => 4,
		'numeric' => 5,
		'date' => 6
	];

	protected $passes = true;
	protected $count_error = 0;
	protected $count_passes = 0;
	protected $EXE_LOG;
	protected $ERROR_LOG;
    public function verify( $data )
    {
    	
       	$this->data = $this->is_multi_array($data) ? call_user_func_array('array_merge', $data) : $data;

        $table = $this->table();
        $columns = $this->column();
        $infra = $this->infrastructure($table, $columns);
        $rule = $this->rule( $infra );

		$customMessage = [
			'exists' => 'The :attribute field is required exists in system.'
		];

		if(sizeof($rule) > 0){

			$validator = Validator::make($this->data, $rule, $customMessage);

			if ( $validator->fails() ) {

				$errors = $validator->errors();

				$log = ['error' => $errors->messages(), 'rule' => $rule];
				$orderLog = new Logger('EDILog');
				$orderLog->pushHandler(new StreamHandler(storage_path('logs/edi.log')), Logger::ERROR);
				$orderLog->info('EDILog', $log);			
				// \Response::json($log, 500)->send();
				$seq = 1;
				$seq = current(\DB::select('SELECT SFC_EDI_LOG.NEXTVAL FROM DUAL'));
				$seq = $seq->nextval;

				foreach ($errors->messages() as $ref => $mess) {
					$this->EXE_LOG[] = [
						'CREATEDBY' => 'SFCOCR',
						//'NOTEEXISTSFLAG'  => '',
						//'INWORKFLOW'  => '',
						'SEQ'  => $seq, //running number
						'PGID'  => $this->EDI,
						'NUM'  => $this->footer['SE']['SE01'] - 2, //tottal qty
						'TNUM'  => '',//ok 
						'ENUM'  => '',//kg
						'EXE_DATE'  => Carbon::now(),
						'PATH'  => $this->filename,
						//'IMPORT_SEQ'  => '',				
					];

					$this->ERROR_LOG[] = [
						'CREATEDBY' => 'SFCOCR',
						'CREATEDATE' => Carbon::now(),
						//'NOTEEXISTSFLAG' => '',
						//'INWORKFLOW' => '',
						'SEQ' => $seq,
						'PGID' => $this->EDI,
						'KEY1' => $this->row,
						'KEY2' => $ref,
						'KEY3' => $this->data[$ref],
						//'CHECK_FLG' => '',
						'MESS' => current($mess)		
					];

					$this->count_error++;
				}


				if($this->passes) $this->passes = false;
				
			}

			if( $validator->passes() ) $this->count_passes++;
		}else{
			$this->count_passes++;
		}
		$this->row++;

    }

    private function infrastructure($data, $col){

    	$infra = [];
		for ($i = 0; $i < sizeof($data); $i++) {
			$rule = [];
			for ($j = 0; $j < sizeof($data[$i]); $j++) {
				if($j > 6){

					if($data[$i][$col[$j]]){
						$str = "";
						
						if($col[$j] == "digits")
							$str = $col[$j] . ":" . $data[$i][$col[$j]];
						elseif($col[$j] == "unique")
							$str = $col[$j] . ":" . $data[$i]['target_table'] . "," . $data[$i]['target_column'];
						elseif($col[$j] == "exists")
							$str = $col[$j] . ":" . $data[$i]['target_table'] . "," . $data[$i]['target_column'];	
						else
							$str = $col[$j];

						array_push($rule, ["text" => $str, "order" => $this->priority[$col[$j]]]);
					}
				}
			}

			if(!is_null($data[$i]['ref']))
				array_push($infra, [
										'name' => $data[$i]['ref'],
										'rule' => $rule,
									]);
		}

		return $infra;
    }

    private function table(){

    	return EDICTRCOLHD::rightjoin('EDICTRCOLDT' ,'EDICTRCOLHD.EDI_CODE', '=', 'EDICTRCOLDT.EDI_CODE')
		    	->where('EDICTRCOLHD.description', 'like', "%{$this->EDI}%")
		    	->select('EDICTRCOLDT.*')
		    	->orderby('item','asc')
		    	->get()
		    	->toArray();
    }

    private function column(){

    	$column = DB::select(" SELECT LOWER(COLUMN_NAME) COLUMN_NAME FROM USER_TAB_COLS WHERE TABLE_NAME='EDICTRCOLDT' ORDER BY COLUMN_ID ");
    	
    	return array_map(function($o){
    		return $o->column_name;
    	}, $column);

    }

    private function rule( $infra ){
		$rule = [];
		foreach ($this->data as $ref => $value) {
			$collec = collect($infra)->where('name', $ref);
			if($collec->isNotEmpty()){
				$arr = current($collec->all());
				$rule[$ref] = collect($arr['rule'])
								->sortBy('order')
								->map(function($item, $key){
									return $item['text'];
								})->implode('|');
			}
		}

		return $rule;  	
    }

    private function execLog(){

		$orderLog = new Logger('EDILog');
		$orderLog->pushHandler(new StreamHandler(storage_path('logs/edi.log')), Logger::ERROR);

    	DB::beginTransaction();

		try {

			array_walk_recursive($this->EXE_LOG, function(&$data, $key){

				if($key == 'TNUM') $data = $this->count_passes;
				if($key == 'ENUM') $data = $this->count_error;

			});

			if(sizeof($this->EXE_LOG) > 0) SFC_EDI_EXE_LOG::insert( $this->EXE_LOG );
			if(sizeof($this->ERROR_LOG) > 0) SFC_EDI_ERROR_LOG::insert( $this->ERROR_LOG );
			
		} catch (ValidationException $e)
		{
			DB::rollback();
			$orderLog->info('EDILog', $e->getErrors());
			throw $e;
		} catch(Exception $e)
		{
		    DB::rollback();
		    $orderLog->info('EDILog', $e->getErrors());
		    throw $e;
		}

		DB::commit();    	
    }


}