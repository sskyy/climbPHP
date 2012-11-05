<?php
$require = array("libraries" => array("table"));

$listen = array(
	"model/:type/:action" => "model_action",
	"system/upper_module_init" => "extend_upper_level_module",
	"@:model/:id/:action" => "model_res_op",
	"system/init" => "setup_restful_entry",
	"system/module_info_extend" => "extend_module_info",
);


?>