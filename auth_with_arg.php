<?php
	require_once("libs/LIB_http.php");
	require_once("libs/LIB_parse.php");

	$option = null;
	$ssid = htmlentities(trim($argv[1]));
	$email = $argv[2];
	$password = $argv[3];

	//ssid: ap-nttu,CSE,ntou, and etc.
	if($ssid === "ntou")
		$option = 1;
	else
		$option = 3;

	if($option == 3) {
		echo auth_nttu($email, $password);
	}
	else if($option == 2) {
		//not yet matbe ssid TANET-Roming(NTOU library)?
	}
	else {
		echo auth_ntou("ntou", $email, $password);
	}

	function auth_ntou($str, $email, $password) {

		if($str == "ntou") {
			//TANetRoaming,ntou,ntou-guest and son.
			$data_arr = array();
			$data_arr["username"] = $email;
			$data_arr["password"] = $password;
			$data_arr["ok"] = "登入";
			
			$response = http($target = "https://140.121.40.253/user/user_login_auth.jsp?", $ref = "", $method = "POST", $data_arr, EXCL_HEAD);
			
			if($response["ERROR"]=="") {
				http_get("https://140.121.40.253/user/user_login_auth.jsp?", $ref = "");
				http_get("https://140.121.40.253/user/_allowuser.jsp?", $ref = "");
				$web_page = http_get("http://google.com.tw", $ref = "");
				$web_page = $web_page["FILE"];

				print_r($web_page);
				if(stristr($web_page, "Authentication Required for Wireless Access")) {
					print_r($response["ERROR"]);
					echo "auth_fail";
				}
				else
					echo "auth_success";
			}
			else {
				echo "It's auth or you are not in this wireless access point.\n";
			}
		}
	}

	function auth_nttu($email, $password) {
		$web_page = http_get("http://google.com.tw", $refer = "");
		$web_page = $web_page["FILE"];

		if(stristr($web_page, "台東大學無線網路驗證系統")) {
			$data_arr = array();
			//user input username and password
			$data_arr["username"] = $email;
			$data_arr["password"] = $password;
			$data_arr["4Tredir"] = "http://google.com.tw";
			$parse_arr = parse_array($web_page," <input ",">");
			foreach ($parse_arr as $value) {
				if(stristr($value, "magic"))
					$magic = trim(get_attribute($value, "value"));
			}
		
			$action = "";
			$check_common = "";
			$action_arr = array("library"=>"http://10.1.230.254:1000/fgtauth?".$magic, 
				"engineering_teach"=>"http://www.gstatic.com/generate_204");
		
			foreach ($action_arr as $key => $value) {
				$web_page = http_get($value, $refer = "");
				if($web_page!="")
				{
					$action = $value;
					$check_common = $key;
				}
			}

			$method = "POST";
			$ref = "";
			$data_arr["magic"] = $magic;
			$response = http($target = $action, $ref , $method, $data_arr, EXCL_HEAD);
			if($response["ERROR"]=="") {
				$web_page = http_get("http://google.com.tw", $refer = "");
				$web_page = $web_page["FILE"];

				if(stristr($web_page, "台東大學無線網路驗證系統")) {
					echo "auth_fail";
				}
				else
					echo "auth_success";
			}
		}
		else if(stristr($web_page, "USERNAME")) {
			$action = "https://securelogin.arubanetworks.com/cgi-bin/login";
			$data_arr = array();
			$data_arr["user"] = $email;
			$data_arr["password"] = $password;
			$data_arr["authenticate"] = "authenticate";
			$data_arr["accept_aup"] = "accept_aup";
			$data_arr["requested_url"] = "";
			$method = "POST";
			$ref = "";
			$response = http($target = $action, $ref , $method, $data_arr, EXCL_HEAD);
			if($response["ERROR"]=="") {
				$web_page = http_get("http://google.com.tw", $refer = "");
				$web_page = $web_page["FILE"];

				if(stristr($web_page, "USERNAME")) {
					echo "auth_fail";
				}
				else
					echo "auth_success";
			}
		}
		else
			echo "It's auth or you are not in this wireless access point.\n";
	}
?>
