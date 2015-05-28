<?php
	require_once("libs/LIB_http.php");
	require_once("libs/LIB_parse.php");
	$web_page = http_get("http://google.com.tw", $refer = "");
	$web_page = $web_page["FILE"];

	if(stristr($web_page, "台東大學無線網路驗證系統"))
	{
		echo "need_auth\n";
		echo "The program is authing, please wait...\n";
		$data_arr = array();
		//user input username and password
		$data_arr["username"] = "your-school-email";
		$data_arr["password"] = "your-pwd";
		$data_arr["4Tredir"] = "http://google.com.tw";
		$parse_arr = parse_array($web_page," <input ",">");
		foreach ($parse_arr as $value) 
		{
			if(stristr($value, "magic"))
				$magic = trim(get_attribute($value, "value"));
		}
		
		$action = "";
		$action_arr = array("library"=>"http://10.1.230.254:1000/fgtauth?".$magic, 
			"engineering"=>"http://www.gstatic.com/generate_204");	
		
		foreach ($action_arr as $key => $value) 
		{
			$web_page = http_get($value, $refer = "");
			if($web_page!="")
				$action = $value;
		}
						
		$method = "POST";
		$ref = "";
		$data_arr["magic"] = $magic;
		$response = http($target = $action, $ref , $method, $data_arr, EXCL_HEAD);
		if($response["ERROR"]=="")
		{
			$web_page = http_get("http://google.com.tw", $refer = "");
			$web_page = $web_page["FILE"];

			if(stristr($web_page, "台東大學無線網路驗證系統"))
			{
				echo "auth_fail";
			}
			else
				echo "auth_success";
		}
	}
	else
		echo "is_auth";
?>
