<?php
//模块不能再使用require和listen两个字段了。



//格式 "模块名 => "别名"
$require = array( 
	'modules' => array("persistence")
);

$listen = array(
	
	//普通的操作
	"user/:action" => "user_action",

	/*
	//以下演示后写user模块时，增加的对所有资源操作的验证。
	//可选键名:
	//    first : 在所有事件之前执行
	//    last : 在所有事件之后执行
	//    before : 在某个事件触发之前执行
	//    after : 在某个事件触发之后执行
	"@:source/:id/:action" => array(
		//同一事件可以对应多个响应，响应通过#async和#sync标识表示同步和顺序操作。
		//响应可以是直接触发事件，当响应的字符串中带有"/"号时表示是事件。
		"first" => array("#async","is_user_logged_in","is_user_authoried"),
	),

	"@book/:id/delete" => array(
		"before" => array("@note/:id/delete", "notify_user_of_delete"),
	),
	*/

	//以下演示先写user模块时，可能需要对外提供的响应
	"user/is_logged_in" => "is_user_logged_in",
);


//使用persistence的扩展功能，相当于声明了事件：
	//对资源的操作 :  (资源名)@[:操作名替代符|操作名]
	//"@user/:id/:action" => "user_res_op",
$modelNs = array(
	'user',
);

?>