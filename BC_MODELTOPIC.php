<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\BUYER;

class BC_MODELTOPIC extends Model
{
    //
    protected $table = "BC_MODELTOPIC";
    protected $primaryKey = 'BC15_ID';
    const CREATED_AT = 'BC15_REGYMD';
    const UPDATED_AT = 'BC15_CHGYMD';
    // public $timestamps = false;
    // protected $dates = ['BC15_REGYMD', 'BC15_CHGYMD'];
    protected $fillable = [
    	'BC15_MODELCD',
		'BC15_TOPICSCHK',
		'BC15_FORMATTYP',
		'BC15_PREFIX',
		'BC15_SUBFIX',
		'BC15_POSTFIX',
		'BC15_FIXEDVAL',
		'BC15_LENGTHLOWER',	
		'BC15_LENGTHUPPER',	
		//'BC15_REGYMD',
		//'BC15_CHGYMD',
		'BC15_USRUPD',
		'BC15_PROGRAMID'
    ];

 //    protected $maps = [
	//     'BC15_MODELCD' => 'MODEL CODE',
	//     'BC15_TOPICSCHK' => 'TOPPICS CHECK',
	//     'BC15_FORMATTYP' => 'FORMAT TYPE',
	//     'BC15_PREFIX' => 'PREFIXED',
	//     'BC15_SUBFIX' => 'SUBFIXED',
	//     'BC15_POSTFIX' => 'POSTFIXED',
	//     'BC15_FIXEDVAL' => 'FIXED VALUE',
	//     'BC15_LENGHLOWER' => 'LENGH LOWER',
	//     'BC15_LENGHUPPER' => 'LENG UPPER'
	// ];
   	public function __construct(){
   		$this->table = BUYER::getSchema() . '.' . $this->table; //BUYER::getSchema() 
   	}

}
