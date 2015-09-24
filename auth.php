<?php
	require_once("libs/LIB_http.php");
	require_once("libs/LIB_parse.php");
	$handle = fopen("php://stdin", "r");

	$option = null;
	while(true)
	{
		if($option !== null)
			break;
		echo "Please select following ssid: \n";
		echo "You have to make sure you select this ssid to login.\n";
		echo "1. ntou( if you are in library)\n";
		echo "2. TANetRoaming(ntou, if you are in library and you want to use TANetRoaming)\n";
		echo "3. ap-nttu or CSE, CS AP-XXX...etc.(ntou), It's alos supported TANetRoaming\n";
		$option = trim(fgets($handle));
		if($option === "")
		{
			$option = null;
			echo "You have to select the option.\n";
		}
		else 
		{
			if((int)$option >= 4 || $option == 0)
			{
				$option = null;
				echo "You have to select 1,2, or 3 numbers.\n";
			}
		}
	}

	$email = null;
	$password = null;
	while (true)
	{
		if($email !== null && $password !== null)
			break;
		echo "Please input the email: ";
		$email = trim(fgets($handle));
		if($email === "")
		{
			$email = null;
			echo "\nYou have to input email.\n";
		}
		else
		{
			echo "\nPlease input the password: ";
			$password = trim(fgets($handle));
			if($password === "")
			{
				$password = null;
				echo "\nYou have to input password.\n";
			}
		}
	}

	if($option == 3)
	{
		echo auth_nttu($email, $password);
	}
	else if($option == 2)
	{
		echo auth_ntou("TANet", $email, $password);
	}
	else
	{
		echo auth_ntou("ntou", $email, $password);
	}

	function auth_ntou($str, $email, $password)
	{
		//$web_page = http_get("https://140.121.40.253/user/user_login_auth.jsp", $ref = "");
		//$web_page = $web_page["FILE"];
		//NTOU Libraries only supported this function

		if($str === "TANet")
		{
			//TANetRoaming
			echo "\nneed_auth\n";
			echo "The program is authing, please wait...\n";
			$data_arr = array();
			$data_arr["username"] = $email;
			$data_arr["password"] = $password;
			$response = http($target = "https://140.121.40.253/user/user_login_auth.jsp", $ref = "", $method = "POST", $data_arr, EXCL_HEAD);
			if($response["ERROR"]=="")
			{
				print_r($response);
				echo "auth_success";
				/*
				$web_page = http_get("http://google.com.tw", $refer = "");
				$web_page = $web_page["FILE"];

				if(stristr($web_page, "Either your user name or password is incorrect. Please try again."))
				{
					print_r($response["ERROR"]);
					echo "auth_fail";
				}
				else
					echo "auth_success";
				*/
			}
			else
			{
				echo "It's auth or you are not in this wireless access point.\n";
			}
		}
		/*
		else
		{
			//ntou
		}
		*/
	}

	function auth_nttu($email, $password)
	{
		$web_page = http_get("http://google.com.tw", $refer = "");
		$web_page = $web_page["FILE"];

		if(stristr($web_page, "台東大學無線網路驗證系統"))
		{
			echo "\nneed_auth\n";
			echo "The program is authing, please wait...\n";
			$data_arr = array();
			//user input username and password
			$data_arr["username"] = $email;
			$data_arr["password"] = $password;
			$data_arr["4Tredir"] = "http://google.com.tw";
			$parse_arr = parse_array($web_page," <input ",">");
			foreach ($parse_arr as $value) 
			{
				if(stristr($value, "magic"))
					$magic = trim(get_attribute($value, "value"));
			}
		
			$action = "";
			$check_common = "";
			$action_arr = array("library"=>"http://10.1.230.254:1000/fgtauth?".$magic, 
				"engineering_teach"=>"http://www.gstatic.com/generate_204");
		
			foreach ($action_arr as $key => $value) 
			{
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
		else if(stristr($web_page, "USERNAME"))
		{
			echo "need_auth2\n";
                		echo "The program is authing, please wait...\n";
                		//It's avilable for this link. It's also supported TANetRoaming
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
			if($response["ERROR"]=="")
			{
				$web_page = http_get("http://google.com.tw", $refer = "");
				$web_page = $web_page["FILE"];

				if(stristr($web_page, "USERNAME"))
				{
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
