<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EDICTRCOLHD extends Model
{
    //
    protected $table = 'EDICTRCOLHD';
    protected $primaryKey = 'EDI_CODE';
    public $incrementing = false;

    public function Detail(){
    	return $this->hasMany('App\EDICTRCOLDT', 'EDI_CODE');
    }
}
