<?php
/*
*  为何不使用统一入口文件index？ 主要着想横向扩展多个应用方便。如果单个应用可以将index.php里面定义全局变量。core基类核心文件变成类。
 * 参考CI，核心文件不使用class
 * 1.引入核心文件
 * 2.定义模板
 * 3.定义开发环境，正式区分
 * ?纠结是不是在index定义常量的时候 考虑到文件本身就打开一次IO 就在这里面定义了
 * 不要再在另外的config里面定义了
 * */

$system_path = 'system';
$application_folder = 'application';
// ensure there's a trailing slash
$system_path = rtrim($system_path, '/') . '/';
// Is the system path correct?
if (!is_dir($system_path)) {
    exit("Your system folder path does not appear to be set correctly. Please open the following file and correct this: " . pathinfo(__FILE__, PATHINFO_BASENAME));
}

// The name of THIS file
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
// The PHP file extension
define('EXT', '.php');

// Path to the system folder
define('BASEPATH', str_replace("\\", "/", $system_path));
// The path to the "application" folder 如果没有则自动生成 app应用 目录
define('APPPATH', $application_folder . '/');
define('HOLYES_VERSION', '1.0');

/*
 * 配置全局环境
 * 开发环境（development）开启调试模式
 * 正式环境（production） 关闭调试模式
 * */
define('ENVIRONMENT', 'development');
if (defined('ENVIRONMENT')) {
    switch (ENVIRONMENT) {
        case 'development':
            define("DEBUG", 1);
            error_reporting(E_ALL ^ E_NOTICE);   //输出除了注意的所有错误报告
            @include BASEPATH."core/debug.php";  //包含debug类
            // 一般这里面可以省略，核心类不可能不存在。暂时先写在这里面吧
            if(!class_exists(Debug))
            {
                exit(BASEPATH."core/debug.php 文件不存在！.");
            }
            //启动debug
            Debug::start();                               //开启脚本计算时间
            set_error_handler(array("Debug", 'Catcher')); //设置捕获系统异常
            break;
        case 'production':
            define("DEBUG", 0);
            error_reporting(0);
            ini_set('log_errors', 'On');             	//开启错误日志，将错误报告写入到日志中
            ini_set('error_log', APPPATH.'logs/'.date('Y-m-d').'_error_log'); //指定错误日志文件
            break;
        default:
            exit('The application environment is not set correctly.');
    }
}

//PHP程序所有需要的路径，都使用相对路径
//define("PROJECT_PATH", dirname('/').'/');  //项目的根路径，也就是框架所在的目录
//define("TMPPATH", str_replace(array(".", '/'), "_", ltrim($_SERVER["SCRIPT_NAME"], '/')).'/');


require_once BASEPATH . '/core/Holyes.php';
HOLYES::run();

