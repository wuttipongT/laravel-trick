<?php
namespace App\Traits;

trait Typical
{
	private function map_ref_header($arr, $key){
		// return array_combine($this->ref_heading[$key], $arr);
		// if some elements dont exists, "add" them...
		$key = trim($key);
		if(count($this->ref_header[$key]) != count($arr))
		{
			
			if(count($this->ref_header[$key]) > count($arr)){

				$i = 1;
				$len = count($this->ref_header[$key]) - count($arr);
				for (; $i <= $len ; $i++) 
					$arr[] = "";
				
			}else{

				$i = count($this->ref_header[$key]) + 1;
				$len = count($arr);
				for (; $i <= $len; $i++) 
					unset($arr[$i]);
			}

		}

		return array_combine($this->ref_header[$key], $arr);
	}

	private function map_ref_detail($arr, $key){

		$key = trim($key);
		if(count($this->ref_detail[$key]) != count($arr))
		{
			
			if(count($this->ref_detail[$key]) > count($arr)){

				$i = 1;
				$len = count($this->ref_detail[$key]) - count($arr);
				for (; $i <= $len ; $i++) 
					$arr[] = "";
				
			}else{

				$i = count($this->ref_detail[$key]) + 1;
				$len = count($arr);
				for (; $i <= $len; $i++) 
					unset($arr[$i]);
			}


		}
						
		return array_combine($this->ref_detail[$key], $arr);;
	}

	private function map_ref_footer($arr, $key){

		$key = trim($key);
		if(count($this->ref_footer[$key]) != count($arr))
		{
			
			if(count($this->ref_footer[$key]) > count($arr)){

				$i = 1;
				$len = count($this->ref_footerl[$key]) - count($arr);
				for (; $i <= $len ; $i++) 
					$arr[] = "";
				
			}else{

				$i = count($this->ref_footer[$key]) + 1;
				$len = count($arr);
				for (; $i <= $len; $i++) 
					unset($arr[$i]);
			}

		}
						
		return array_combine($this->ref_footer[$key], $arr);;
	}

	//ref: https://gist.github.com/tuki0918/0a3f64c1451f705ff912
	private function fputcsv2($fp, $fields, $delimiter = ',', $enclosure = '"', $mysql_null = false) {
	    $delimiter_esc = preg_quote($delimiter, '/');
	    $enclosure_esc = preg_quote($enclosure, '/');
	    $output = array();
	    foreach ($fields as $field) {
	        if ($field === null && $mysql_null) {
	            $output[] = 'NULL';
	            continue;
	        }
	        // original
	        // $output[] = preg_match("/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field) ? (
	        //     $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure
	        // ) : $field;
	        $output[] = preg_match("/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field) ? (
	            $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure
	        ) : "\"{$field}\"";
	    }
	    fwrite($fp, join($delimiter, $output) . "\n");
	}

	public function is_multi_array( $arr ) {
	    rsort( $arr );
	    return isset( $arr[0] ) && is_array( $arr[0] );
	}
}