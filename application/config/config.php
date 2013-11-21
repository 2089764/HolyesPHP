<?php
 
define("DRIVER","pdo");				      //数据库的驱动，本系统支持pdo(默认)和mysqli两种
//define("DSN", "mysql:host=localhost;dbname=123456"); //如果使用PDO可以使用，不使用则默认连接MySQL
define("HOST", "localhost");			      //数据库主机
define("USER", "root");                               //数据库用户名
define("PASS", "");                                   //数据库密码
define("DBNAME","123456");			      //数据库名
define("TABPREFIX", "bro_");                           //数据表前缀

$memServers = array("localhost", 11211);	     //使用memcache服务器
/*
如果有多台memcache服务器可以使用二维数组
$memServers = array(
        array("192.168.1.1", '11211'),
        array("192.168.1.2", '11211'),
        ...
    );
*/