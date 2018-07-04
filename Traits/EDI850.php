<?php
namespace App\Traits;

/**
 * summary
 */
use Illuminate\Support\Facades\Storage;
use App\WMS_CUSTPO;
use App\WMS_CUSTPOLINE;
use App\WMS_JOBORDER;
use App\WMSC_JOBORDERCTL;
use DB;
use Carbon\Carbon;
trait EDI850
{
    /**
     * summary
     */
    use Typical, QuantaValidate;

    // force needs
    protected $EDI = "EDI850";
    protected $row = 2;

    // ---------------------------

    protected $filename;
	//attribute
   	private $data_file = [];
	private $header = [];
	private $footer = [];
	private $ref_detail = [
		'PO1' => [
			'PO101',
			'PO102',
			'PO103',
			'PO104',
			'PO105',
			'PO106',
			'PO107',
			'PO108',
			'PO109',
		],
		'DTM' => ['DTM01', 'DTM02']
	];

	private $ref_header = [
		'ST' => ["ST01","ST02"],
		'BEG' => [
			'BEG01',
			'BEG02',
			'BEG03',
			'BEG04',
			'BEG05',
			'BEG06',
			'BEG07',
			'BEG08',
			'BEG09',
			'BEG010',
			'BEG011',
			'BEG012'
		],
		'CUR' => ['CUR01', 'CUR02'],
		'REF' => ['REF01', 'REF02'],
		'N1' => ['N101', 'N102', 'N103', 'N104'],
		'N2' => [],
		'N3' => ['N301', 'N302'],
		'N4' => ['N401', 'N402', 'N403', 'N404'],
	];

	private $ref_footer = [
		'CTT' => ['CTT01'],
		'SE' => ['SE01', 'SE02'],
		'GE' => ['GE01', 'GE02'],
		'IEA' => ['IEA01', 'IEA02']
	];

	private $segment = [];

	// public function cook_file(){
 //    	$validate = $this->verify(\Config::get('quantastorage.header'));
	// 	echo $validate;		
	// }

