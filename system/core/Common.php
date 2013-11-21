<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 框架核心函数， 如CI load_class
 * 采取BROPHP的核心函数，貌似 计算大小的可以在此省略，
 * 另外要支持应用目录 动态加载函数 如CI　 library  helper
 */

/**
 * Determines if the current version of PHP is greater then the supplied value
 *
 * Since there are a few places where we conditionally test for PHP > 5
 * we'll set a static variable.
 *
 * @access	public
 * @param	string
 * @return	bool	TRUE if the current version is $version or higher
 */
if ( ! function_exists('is_php'))
{
    // var_dump(version_compare(8, 7) );
    function is_php($version = '5.0.0')
    {
        static $_is_php;
        $version = (string)$version;
        if ( ! isset($_is_php[$version]))
        {
            $_is_php[$version] = (version_compare(PHP_VERSION, $version) < 0) ? FALSE : TRUE;
        }
        return $_is_php[$version];
    }
}



/**
 * 输出各种类型的数据，调试程序时打印数据使用。
 * @param	mixed	参数：可以是一个或多个任意变量或值
 */
if(!function_exists('p'))
{
    function p(){
        $args=func_get_args();  //获取多个参数
        if(count($args)<1){
            Debug::addmsg("<font color='red'>必须为p()函数提供参数!");
            return;
        }

        echo '<div style="width:100%;text-align:left"><pre>';
        //多个参数循环输出
        foreach($args as $arg){
            if(is_array($arg)){
                print_r($arg);
                echo '<br>';
            }else if(is_string($arg)){
                echo $arg.'<br>';
            }else{
                var_dump($arg);
                echo '<br>';
            }
        }
        echo '</pre></div>';
    }
}

/**
 * 创建Models中的数据库操作对象
 *  @param	string	$className	类名或表名
 *  @param	string	$app	 应用名,访问其他应用的Model
 *  @return	object	数据库连接对象
 */
if(!function_exists('D')){
    function D($className=null,$app=""){
        $db=null;
        //如果没有传表名或类名，则直接创建DB对象，但不能对表进行操作
        if(is_null($className)){
            $class="D".DRIVER;

            $db=new $class;
        }else{
            $className=strtolower($className);
            $model=Structure::model($className, $app);
            $model=new $model();

            //如果表结构不存在，则获取表结构
            $model->setTable($className);


            $db=$model;
        }
        if($app=="")
            $db->path=APP_PATH;
        else
            $db->path=PROJECT_PATH.strtolower($app).'/';
        return $db;
    }
}

/**
 * 文件尺寸转换，将大小将字节转为各种单位大小
 * @param	int	$bytes	字节大小
 * @return	string	转换后带单位的大小
 */
if(!function_exists('tosize')){
    function tosize($bytes) {       	 	     //自定义一个文件大小单位转换函数
        if ($bytes >= pow(2,40)) {      		     //如果提供的字节数大于等于2的40次方，则条件成立
            $return = round($bytes / pow(1024,4), 2);    //将字节大小转换为同等的T大小
            $suffix = "TB";                        	     //单位为TB
        } elseif ($bytes >= pow(2,30)) {  		     //如果提供的字节数大于等于2的30次方，则条件成立
            $return = round($bytes / pow(1024,3), 2);    //将字节大小转换为同等的G大小
            $suffix = "GB";                              //单位为GB
        } elseif ($bytes >= pow(2,20)) {  		     //如果提供的字节数大于等于2的20次方，则条件成立
            $return = round($bytes / pow(1024,2), 2);    //将字节大小转换为同等的M大小
            $suffix = "MB";                              //单位为MB
        } elseif ($bytes >= pow(2,10)) {  		     //如果提供的字节数大于等于2的10次方，则条件成立
            $return = round($bytes / pow(1024,1), 2);    //将字节大小转换为同等的K大小
            $suffix = "KB";                              //单位为KB
        } else {                     			     //否则提供的字节数小于2的10次方，则条件成立
            $return = $bytes;                            //字节大小单位不变
            $suffix = "Byte";                            //单位为Byte
        }
        return $return ." " . $suffix;                       //返回合适的文件大小和单位
    }
}

