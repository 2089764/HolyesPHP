<?php
/**
 */
 //包含框架中的公共函数库文件
require(BASEPATH.'core/Common.php');

class Holyes{
    /**
     * 异常报告实例
     * @var Report
     */
    public static $parseUrl;
    /**
     * 存储get
     * @var array
     */
    static $_gpcGet = array();
    /**
     * 存储post
     * @var array
     */
    static $_gpcPost = array();
    /**
     * 存储cookie
     * @var array
     */
    static $_gpcCookie = array();
    #全局变量存放
    static $_g = array();

    public static function run()
    {
         #gpc的统统的消灭
        self::$_gpcCookie = $_COOKIE;
        self::$_gpcGet = $_GET;
        self::$_gpcPost = $_POST;
        unset($_COOKIE,$_GET,$_POST,$_REQUEST);

        //加载config文件，可参考CI里面的;$this->config->getconfig()
        //暂时采用手工载入。
        self::config('config');

        /*   #设置自动加载,app的文件可以覆盖掉应用目录的文件
         这里有一个疑问，如果全部覆盖那不是不安全吗？  而CI里面手工覆盖了core下面的 其它使用的是load函数
        设置包含目录（类所在的全部目录）,  PATH_SEPARATOR 分隔符号 Linux(:) Windows(;)
        */
        $include_path=get_include_path();                              //原基目录
        $include_path.=PATH_SEPARATOR.BASEPATH."core/";           //框架中基类所在的目录
        $include_path.=PATH_SEPARATOR.BASEPATH."helpers/" ;      //框架中扩展函数的目录
        $include_path.=PATH_SEPARATOR.BASEPATH."database/" ;     //框架中DB类的目录
        $include_path.=PATH_SEPARATOR.BASEPATH."libraries/" ;   //框架中扩展类的目录
        $include_path.=PATH_SEPARATOR.APPPATH."core/" ;         //框架中扩展类的目录
        $include_path.=PATH_SEPARATOR.APPPATH."controllers/" ;  //框架中扩展类的目录
        //设置include包含文件所在的所有目录
        set_include_path($include_path);

        //利用spl函数将Bingo中的 _autoLoad注册__autoload()函数
        spl_autoload_register(array(__CLASS__, '_autoLoad'));
        /*
         利用自动加载类实现动态  同步一下 create创建的目录 差不多就可以使用了
         模板方面使用 php中的模块实现自己的模板 也可以利用 现成的开源产品
        Structure::create();   //初使化时，创建项目的目录结构
        */

        //开启路由模式
        self::parseUrl();

        $_g["app"]=$_SERVER["SCRIPT_NAME"].'/';           	//当前应用脚本文件
        $_g["url"]=$_g["app"].self::$_gpcGet["m"].'/';       //访问到当前模块

        // pathinfo模式 及 controler  model方法待验证
        // load 这里面最好采用CI里面的
        var_dump($_g);


        //控制器类所在的路径
        $srccontrolerfile=APPPATH."controllers/".strtolower(self::$_gpcGet["m"]).".class.php";
        Debug::addmsg("当前访问的控制器类在项目应用目录下的: <b>$srccontrolerfile</b> 文件！");

        //控制器类的创建
        if(file_exists($srccontrolerfile)){
            //控制器类所在的路径
//            $srccontrolerfile=APP_PATH."controls/".strtolower(self::$_gpcGet["m"]).".class.php";
            //调用spl_autoload_register时出错误，后来发现 自动加载的方法必须为静态访求
            // 自动加载存在问题，还需要自己修改。尤其是common出现与父类冲突 。

            $className=self::$_gpcGet["m"];
            $controler=new $className;
            $controler->run();
        }else{
            Debug::addmsg("<font color='red'>对不起!你访问的模块不存在,应该在".APP_PATH."controllers目录下创建文件名为".strtolower(self::$_gpcGet["m"]).".class.php的文件，声明一个类名为".ucfirst(self::$_gpcGet["m"])."的类！</font>");
        }

        //设置输出Debug模式的信息
        if(defined("DEBUG") && DEBUG==1){
            Debug::stop();
            Debug::message();
        }
    }

    /**
     * @var array 存储配置
     */
    protected static $_conf = array();

    /**
     * 加载并返回config
     * @param string $config 配置路径，句号路径
     * @return mixed
     */
    public static function config($config){
        /*
         * 导入主配置，其实就是将application/config下面的config.php包含进来
         */
        $config=APPPATH."config/config.php";
        try{
            if(!file_exists($config)){
                Debug::addmsg("config文件不存在！", 0);  // 返回一个错误 触发异常
                throw new CustomException();
            }
        }catch (Exception $e){}

        self::$_conf[$config] = @include $config;

        //启用memcache缓存
        if(!empty($memServers)){
            //判断memcache扩展是否安装
            if(extension_loaded("memcache")){
                $mem=new MemcacheModel($memServers);
                //判断Memcache服务器是否有异常
                if(!$mem->mem_connect_error()){
                    Debug::addmsg("<font color='red'>连接memcache服务器失败,请检查!</font>"); //debug
                }else{
                    define("USEMEM",true);
                    Debug::addmsg("启用了Memcache");
                }
            }else{
                Debug::addmsg("<font color='red'>PHP没有安装memcache扩展模块,请先安装!</font>"); //debug
            }
        }else{
            Debug::addmsg("<font color='red'>没有使用Memcache</font>(为程序的运行速度，建议使用Memcache)");  //debug
        }

        //如果Memcach开启，设置将Session信息保存在Memcache服务器中
        if(defined("USEMEM")){
            MemSession::start($mem->getMem());
            Debug::addmsg("开启会话Session (使用Memcache保存会话信息)"); //debug

        }else{
            session_start();
            Debug::addmsg("<font color='red'>开启会话Session </font>(但没有使用Memcache，开启Memcache后自动使用)"); //debug
        }
        Debug::addmsg("会话ID:".session_id());
    }

