<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\BC_MODELTOPIC;
use App\BC_TOPPICCTL;
use \DB;
use App\BUYER;
use App\BC_MODEL;
use Excel;
use PHPExcel_Worksheet_PageSetup;
class BCI10020Controller extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private $validate = [
            'BC15_MODELCD' => 'required',
            'BC15_TOPICSCHK' => 'required',
            'BC15_FORMATTYP' => 'required',
            // 'BC15_PREFIX' => 'required',
            // 'BC15_SUBFIX' => 'required',
            // 'BC15_POSTFIX' => 'required',
            'BC15_FIXEDVAL' => 'required',
            // 'BC15_LENGTHLOWER' => 'required',
            // 'BC15_LENGTHUPPER' => 'required'
        ];

    public function __construct(){
        $this->middleware('auth');
    }

    public function index()
    {
        //
        return view('BASE.BCI10020.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $BC_TOPPICCTLS = $this->__toUpperCase(BC_TOPPICCTL::all(['BC16_TOPPICCD','BC16_TOPPICNM'])->toArray());
        $BC_TOPPICCTL = array('-1' => '-- Select --');
        foreach ($BC_TOPPICCTLS as $key => $value) {
          # code...
          $BC_TOPPICCTL[$value['BC16_TOPPICCD']] = $value['BC16_TOPPICNM']; 
        }

        return view('BASE.BCI10020.create', compact('BC_TOPPICCTL'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
     
        if($request->input('BC15_FORMATTYP') == 'KEEP'){
          $validate = $this->validate;
          unset($validate['BC15_FIXEDVAL']);    
        }else{
          $validate = $this->validate;
        }


        $this->validate($request, $validate);
  
        // $data = BC_MODELTOPIC::create( $request->all() );
        // dd($request->all()); exit;
        $model = new BC_MODELTOPIC();
        // $model->BC15_MODELCD = "55";
        // $model->BC15_TOPICSCHK = "55";
        $data = $request->all();
        $data['BC15_USRUPD'] = date("Y-m-d H:i:s");
        $model->fill( $data );
        // $model->timestamps = false;
        $data = $model->save();

        $response = [
            'message' => 'create successfully!',
            'data' => $data,
            'success' => $data ? true : false
        ];

        return $response;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
    
        $type = $request->input('type');
        switch ($type) {
            case 'autocomplete':
                # code...
                $max = $request->input('max');
                $data =  $this->__toUpperCase( BC_MODEL::where(function ( $q ) use ( $id ) {
                                                                      $q->whereRaw( "UPPER(BC10_MODELCD) like '%{$id}%'" );
                                                                    })->limit($max)->get(['BC10_MODELCD'])->toArray() );
                return response()->json([
                  'data' => $data,
                  'request' => $request->all(),
                  'schema' => BUYER::getSchema()
                ]);
                break;
            case 'unique' :
                $BC15_TOPICSCHK = $request->input('BC15_TOPICSCHK');
                $bool = BC_MODELTOPIC::where([
                    ['BC15_MODELCD', '=', $id],
                    ['BC15_TOPICSCHK','=', $BC15_TOPICSCHK]
                ])->count() > 0 ? true : false;
                return ['bool' => $bool, 'request' => $request->all(), 'id' => $id];
                break;
            case 'report': $this->drawReport(strtoupper($request->input('txtSearch'))); break;
            case 'id':
                   return $this->__toUpperCase( BC_MODELTOPIC::where('BC15_ID', $request->input('txtSearch'))->get()->toArray() );  
                break;
            default:
                 if($id != -1){
                    $BC_MODELTOPIC = (object) $this->__toUpperCase( BC_MODELTOPIC::find( $id )->toArray() );
                    return view('BASE.BCI10020.view', compact('BC_MODELTOPIC'));
                }else{
                    $txtSearch = strtoupper($request->input('txtSearch'));
                    if($txtSearch){
                        $data = $this->__toUpperCase( BC_MODELTOPIC::Where(function($query) use ($txtSearch) {
                                                                    $query->orWhere('BC15_MODELCD', 'like', '%' . $txtSearch . '%');
                                                                    $query->orWhere('BC15_TOPICSCHK', 'like', '%' . $txtSearch . '%');
                                                                    $query->orWhere('BC15_FORMATTYP', 'like', '%' . $txtSearch . '%');
                                                                    $query->orWhere('BC15_PREFIX', 'like', '%' . $txtSearch . '%');
                                                                    $query->orWhere('BC15_SUBFIX', 'like', '%' . $txtSearch . '%');
                                                                    $query->orWhere('BC15_POSTFIX', 'like', '%' . $txtSearch . '%');
                                                                    $query->orWhere('BC15_FIXEDVAL', 'like', '%' . $txtSearch . '%');
                                                                    $query->orWhere('BC15_LENGTHLOWER', 'like', '%' . $txtSearch . '%');
                                                                    $query->orWhere('BC15_LENGTHUPPER', 'like', '%' . $txtSearch . '%');
                                                                  })
                                                                  ->get()->toArray() );
                    }else{
                      $data = $this->__toUpperCase( BC_MODELTOPIC::all()->toArray() );    
                    }
                    
                    return response()->json($data);
                }
                break;
        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $BC_MODELTOPIC = (object) $this->__toUpperCase( BC_MODELTOPIC::find($id)->toArray() );

        $BC_TOPPICCTLS = $this->__toUpperCase(BC_TOPPICCTL::all(['BC16_TOPPICCD','BC16_TOPPICNM'])->toArray());
        $BC_TOPPICCTL = array('-1' => '-- Select --');
        foreach ($BC_TOPPICCTLS as $key => $value) {
          # code...
          $BC_TOPPICCTL[$value['BC16_TOPPICCD']] = $value['BC16_TOPPICNM']; 
        }

        return  view('BASE.BCI10020.edit', compact('BC_MODELTOPIC', 'BC_TOPPICCTL'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //

        if($request->input('BC15_FORMATTYP') == 'KEEP'){
          $validate = $this->validate;
          unset($validate['BC15_FIXEDVAL']);    
        }else{
          $validate = $this->validate;
        }

        $this->validate($request, $validate);

        // dd($this->__toLowerCase($request->all()));exit;
        $d = $request->all();
        // unset($d['BC15_MODELCD']);
        // unset($d['BC15_TOPICSCHK']);
        unset($d['_method']);
        unset($d['_token']);

        // DB::enableQueryLog();

        DB::beginTransaction();
        $data = BC_MODELTOPIC::where('BC15_ID', $id)->update( $d );
   
        DB::commit();

        // $queries = DB::getQueryLog();
        // print_r ($queries); exit;
        // $data = BC_MODELTOPIC::find( $id )->update( [
        //     'bc15_modelcd' => 'ok'
        // ] );
        // $model = BC_MODELTOPIC::find( $id );
        // $model->fill( $this->__toLowerCase( $request->all() ) );
        // $model->save();
        // $schema = BUYER::getSchema();
        // $sql_text = " UPDATE {$schema}.BC_MODELTOPIC 
        //                 SET 
        //                     BC15_FORMATTYP = '{$request->input('BC15_FORMATTYP')}',
        //                     BC15_PREFIX = '{$request->input('BC15_PREFIX')}',
        //                     BC15_SUBFIX = '{$request->input('BC15_SUBFIX')}',
        //                     BC15_POSTFIX = '{$request->input('BC15_POSTFIX')}',
        //                     BC15_FIXEDVAL = '{$request->input('BC15_FIXEDVAL')}',
        //                     BC15_LENGHLOWER = '{$request->input('BC15_LENGHLOWER')}',
        //                     BC15_LENGHUPPER = '{$request->input('BC15_LENGHUPPER')}',
        //                     BC15_CHGYMD = CURRENT_DATE
        //                 WHERE BC15_ID = ? ";

        // $data = DB::update($sql_text, [$id]);
        
        $response = [
            // 'error' => $errors->all(),
            'success' => $data ? true : false,
            'message' => 'update successfully!',
            'data' => $data,
            'request' => $request->all(),
            'id' => $id
        ];

        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
        // $data = BC_MODELTOPIC::destroy( $id );
        $data = BC_MODELTOPIC::where('BC15_ID', $id)->delete();
        //$schema = BUYER::getSchema();
       // $data = DB::delete("DELETE FROM {$schema}.BC_MODELTOPIC WHERE BC15_ID = ?", [$id]);

        $response = [
            // 'error' => $errors->all(),
            'success' => $data ? true : false,
            'message' => 'delete successfully!',
            'data' => $data,
            'id' => $id
        ];
        
        return response()->json( $response );
    }

    private function array_map_callback( $res ){
        return array_change_key_case($res, CASE_UPPER);
    }

    private function __toUpperCase( $val ){
        
        if( $this->is_multi( $val ) ){
             $arr = array_map(array($this, 'array_map_callback'), $val);
        }else{
            $arr = array_change_key_case($val, CASE_UPPER);
        }

        return $arr;
    }

    private function __toLowerCase( $val ){
        
        if( $this->is_multi( $val ) ){
             $arr = array_map(function( $res ){
              return array_change_key_case($res, CASE_LOWER);
             }, $val);
        }else{
            $arr = array_change_key_case($val, CASE_LOWER);
        }

        return $arr;
    }

    private function is_multi($array) {
        return (count($array) != count($array, 1));
    }

    public function drawReport($param){
        global $txtSearch;
        $txtSearch = $param;

        Excel::create('BCI10020', function($excel){
         // Set the title
          $excel->setTitle('Control file input by P/E');

          // Chain the setters
          $excel->setCreator('th.wuttipong C000067')
                ->setCompany('World Electric co.Ltd');

          // Call them separately
          $excel->setDescription('Control file input by P/E');

          $styleArray = array(
           'font'  => array(
               'color' => array('rgb' => '000000'),
               'size'  => 11,
               'name'  => 'Tahoma'
           ));

          $excel->getDefaultStyle()->applyFromArray($styleArray);

          $excel->sheet('sheet1', function($sheet){
            // Sheet manipulation

            $sheet->getPageSetup()
                 ->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4)
                 ->setFitToPage(true)
                 ->setFitToWidth(1)
                 ->setFitToHeight(0);

            $sheet->getPageMargins()
                 ->setTop(0.25)
                 ->setRight(0.25)
                 ->setLeft(0.25)
                 ->setBottom(0.25);

            // $sheet->setColumnFormat(array(
            //   'A' => '0000'
            // ));

            $sheet->cell('A1:I1', function($cell) {
                // manipulate the cell
                $cell->setAlignment('center');
                $cell->setFont(['bold'=>true]);
            });

            $data = array('MODEL CODE', 'TOPPICS CHECK', 'FORMAT TYPE', 'PREFIXED', 'SUBFIXED', 'POSTFIXED', 'FIXED VALUE', 'LENGH LOWER', 'LENG UPPER');

            $sheet->fromArray($data, null, 'A1', false, false);
            $sheet->freezeFirstRow();
            
            // $sheet->cell('A1:H2', function($cell) {
            //     // manipulate the cell
            //     $cell->setAlignment('center');
            // });

            // $BOIGROUP = QB_BOIGROUP::select(
            //     'QST20_PARTCD',
            //     'QST20_PARTNM'
            // )->rightJoin(
            //     DB::raw("
            //         (SELECT * FROM SFCORCL.QSTBOM WHERE QST20_PARTCD IS NOT NULL) QSTBOM
            //     "), 'QB01_PARTCD', '=', 'QST20_PARTCD'
            // )
            // ->whereNull('QB01_PARTCD')
            // ->get();
            $txtSearch = $GLOBALS['txtSearch'];
            $BC_MODELTOPIC = BC_MODELTOPIC::Where(function($query) use ($txtSearch) {
                                                                    $query->orWhere('BC15_MODELCD', 'like', '%' . $txtSearch . '%');
                                                                    $query->orWhere('BC15_TOPICSCHK', 'like', '%' . $txtSearch . '%');
                                                                    $query->orWhere('BC15_FORMATTYP', 'like', '%' . $txtSearch . '%');
                                                                    $query->orWhere('BC15_PREFIX', 'like', '%' . $txtSearch . '%');
                                                                    $query->orWhere('BC15_SUBFIX', 'like', '%' . $txtSearch . '%');
                                                                    $query->orWhere('BC15_POSTFIX', 'like', '%' . $txtSearch . '%');
                                                                    $query->orWhere('BC15_FIXEDVAL', 'like', '%' . $txtSearch . '%');
                                                                    $query->orWhere('BC15_LENGTHLOWER', 'like', '%' . $txtSearch . '%');
                                                                    $query->orWhere('BC15_LENGTHUPPER', 'like', '%' . $txtSearch . '%');
                                                                  })
                                                                  ->get([
                                                                    'BC15_MODELCD',
                                                                    'BC15_TOPICSCHK',
                                                                    'BC15_FORMATTYP',
                                                                    'BC15_PREFIX',
                                                                    'BC15_SUBFIX',
                                                                    'BC15_POSTFIX',
                                                                    'BC15_FIXEDVAL',
                                                                    'BC15_LENGTHLOWER',
                                                                    'BC15_LENGTHUPPER'
                                                                ]);

            global $num_row;
            $BC_MODELTOPIC->each(function($data, $num_row) use ($sheet)// foreach($posts as $post) { }
            {
                //do something
                $row = $num_row + 2;
                $sheet->row($row, $data->toArray());

                $GLOBALS['num_row'] = $row;
            });

            // $sheet->cell("A2:B{$num_row}", function($cell) {
            //     // manipulate the cell
            //     $cell->setAlignment('left');
            // });

          });

        })->export('xls');
    }
}
