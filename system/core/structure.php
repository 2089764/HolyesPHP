<?php 

	class Structure {
		static $mess=array();    //提示消息

		/*
		 * 创建文件
		 * @param	string	$fileName	需要创建的文件名
		 * @param	string	$str		需要向文件中写的内容字符串
		 */
		static function touch($fileName, $str){
			if(!file_exists($fileName)){
				if(file_put_contents($fileName, $str)){
					self::$mess[]="创建文件 {$fileName} 成功.";
				}
			}
		}
		/*
		 * 创建目录
		 * @param	string	$dirs		需要创建的目录名称
		 */
		static function mkdir($dirs){
			foreach($dirs as $dir){
				if(!file_exists($dir)){
					if(mkdir($dir,"0755")){
						self::$mess[]="创建目录 {$dir} 成功.";
					}
				}
			}
		}
		/**
		 * 创建系统运行时的文件
		 */
		static function runtime(){
			$dirs=array(
                APP."/cache/",   //系统的缓存目录
				);
			self::mkdir($dirs);   //创建目录
		}
		/**
		 *创建项目的目录结构
		 */
		static function create(){
			self::mkdir(array(APP));
			//文件锁，一旦生成，就不再创建
			$structFile=APP_PATH."logs/".str_replace("/","_",$_SERVER["SCRIPT_NAME"]);  //主入口文件名

			if(!file_exists($structFile)) {	
				$fileName=APP_PATH."config/config.php";
				$str=<<<st
<?php
	define("DRIVER","pdo");				      //数据库的驱动，本系统支持pdo(默认)和mysqli两种
	//define("DSN", "mysql:host=localhost;dbname=123456"); //如果使用PDO可以使用，不使用则默认连接MySQL
	define("HOST", "localhost");			      //数据库主机
	define("USER", "root");                               //数据库用户名
	define("PASS", "");                                   //数据库密码
	define("DBNAME","123456");			      //数据库名

	//\$memServers = array("localhost", 11211);	     //使用memcache服务器
	/*
	如果有多台memcache服务器可以使用二维数组
	\$memServers = array(
			array("localhost", '11211'),
			array("localhost", '11211'),
			...
		);
	*/
st;
				self::touch($fileName, $str);
				if(!defined("DEBUG")){
                    include $fileName;
                }
				$dirs=array(                 //当前的应用目录
					APP_PATH."controllers/",         //当前应用的模型目录
					APP_PATH."libraries/",       //当前应用的控制器目录
					APP_PATH."logs/",          //当前应用的视图目录
                    APP."/cache/",   //系统的缓存目录
					APP_PATH."views/"           //当前应用的视图目录
				);
				self::mkdir($dirs);
                $str = <<<ST
<html>
<head>
	<title>403 Forbidden</title>
</head>
<body>
<p>Directory access is forbidden.</p>
</body>
</html>
ST;
                foreach ($dirs as $v) {
                    self::touch($v."index.html", $str);
                }


                $str=<<<st
<?php
	class Common extends Action {
		function init(){

		}		
	}
st;

				self::touch(APP_PATH."controllers/common.class.php", $str);
	
				$str=<<<st
<?php
	class Index {
		function index(){
			echo "<b>欢迎使用框架1.0, 第一次访问时会自动生成项目结构：</b><br>";
			echo '<pre>';
			echo file_get_contents('{$structFile}');
			echo '</pre>';
		}		
	}
st;

				self::touch(APP_PATH."controllers/index.class.php", $str);

				self::touch($structFile, implode("\n", self::$mess));
				
			}	
		//	self::runtime();
		}
		/**
		 * 父类控制器的生成
		 * @param	string	$srccontrolerpath	原基类控制器的路径
		 * @param	string	$controlerpath		目标基类控制器的路径
		 */ 
		static function commoncontroler($srccontrolerpath,$controlerpath){
			$srccommon=$srccontrolerpath."common.class.php";
			$common=$controlerpath."common.class.php";
			//如果新控制器不存在， 或原控制器有修改就重新生成
			if(!file_exists($common) || filemtime($srccommon) > filemtime($common)){
				copy($srccommon, $common); 	
			}	
		}

		static function controler($srccontrolerfile,$controlerpath,$m){
			$controlerfile=$controlerpath.strtolower($m)."action.class.php";
			//如果新控制器不存在， 或原控制器有修改就重新生成
			if(!file_exists($controlerfile) || filemtime($srccontrolerfile) > filemtime($controlerfile)){
				//将控制器类中的内容读出来
				$classContent=file_get_contents($srccontrolerfile);	
				//看类中有没有继承父类
				$super='/extends\s+(.+?)\s*{/i'; 
				//如果已经有父类
				if(preg_match($super,$classContent, $arr)) {
					$classContent=preg_replace('/class\s+(.+?)\s+extends\s+(.+?)\s*{/i','class \1Action extends \2 {',$classContent,1);
					//新生成控制器类
					file_put_contents($controlerfile, $classContent);
				//没有父类时
				}else{ 
					//继承父类Common
					$classContent=preg_replace('/class\s+(.+?)\s*{/i','class \1Action extends Common {',$classContent,1);
					//生成控制器类
					file_put_contents($controlerfile,$classContent);	
				}
			}
	
	
		}

		static function model($className, $app){
			$driver="D".DRIVER; //父类名
			$path=PROJECT_PATH."runtime/models/".TMPPATH;
			if($app==""){
				$src=APP_PATH."models/".strtolower($className).".class.php";
				$psrc=APP_PATH."models/___.class.php";
				$className=ucfirst($className).'Model';
				$parentClass='___model';
				$to=$path.strtolower($className).".class.php";
				$pto=$path.$parentClass.".class.php";
				
			}else{
				$src=PROJECT_PATH.$app."/models/".strtolower($className).".class.php";
				$psrc=PROJECT_PATH.$app."/models/___.class.php";
				$className=ucfirst($app).ucfirst($className).'Model';
				$parentClass=ucfirst($app).'___model';
				$to=$path.strtolower($className).".class.php";
				$pto=$path.$parentClass.".class.php";
			}

			
			//如果有原model存在
			if(file_exists($src)) {	
				$classContent=file_get_contents($src);											     $super='/extends\s+(.+?)\s*{/i'; 
				//如果已经有父类
				if(preg_match($super,$classContent, $arr)) {
					$psrc=str_replace("___", strtolower($arr[1]), $psrc);
					$pto=str_replace("___", strtolower($arr[1]), $pto);
					
					if(file_exists($psrc)){
						if(!file_exists($pto) || filemtime($psrc) > filemtime($pto)){
							$pclassContent=file_get_contents($psrc);
							$pclassContent=preg_replace('/class\s+(.+?)\s*{/i','class '.$arr[1].'Model extends '.$driver.' {',$pclassContent,1);
				
							file_put_contents($pto, $pclassContent);
				
						}
				
					}else{
						Debug::addmsg("<font color='red'>文件{$psrc}不存在!</font>");
					} 
					$driver=$arr[1]."Model";
					include_once $pto;
				}
				if(!file_exists($to) || filemtime($src) > filemtime($to) ) {	
					$classContent=preg_replace('/class\s+(.+?)\s*{/i','class '.$className.' extends '.$driver.' {',$classContent,1);
					//生成model
					file_put_contents($to,$classContent);
				}	
			}else{
				if(!file_exists($to)){
					$classContent="<?php\n\tclass {$className} extends {$driver}{\n\t}";
					//生成model
					file_put_contents($to,$classContent);	
				}	
			}

			include_once $to;
			return $className;
		}

	}