	private function to_csv($filename){
		//storage_path("files/850/.{$this->ext}")
		$this->filename = $filename;
		$file = Storage::get($this->filename);
		$this->parse($file);
		
		foreach ($this->header as $value) {
			$this->verify( $value );
		}

		$output = [];

		if($this->header["BEG"]["BEG02"] == "KN"){//PurchaseOrder
			$header = array(
						"QSI Po Number",
						"Purchase Date",
						"ISO Currency Code",
						"Internal Vendor Number",
						"Ship To Company Name",
						"Ship To Code",
						"Address1",
						"Address2",
						"City Name",
						"State Or Province Code",
						"Postal Code",
						"ISO Country Code",
						"QSI Po Line Item",
						"Quantity",
						"Unit",
						"Unit Price",
						"Quanta Part Number",
						"WD Part Number",
						"Due Date"
					);

			array_push($output, $header);

			$i = 1;
			for (; $i <= sizeof($this->data_file); $i++) {

				if($i % 2 == 0){

					$PO1 = explode("*", $this->data_file[$i - 1]); //is odd number
					$DTM = explode("*", $this->data_file[$i]); //is even number
					unset($PO1[0]);
					unset($DTM[0]);
					$PO1 = $this->map_ref_detail($PO1, "PO1");
					$DTM = $this->map_ref_detail($DTM, "DTM");

					$this->verify( $PO1 );
					$this->verify( $DTM );

					$new_arr = [
						trim($this->header["BEG"]["BEG03"]), //QSI Po Number
						trim($this->header["BEG"]["BEG05"]), //Purchase Date
						"",//ISO Currency Code
						trim($this->header["REF"]["REF02"]),//Internal Vendor Number
						trim($this->header["N1"]["N102"]),//Ship To Company Name
						trim($this->header["N1"]["N102"]),//Ship To Code
						trim($this->header["N3"]["N301"]),//Address1
						trim($this->header["N3"]["N302"]),//Address2
						"",//City Name
						"",//State Or Province Code
						"",//Postal Code
						"",//ISO Country Code
						trim($PO1["PO101"]),//QSI Po Line Item
						number_format(trim($PO1["PO102"]), 2, null, ''),//Quantity,
						trim($PO1["PO103"]),//Unit
						trim($PO1["PO104"]),//Unit Price
						trim($PO1["PO107"]),//Quanta Part Number
						trim($PO1["PO109"]),//WD Part Number
						trim($DTM["DTM02"])//Due Date
					];

					array_push($output, $new_arr);
				}
			}

			$path = $this->passes ? storage_path("files/{$this->EDI}/PurchaseOrder/") : storage_path("files/{$this->EDI}/Error/");
			$csv_filename = "{$path}editest_".date("Y-m-d_H-i",time()).".csv";
			$fp = fopen($csv_filename, 'w'); //'php://output'
			foreach ($output as $line) {
			    // though CSV stands for "comma separated value"
			    // in many countries (including France) separator is ";"
			    // fputcsv($fp, $line, ',', "");
			    $this->fputcsv2($fp, $line);
			}
			fclose($fp);
	
			$move = storage_path("files/") . $this->filename;
			$move_to = storage_path("files/{$this->EDI}/Complete/") . \File::name($move) . "." . \File::extension($move);
			// \File::move($move, $move_to);

		}else{
			//JobOrder [NE]
			$i = 1;
			DB::beginTransaction();

			try {

				for (; $i <= sizeof($this->data_file); $i++) {
					if($i % 2 == 0){
						$PO1 = explode("*", $this->data_file[$i - 1]); //is odd number
						$DTM = explode("*", $this->data_file[$i]); //is even number
						unset($PO1[0]);
						unset($DTM[0]);
						$PO1 = $this->map_ref_detail($PO1, "PO1");
						$DTM = $this->map_ref_detail($DTM, "DTM");

						WMS_CUSTPO::create([
							'WMS01_CUSTPO' => $this->header["BEG"]["BEG03"],
							'WMS01_CUSTPODATE' => Carbon::parse(\DateTime::createFromFormat('Ymd', $this->header["BEG"]["BEG05"])->format('Y-m-d')),
							'WMS01_CURRENCY' => $this->header["CUR"]["CUR02"],
							'WMS01_STATUS' => 'ORDERED',
							'WMS01_UPDUSRCD' => 'W',
						]);

						WMS_CUSTPOLINE::create([
							'WMS02_CUSTPO' => $this->header["BEG"]["BEG03"],
							'WMS02_CUSTPOLINE' => $PO1["PO101"],
							'WMS02_QSIMODEL' => $PO1["PO107"],
							'WMS02_WDMODEL' => $PO1["PO109"],
							'WMS02_QUANTITY' => number_format($PO1["PO102"], 4, null, ''),
							'WMS02_UNIT' => $PO1["PO103"],
							'WMS02_UPRICE' => $PO1["PO104"],
							'WMS02_FINISHDATE' => Carbon::parse(\DateTime::createFromFormat('Ymd', $DTM['DTM02'])->format('Y-m-d')),
							'WMS02_SHIPQTY' => 0,
							'WMS02_STATUS' => 'ORDERED',
							'WMS02_UPDUSRCD' => 'W',
						]);

						$jobOrderCtl = WMSC_JOBORDERCTL::first();
						$seq = $jobOrderCtl ? ltrim($jobOrderCtl->sfcc_lastjobno, '0') : 0;
						$lastjobno = str_pad($seq + 1, 7, "0", STR_PAD_LEFT);
						WMS_JOBORDER::create([
							'WMS03_CUSTPO' => $this->header["BEG"]["BEG03"],
							'WMS03_CUSTPOLINE' => $PO1["PO101"],
							'WMS03_JOBORDER' => 'QSJ' . $lastjobno . '0000',
							'WMS03_QSIMODEL' => $PO1["PO107"],
							'WMS03_LOTQTY' => number_format($PO1["PO102"], 4, null, ''),
							'WMS03_STATUS' => 'FIRM',
							'WMS03_PROCESSCD' => is_numeric(substr($this->header["BEG"]["BEG03"], 3, 1)) ? 'FINAL' : 'PCB',
							'WMS03_ORDTYPE' => in_array(substr($this->header["BEG"]["BEG03"], 3, 1), ['A', '0']) ? 'N' : 'R',
							'WMS03_UPDUSRCD' => 'W',
						]);

						if( WMSC_JOBORDERCTL::count() > 0 ){
							WMSC_JOBORDERCTL::where('NUM', 1)->update(['SFCC_LASTJOBNO' => $lastjobno]);
						}else{
							WMSC_JOBORDERCTL::create(['NUM' => 1,'SFCC_LASTJOBNO' => $lastjobno]);
						}
					}
				}

			} catch(ValidationException $e) {
			    DB::rollback();
			    throw $e;

			} catch (Exception $e) {
			    DB::rollback();
			    throw $e;
			}

			DB::commit();	
		}

		$this->execLog();
	}

	private function parse($file){
		$i = 1; 
		$isHead = true;
		$isDetail = false;
		$isFooter = false;
		foreach (explode("\n", $file) as $key => $row){
		    // $array[$key] = explode(',', $line);
			if($key > 1){//begin ST

				$id = current(explode("*", $row));//get value from index 0 alway
				
				if(empty($id)) break;

				array_push($this->segment, $id);
				
				//find detail
				if($id == "PO1" && !$isDetail){
					$isHead = false;
					$isDetail = true;
				}

				//if summary;
				if($id == "CTT" && !$isFooter){
					$isDetail = false;
					$isFooter = true;
				}

				if($isHead){ //header

					$d = explode("*", $row);
					unset($d[0]);
					$this->header[$id] = $this->map_ref_header($d, $id);

				}else if($isDetail){//detail

					$this->data_file[$i] = $row;
					$i++;

				}else if($isFooter){

					$d = explode("*", $row);
					unset($d[0]);

					$this->footer[$id] = $this->map_ref_footer($d, $id);	

				}
			}
		}
	}


}