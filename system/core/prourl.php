<?php
/**
 */
	class Prourl {
		/**
		 * URL路由,转为PATHINFO的格式
		 */ 
		static function parseUrl(){
			if (isset($_SERVER['PATH_INFO'])){
      			 	//获取 pathinfo
				$pathinfo = explode('/', trim($_SERVER['PATH_INFO'], "/"));
			
       				// 获取 control
                Holyes::$_gpcGet['m'] = (!empty($pathinfo[0]) ? $pathinfo[0] : 'index');

       				array_shift($pathinfo); //将数组开头的单元移出数组 
      				
			       	// 获取 action
                Holyes::$_gpcGet['a'] = (!empty($pathinfo[0]) ? $pathinfo[0] : 'index');
				array_shift($pathinfo); //再将将数组开头的单元移出数组 

				for($i=0; $i<count($pathinfo); $i+=2){
                    Holyes::$_gpcGet[$pathinfo[$i]]=$pathinfo[$i+1];
				}
			
			}else{
                Holyes::$_gpcGet["m"]= (!empty(Holyes::$_gpcGet['m']) ? Holyes::$_gpcGet['m']: 'index');    //默认是index模块
                Holyes::$_gpcGet["a"]= (!empty(Holyes::$_gpcGet['a']) ? Holyes::$_gpcGet['a'] : 'abc');   //默认是index动作
	
				if($_SERVER["QUERY_STRING"]){
					$m=Holyes::$_gpcGet["m"];
					unset(Holyes::$_gpcGet["m"]);  //去除数组中的m
					$a=Holyes::$_gpcGet["a"];
					unset(Holyes::$_gpcGet["a"]);  //去除数组中的a
					$query=http_build_query(Holyes::$_gpcGet);   //形成0=foo&1=bar&2=baz&3=boom&cow=milk格式
					//组成新的URL
					$url=$_SERVER["SCRIPT_NAME"]."/{$m}/{$a}/".str_replace(array("&","="), "/", $query);
					header("Location:".$url);
				}	
			}
		}
	}
