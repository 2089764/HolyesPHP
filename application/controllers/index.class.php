<?php
    class Index extends Common {
        public function __construct()
        {
            parent::__construct();
        }
		function abc()
		{
			$a = Holyes::load('welcome');
            $a->ttt();
		}
        //写在基类
        function run(){
            //如果有子类Common，调用这个类的init()方法 做权限控制
            if(method_exists($this, "init")){
                $this->init();
            }

            //根据动作去找对应的方法
            $method=Holyes::$_gpcGet["a"];
            if(method_exists($this, $method)){
                $this->$method();
            }else{
                Debug::addmsg("<font color='red'>没有".Holyes::$_gpcGet["a"]."这个操作！</font>");
            }
        }
	}