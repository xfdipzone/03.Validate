<?php
/** 表單驗證類
*	Date: 	2011-3-28
*	Author:	fdipzone
*/

class Validate{	//class start

	public function __construct(){

	}


	/** 檢測表單數據
	* @param String	$val	要檢測的數據
	* @param String	$type	檢測的類型
	* @param Array	$param	參數
	*/	
	public function checkData($val,$type,$param=array()){
		$type = strtolower($type);
		switch($type){
			case 'empty':	//為空
					if(!isset($val)){	//no isset = empty
						return true;
					}
					if(isset($param['zero']) && $param['zero']==1){	//0不屬於空
						return gettype($val)=='integer'? empty($val) && $val!==0 : empty($val) && $val!=='0';
					}else{
						return empty($val);	//0屬於空
					}
					break;

			case 'length':	//字符長度
					$length = mb_strlen($val,'utf-8');
					$max = isset($param['maxlen'])? $param['maxlen'] : 9999;
					$min = isset($param['minlen'])? $param['minlen'] : 0;
					return ($length>=$min && $length<=$max)? true : false;
					break;

			case 'number':	//數字
					$float = isset($param['float'])? $param['float'] : 0;		//1:允許浮點數	0:不允許浮點數
					$signed = isset($param['signed'])? $param['signed'] : 0;	//1:允許負數	0:不允許負數
					if($signed){
						return $float? is_numeric($val) : preg_match('/^-?([0-9])+$/',$val);
					}else{
						return $float? preg_match('/^([0-9])+([\.|,]([0-9])*)?$/',$val) : preg_match('/^([0-9])+$/',$val);
					}
					break;

			case 'en':	//字母
					return preg_match('/^[A-Za-z]+$/',$val);
					break;

			case 'cn':	//中文
					return preg_match('/^[\x7f-\xff]+$/',$val);
					break;

			case 'email':	//email
					return preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/",$val);
					break;

			case 'date':	//日期 yyyy-mm-dd
					$low = isset($param['low'])? $param['low'] : 1900;
					$high = isset($param['high'])? $param['high'] : 9999;
					if(!preg_match('/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/',$val)){ return false; }
					$date = explode('-',$val);
					if($date[0]>$high || $date[0]<$low){ return false; }
					return checkdate($date[1],$date[2],$date[0]);
					break;

			case 'phone':	//電話
					$length = isset($param['length'])? $param['length'] : 8;
					return preg_match('/^[0-9]{'.$length.'}$/',$val);
					break;

			case 'idcard':	//身份證
					$letter = isset($param['letter'])? intval($param['letter']) : 2;
					$length = isset($param['length'])? intval($param['length']) : 4;
					$letter = $letter<1 || $letter>2? 2 : $letter;
					$length = $length<4 || $length>8? 4 : $length;
					$exec = array();
					for($i=1; $i<=$letter; $i++){
						array_push($exec,'(^[A-Za-z]{'.$i.'}[0-9]{'.($length-$i).'}$)');
					}
					$exec = '/'.implode('|',$exec).'/';
					return preg_match($exec,$val);
					break;

			case 'http':	//網址
					return preg_match('/http(s)?:\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is',$val);
					break;

			case 'fileext':	//文件類型
					$ext = isset($param['ext'])? $param['ext'] : 'jpg';
					$allext = explode('|',$ext);
					$fileext = strtolower(substr($val,strrpos($val,'.')+1));
					return in_array($fileext,$allext);
					break;

			case 'struct':	//組合
					$cn = isset($param['cn'])? $param['cn'] : 0;
					$en = isset($param['en'])? $param['en'] : 0;
					$number = isset($param['number'])? $param['number'] : 0;
					$other = isset($param['other'])? $param['other'] : '';
					$max = isset($param['maxlen'])? $param['maxlen'] : 9999;
					$min = isset($param['minlen'])? $param['minlen'] : 0;
					$length = mb_strlen($val,'utf-8');
					if($cn==0 && $en==0 && $number==0 && $other==''){ return false;	}	//條件為空
					$exec = '';
					if($cn==1){ $exec = $exec . '\x7f-\xff'; }
					if($en==1){ $exec = $exec . 'A-Za-z'; }
					if($number==1){ $exec = $exec . '0-9'; }
					if(!empty($other)){ $exec = $exec . $other;}
					$exec = str_replace(' ','\s',$exec);		//空格
					return preg_match('/^['.$exec.']+$/',$val) && $length>=$min && $length<=$max;
					break;

			case 'only':	//唯一
					$key = isset($param['key'])? $param['key'] : ',';
					$group = explode($key,$val);
					return count($group)==count(array_count_values($group));
					break;
			
			case 'in':	//固定範圍
					$min = isset($param['min'])? $param['min'] : 1;
					$max = isset($param['max'])? $param['max'] : 0;
					$empty = isset($param['empty'])? $param['empty'] : 0;
					if($empty==1 && empty($val)){ return true; }	//允許為空
					if(empty($val)){ return false; }
					$tmparr = array();	//臨時數組
					for($i=$min; $i<=$max; $i++){ array_push($tmparr,$i); }
					if(is_array($val)){
						for($i=0; $i<count($val); $i++){
							if(!in_array($val[$i],$tmparr)){
								return false;
							}
						}
						return true;
					}else{
						return in_array($val,$tmparr);
					}
					break;

			case 'match':	//自定義正則
					if(!isset($param['exp'])){
						return false;
					}else{
						$exp = $param['exp'];
						return preg_match($exp,$val);
					}
					break;
					
			default:
					return '';
		}
	}

}	// class end
?>