    /*
     * 自动加载类
     * */
    static function _autoLoad($className){
        if($className=="memcache"){        //如果是系统的Memcache类则不包含
            return;
        }else{                             //如果是其他类，将类名转为小写
           $rs = @include strtolower($className).".class.php";
           if(!$rs)
           {
                Debug::addmsg("指定的类 {$className}不存在。", 0);
                throw new CustomException();
           }
        }
        Debug::addmsg("<b> $className </b>类", 1);  //在debug中显示自动包含的类
    }


    /**
     * load已经实例化过的类集合
     * @var array
     */
    protected static $_instance = array();
    /**
     * @var string $loading 目前加载中的类名、参数，可以在hook中改变
     */
    public static $_loading = array(
        'class'=>'',
        'params'=>'',
        'function'=>'',
    );

    /**
     * 加载并实例化类
     * @param string $class 类名称，支持句号模式
     * @param null $params 类参数
     * @param bool $single 是否单例
     * @return object
     */
    public static function load($class,$params = null,$single = true){
        //若单例或已实例化过 直接返回资源
        if ($single && isset(self::$_instance[$class])) {
       //     return self::$_instance[$class];
        }
        //对_loading进行赋值
        $_tempClassName = explode('.',$class);
        self::$_loading['class'] = $_tempClassName[0];
        self::$_loading['params'] = $params;
        self::$_loading['function'] = $_tempClassName[1] ? $_tempClassName[1] : '';

        // 进行实例化
        $obj = self::_load();
        //设置单例时，将实例对象保存起来。
        if ($single) {
            self::$_instance[$class] = $obj;
        }
        self::$_loading = array(
            'class'=>'',
            'params'=>array()
        );
        return $obj;
    }
    /*
    * 导入类文件并返回类的实例化
    * 1.导入类文件
    * 2.若有参数则利用反射进行实例化，无参数则直接 new
    * 3.返回实例化对象
     *
     * ??这里如果使用目录限制 如 system作为参数传进来可能更好的定位类文件所在。但问题是include的配置的路径就没有什么意义了。
     * 这里先用include自动 包含台。后期 再优化。
    * */
    protected static function _load(){
        try{
            $class = self::$_loading['class'];

            //若有参数则利用反射进行实例化，无参数则直接 new
            if (self::$_loading['params']) {
                $classReflection = new ReflectionClass($class);//创建一个反射类
                //从给出的参数创建一个新的类实例 new obj（）
                $obj = $classReflection->newInstanceArgs(self::$_loading['params']);
            } else {
                $obj = new $class();
            }
            $function = self::$_loading['function'];
            if ($function && !method_exists($class,$function)) {
                Debug::addmsg("指定的{$class}类中方法{$function}不存在。", 0);
                throw new CustomException();
            }
            if($function){
                $obj->$function();
            }
            return $obj;
        }catch (Exception $e){}


    }

    /**
     * URL路由,转为PATHINFO的格式
     */
    static function parseUrl(){
        if (isset($_SERVER['PATH_INFO'])){
            //获取 pathinfo
            $pathinfo = explode('/', trim($_SERVER['PATH_INFO'], "/"));

            // 获取 control
            self::$_gpcGet['m'] = (!empty($pathinfo[0]) ? $pathinfo[0] : 'index');

            array_shift($pathinfo); //将数组开头的单元移出数组

            // 获取 action
            self::$_gpcGet['a'] = (!empty($pathinfo[0]) ? $pathinfo[0] : 'index');
            array_shift($pathinfo); //再将将数组开头的单元移出数组

            for($i=0; $i<count($pathinfo); $i+=2){
                self::$_gpcGet[$pathinfo[$i]]=$pathinfo[$i+1];
            }
        }else{
            //这里要处理 验证 过滤sql注入
            self::$_gpcGet["m"]= (!empty(self::$_gpcGet['m']) ? self::$_gpcGet['m']: 'index');    //默认是index模块
            self::$_gpcGet["a"]= (!empty(self::$_gpcGet['a']) ? self::$_gpcGet['a'] : 'abc');   //默认是index动作
            if($_SERVER["QUERY_STRING"]){
                $m=self::$_gpcGet["m"];
                unset(self::$_gpcGet["m"]);  //去除数组中的m
                $a=self::$_gpcGet["a"];
                unset(self::$_gpcGet["a"]);  //去除数组中的a
                $query=http_build_query(self::$_gpcGet);   //形成0=foo&1=bar&2=baz&3=boom&cow=milk格式
                //组成新的URL
                $url=$_SERVER["SCRIPT_NAME"]."/{$m}/{$a}/".str_replace(array("&","="), "/", $query);
                header("Location:".$url);
            }
        }
    }

}

class CustomException extends Exception{
    public function __construct()
    {
        do {
            $msg[] = self::getFile().":".self::getLine()."行 ".self::getMessage()." (".self::getCode().") ";
        } while(self::getPrevious());
        Debug::addmsg($msg, 3);
        Debug::stop();
        Debug::message();
        exit();
    }
}
