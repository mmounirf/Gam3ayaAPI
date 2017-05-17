<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Firebase\JWT\JWT;

require '../vendor/autoload.php';



// database configuration
$config['db']['host']   = "localhost";
$config['db']['user']   = "root";
$config['db']['pass']   = "";
$config['db']['dbname'] = "gam3aya";
$config['secret_key'] = "OTQzZTEyNmJmYWJhYTFkOTA5ODQ1ZWM0MzI4N2YyNzM=";

$app = new \Slim\App(["settings" => $config]);



/////////////////////////
//login endpoint
////////////////////////////

$app->post('/login', function (Request $request, Response $response) {
	$headers = $request->getHeaders();
	$in_user = implode(", ", $headers["HTTP_USERNAME"]);
	$in_pass = implode(", ", $headers["HTTP_PASSWORD"]);
	$servername = $this->settings["db"]["host"];
	$username = $this->settings["db"]["user"];
	$password = $this->settings["db"]["pass"];
	$conn = new mysqli($servername, $username, $password ,$this->settings["db"]["dbname"]);
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	} 

	$sql = 'SELECT id FROM users WHERE user_name="'.$in_user.'" AND password="'.md5($in_pass).'"';
	$result = $conn->query($sql);
	//$isadmin = $conn->query("SELECT * FROM users WHERE ");

	if ($result->num_rows > 0) {
	    // output data of each row
	    while($row = $result->fetch_assoc()) {
	        // create a token

		    $tokenId    = base64_encode(mcrypt_create_iv(32));
		    $issuedAt   = time();
		    $notBefore  = $issuedAt + 0;             //Adding 10 seconds
		    $expire     = $notBefore + 60000;            // Adding 60 seconds
		    $serverName = "name"; // Retrieve the server name from config file
		    
		    /*
		     * Create the token as an array
		     */
		    $data = [
		        'iat'  => $issuedAt,         // Issued at: time when the token was generated
		        'jti'  => $tokenId,          // Json Token Id: an unique identifier for the token
		        'iss'  => $serverName,       // Issuer
		        'nbf'  => $notBefore,        // Not before
		        'exp'  => $expire,           // Expire
		        'data' => [                  // Data related to the signer user
		            'userId'   => $row['id']// userid from the users table
		        ]
		    ];

		     /*
		      * More code here...
		      */
		     $secretKey = $this->settings["secret_key"];
		    
		    /*
		     * Encode the array to a JWT string.
		     * Second parameter is the key to encode the token.
		     * 
		     * The output string can be validated at http://jwt.io/
		     */
		    $jwt = JWT::encode(
		        $data,      //Data to be encoded in the JWT
		        $secretKey, // The signing key
		        'HS256'     // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
		        );
		        
			$response->getBody()->write($jwt);
	    }
	} else {
	   	$response->getBody()->write("wrong data");
	}
			 return $response;

mysqli_close($conn);
});

///////////////////////
// users end point used by the admin 
////////////////////////////////////////
// returns json with all users data 
/////////////////////////////////////////////


$app->get('/users', function (Request $request, Response $response) {
	$servername = $this->settings["db"]["host"];
	$username = $this->settings["db"]["user"];
	$password = $this->settings["db"]["pass"];
	$conn = new mysqli($servername, $username, $password ,$this->settings["db"]["dbname"]);
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	} 
	$headers = $request->getHeaders();
	if(isset($headers["HTTP_TOKEN"])){
		$token = implode(", ", $headers["HTTP_TOKEN"]);
		// echo $token;
		$decoded = JWT::decode($token,$this->settings["secret_key"], array('HS256'));
		// print_r($decoded);
		$decoded_array = (array) $decoded;
		$decoded_data = (array) $decoded_array["data"];
		$userId = $decoded_data["userId"];
		$user_data = $conn->query('SELECT user_name FROM users WHERE id="'.$userId.'"');
		$row = $user_data->fetch_assoc();
	        if($row["user_name"] == 'amr'){
	        	$required_data =  $conn->query('SELECT id,full_name,email,phoneNo,facebook,addr,profilepic,reputation,joind_groups FROM users');
	        	$r_d_array = array();
	        	// print_r($required_data);
	        	while($row_r_d = $required_data->fetch_assoc()) {
	        		array_push($r_d_array, $row_r_d);
	        	}
	        		$response = $response->withJson($r_d_array);
	        		//json_encode($r_d_array);


	        }else{
	        	$response->getBody()->write("only admin authorized");
	        }

	}else{
	    $response->getBody()->write("not authorized");
	}
    return $response;
mysqli_close($conn);
});


////////////////////////
// user profile endpoint 
/////////////////////////////////
// returns json with current user's data
//////////////////////////////////////////


$app->get('/users/me', function (Request $request, Response $response) {
	$servername = $this->settings["db"]["host"];
	$username = $this->settings["db"]["user"];
	$password = $this->settings["db"]["pass"];
	$conn = new mysqli($servername, $username, $password ,$this->settings["db"]["dbname"]);
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	} 
	$headers = $request->getHeaders();
	if(isset($headers["HTTP_TOKEN"])){
		$token = implode(", ", $headers["HTTP_TOKEN"]);
		// echo $token;
		$decoded = JWT::decode($token,$this->settings["secret_key"], array('HS256'));
		// print_r($decoded);
		$decoded_array = (array) $decoded;
		$decoded_data = (array) $decoded_array["data"];
		$userId = $decoded_data["userId"];
		$user_data = $conn->query('SELECT id,user_name,full_name,email,phoneNo,facebook,addr,profilepic,reputation,joind_groups FROM users WHERE id="'.$userId.'"');
		$row = $user_data->fetch_assoc();
	    $response = $response->withJson($row);
	 }else{
	    $response->getBody()->write("who are you");
	}
    return $response;
mysqli_close($conn);
});

/////////////////////////
// user update data end point
//////////////////////////////////
// send me only data to be modefied of user // // must provide password to secure modification process
////////////////////////////////////////////////////////////////////////////////////////////////////////

$app->put('/users/me', function (Request $request, Response $response) {
	$servername = $this->settings["db"]["host"];
	$username = $this->settings["db"]["user"];
	$password = $this->settings["db"]["pass"];
	$conn = new mysqli($servername, $username, $password ,$this->settings["db"]["dbname"]);
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	} 
	$headers = $request->getHeaders();
	if(isset($headers["HTTP_TOKEN"])){
		$token = implode(", ", $headers["HTTP_TOKEN"]);
		//$password = implode(", ", $headers["HTTP_PASSWORD"]);
		// echo $token;
		$decoded = JWT::decode($token,$this->settings["secret_key"], array('HS256'));
		// print_r($decoded);
		$decoded_array = (array) $decoded;
		$decoded_data = (array) $decoded_array["data"];
		$userId = $decoded_data["userId"];

		$query = 'UPDATE `users` SET ';
		if(array_key_exists("HTTP_USERNAME",$headers))
		{	
			$newUserName = $conn->real_escape_string($headers["HTTP_USERNAME"][0]);
			$query .= '`user_name` = "'.$newUserName.'",';
		}
		if(array_key_exists("HTTP_PASSWORD",$headers))
		{	
			$newPassword = $conn->real_escape_string($headers["HTTP_PASSWORD"][0]);
			$query .= '`password` = "'.md5($newPassword).'",';
		}
		if(array_key_exists("HTTP_FULLNAME",$headers))
		{	
			$newFullName = $conn->real_escape_string($headers["HTTP_FULLNAME"][0]);
			$query .= '`full_name` = "'.$newFullName.'",';
		}
		if(array_key_exists("HTTP_PHONE",$headers))
		{	
			$newPhone = $conn->real_escape_string($headers["HTTP_PHONE"][0]);
			$query .= '`phoneNo` = "'.$newPhone.'",';
		}
		if(array_key_exists("HTTP_MAIL",$headers))
		{	
			$newMail = $conn->real_escape_string($headers["HTTP_MAIL"][0]);
			$query .= '`email` = "'.$newMail.'",';
		}
		if(array_key_exists("HTTP_ADDRESS",$headers))
		{	
			$newADDR = $conn->real_escape_string($headers["HTTP_ADDRESS"][0]);
			$query .= '`addr` = "'.$newADDR.'",';
		}
		if(array_key_exists("HTTP_FB",$headers))
		{	
			$newFB = $conn->real_escape_string($headers["HTTP_FB"][0]);
			$query .= '`facebook` = "'.$newFB.'",';
		}
		if(array_key_exists("HTTP_PP",$headers))
		{	
			$newPP = $conn->real_escape_string($headers["HTTP_PP"][0]);
			$query .= '`profilepic` = "'.$newPP.'",';
		}
		$query = substr($query, 0, -1) . ' WHERE id="'.$userId.'"';
		$conn->query($query);

		// $row = $user_data->fetch_assoc();
	    $response->getBody()->write("data updated");
	 }else{
	    $response->getBody()->write("who are you");
	}
    return $response;
mysqli_close($conn);
});

/////////////////////////
// add user
//////////////////////////////////
// send me only data to be modefied of user // // // not working upon request // must provide password to secure modification process
////////////////////////////////////////////////////////////////////////////////////////////////////////

$app->post('/users/join', function (Request $request, Response $response) {
	$servername = $this->settings["db"]["host"];
	$username = $this->settings["db"]["user"];
	$password = $this->settings["db"]["pass"];
	$conn = new mysqli($servername, $username, $password ,$this->settings["db"]["dbname"]);
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	} 
		$headers = $request->getHeaders();

		if(array_key_exists("HTTP_USERNAME",$headers)&&array_key_exists("HTTP_PASSWORD",$headers)&&array_key_exists("HTTP_FULLNAME",$headers)&&array_key_exists("HTTP_PHONE",$headers)&&array_key_exists("HTTP_MAIL",$headers)&&array_key_exists("HTTP_ADDRESS",$headers)&&array_key_exists("HTTP_FB",$headers)&&array_key_exists("HTTP_PP",$headers))
		{	
			$newUserName = $conn->real_escape_string($headers["HTTP_USERNAME"][0]);
			$newPassword = $conn->real_escape_string($headers["HTTP_PASSWORD"][0]);
			$newFullName = $conn->real_escape_string($headers["HTTP_FULLNAME"][0]);
			$newPhone = $conn->real_escape_string($headers["HTTP_PHONE"][0]);
			$newMail = $conn->real_escape_string($headers["HTTP_MAIL"][0]);
			$newADDR = $conn->real_escape_string($headers["HTTP_ADDRESS"][0]);
			$newFB = $conn->real_escape_string($headers["HTTP_FB"][0]);
			$newPP = $conn->real_escape_string($headers["HTTP_PP"][0]);
			$query = 'INSERT INTO `users`(`user_name`, `password`, `email`, `full_name`, `phoneNo`, `addr`, `facebook`, `profilepic`, `reputation`, `joind_groups`) VALUES ("'.$newUserName.'","'.md5($newPassword).'","'.$newMail.'","'.$newFullName.'","'.$newPhone.'","'.$newADDR.'","'.$newFB.'","'.$newPP.'","0","")';
			echo $query;
			$conn->query($query);
	    	$response->getBody()->write("Welcome");

		}else{
	    $response->getBody()->write("incomplete request");
	}
    return $response;
mysqli_close($conn);
});



///////////////////////
// groups end point used by the admin 
////////////////////////////////////////
// returns json with all groups data 
/////////////////////////////////////////////


$app->get('/groups', function (Request $request, Response $response) {
	$servername = $this->settings["db"]["host"];
	$username = $this->settings["db"]["user"];
	$password = $this->settings["db"]["pass"];
	$conn = new mysqli($servername, $username, $password ,$this->settings["db"]["dbname"]);
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	} 
	$headers = $request->getHeaders();
	if(isset($headers["HTTP_TOKEN"])){
		$token = implode(", ", $headers["HTTP_TOKEN"]);
		// echo $token;
		$decoded = JWT::decode($token,$this->settings["secret_key"], array('HS256'));
		// print_r($decoded);
		$decoded_array = (array) $decoded;
		$decoded_data = (array) $decoded_array["data"];
		$userId = $decoded_data["userId"];
		$user_data = $conn->query('SELECT user_name FROM users WHERE id="'.$userId.'"');
		$row = $user_data->fetch_assoc();
	        if($row["user_name"] == 'amr'){
	        	$required_data =  $conn->query('SELECT * FROM groups');
	        	$r_d_array = array();
	        	while($row_r_d = $required_data->fetch_assoc()) {
	        		array_push($r_d_array, $row_r_d);
	        	}
	        		$response = $response->withJson($r_d_array);
	        		//json_encode($r_d_array);


	        }else{
	        	$response->getBody()->write("only admin authorized");
	        }

	}else{
	    $response->getBody()->write("not authorized");
	}
    return $response;
mysqli_close($conn);
});



////////////////////////
// group endpoint 
/////////////////////////////////
// returns json with group data // for groups' users only
//////////////////////////////////////////


$app->get('/groups/{group}', function (Request $request, Response $response,$args) {
	$servername = $this->settings["db"]["host"];
	$username = $this->settings["db"]["user"];
	$password = $this->settings["db"]["pass"];
	$conn = new mysqli($servername, $username, $password ,$this->settings["db"]["dbname"]);
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	} 
	$headers = $request->getHeaders();
	if(isset($headers["HTTP_TOKEN"])){
		$token = implode(", ", $headers["HTTP_TOKEN"]);
		// echo $token;
		$decoded = JWT::decode($token,$this->settings["secret_key"], array('HS256'));
		// print_r($decoded);
		$decoded_array = (array) $decoded;
		$decoded_data = (array) $decoded_array["data"];
		$userId = $decoded_data["userId"];
		$group_data = $conn->query('SELECT * FROM groups WHERE id="'.$args["group"].'"');
		$row = $group_data->fetch_assoc();
		$group_users = explode( ",", $row["users"]);
		if(in_array($userId, $group_users)){
	    	$response = $response->withJson($row);
		}else{$response->getBody()->write("you must be a member");}
	 }else{
	    $response->getBody()->write("who are you");
	}
    return $response;
mysqli_close($conn);
});







/////////////////////////
// user update data end point
//////////////////////////////////
// send me only data to be modefied of user // // must provide password to secure modification process
////////////////////////////////////////////////////////////////////////////////////////////////
////////







$app->put('/groups/{group}', function (Request $request, Response $response,$args) {
	$servername = $this->settings["db"]["host"];
	$username = $this->settings["db"]["user"];
	$password = $this->settings["db"]["pass"];
	$conn = new mysqli($servername, $username, $password ,$this->settings["db"]["dbname"]);
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	} 
	$headers = $request->getHeaders();
	if(isset($headers["HTTP_TOKEN"])){
		$token = implode(", ", $headers["HTTP_TOKEN"]);
		// echo $token;
		$decoded = JWT::decode($token,$this->settings["secret_key"], array('HS256'));
		// print_r($decoded);
		$decoded_array = (array) $decoded;
		$decoded_data = (array) $decoded_array["data"];
		$userId = $decoded_data["userId"];
		$group_data = $conn->query('SELECT admin FROM groups WHERE id="'.$args["group"].'"');
		$row = $group_data->fetch_assoc();
		if($userId == $row["admin"]){
			$query = 'UPDATE `groups` SET ';
			if(array_key_exists("HTTP_GTITLE",$headers))
			{	
				$newGtitle = $conn->real_escape_string($headers["HTTP_GTITLE"][0]);
				$query .= '`title` = "'.$newGtitle.'",';
			}
			if(array_key_exists("HTTP_GDESC",$headers))
			{	
				$newGdesc = $conn->real_escape_string($headers["HTTP_GDESC"][0]);
				$query .= '`descr` = "'.$newGdesc.'",';
			}
			if(array_key_exists("HTTP_GPAY",$headers))
			{	
				$newGpaypm = $conn->real_escape_string($headers["HTTP_GPAY"][0]);
				$query .= '`pay_per_month` = "'.$newGpaypm.'",';
			}
			if(array_key_exists("HTTP_USRS",$headers))
			{	
				$newUSRS= $conn->real_escape_string($headers["HTTP_USRS"][0]);
				$query .= '`users` = "'.$newUSRS.'",';
			}
			$query = substr($query, 0, -1) . ' WHERE id="'.$args["group"].'"';
			$conn->query($query);
	    	$response->getBody()->write("data updated");
		}else{
	 	  	$response->getBody()->write("404");
		}
			// print_r($row["admin"]);
	 }else{
	    $response->getBody()->write("who are you");
	}
    return $response;
mysqli_close($conn);
});







////////////////////////
// create group endpoint 
/////////////////////////////////
// returns json with group data // for groups' users only
//////////////////////////////////////////


$app->post('/me/create_group', function (Request $request, Response $response,$args) {
	$servername = $this->settings["db"]["host"];
	$username = $this->settings["db"]["user"];
	$password = $this->settings["db"]["pass"];
	$conn = new mysqli($servername, $username, $password ,$this->settings["db"]["dbname"]);
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	} 
	$headers = $request->getHeaders();
	if(isset($headers["HTTP_TOKEN"])){
		$token = implode(", ", $headers["HTTP_TOKEN"]);
		// echo $token;
		$decoded = JWT::decode($token,$this->settings["secret_key"], array('HS256'));
		// print_r($decoded);
		$decoded_array = (array) $decoded;
		$decoded_data = (array) $decoded_array["data"];
		$userId = $decoded_data["userId"];
		if(array_key_exists("HTTP_GTITLE",$headers)&&array_key_exists("HTTP_GDESC",$headers)&&array_key_exists("HTTP_GPAY",$headers))
		{
			$newGtitle = $conn->real_escape_string($headers["HTTP_GTITLE"][0]);
			$newGdesc = $conn->real_escape_string($headers["HTTP_GDESC"][0]);
			$newGpaypm = $conn->real_escape_string($headers["HTTP_GPAY"][0]);
			$conn->query("INSERT INTO `groups` (`title`, `descr`, `admin`, `users`, `pay_per_month`, `flag_next`, `status`) VALUES ('".$newGtitle."', '".$newGdesc."', '".$userId."', '".$userId."', ".$newGpaypm.", 0, '')");
			$group_data = $conn->query('SELECT * FROM groups WHERE title="'.$newGtitle.'"');
			$row = $group_data->fetch_assoc();
			$conn->query("UPDATE `users` SET `joind_groups` = CONCAT(`joind_groups`,'".$row['id'].",') WHERE `users`.`id` = ".$userId);

			$response = $response->withJson($row);

		}else{
			$response->getBody()->write("incomplete");
		}
	 }else{
	    $response->getBody()->write("who are you");
	}
    return $response;
mysqli_close($conn);
});



////////////////////////
// group endpoint 
/////////////////////////////////
// returns json with group data // for groups' users only
//////////////////////////////////////////


$app->get('/groups/{group}/invite', function (Request $request, Response $response,$args) {
	$servername = $this->settings["db"]["host"];
	$username = $this->settings["db"]["user"];
	$password = $this->settings["db"]["pass"];
	$conn = new mysqli($servername, $username, $password ,$this->settings["db"]["dbname"]);
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	} 
	$headers = $request->getHeaders();
	if(isset($headers["HTTP_TOKEN"])){
		$token = implode(", ", $headers["HTTP_TOKEN"]);
		// echo $token;
		$decoded = JWT::decode($token,$this->settings["secret_key"], array('HS256'));
		// print_r($decoded);
		$decoded_array = (array) $decoded;
		$decoded_data = (array) $decoded_array["data"];
		$userId = $decoded_data["userId"];
		$group_data = $conn->query('SELECT * FROM groups WHERE id="'.$args["group"].'"');
		$row = $group_data->fetch_assoc();
		$group_users = explode( ",", $row["users"]);
		if(in_array($userId, $group_users)){
	    	$length = 10;
			$inviteCode = "";
			$characters = "0123456789abcdefghijklmnopqrstuvwxyz";
			for ($p = 0; $p < $length; $p++) {
				$inviteCode .= $characters[mt_rand(0, strlen($characters))];
			}
			$final_code=$inviteCode."g".$args["group"];
			$conn->query("INSERT INTO `generated_codes` (`code`) VALUES ('".$final_code."')");
			$response->getBody()->write("http://localhost/slim-server/task2/task2/index.php/groups/join/".$final_code);
		}else{$response->getBody()->write("you must be a member");}
	 }else{
	    $response->getBody()->write("who are you");
	}
    return $response;
mysqli_close($conn);
});


$app->get('/groups/join/{code}', function (Request $request, Response $response,$args) {
	$servername = $this->settings["db"]["host"];
	$username = $this->settings["db"]["user"];
	$password = $this->settings["db"]["pass"];
	$conn = new mysqli($servername, $username, $password ,$this->settings["db"]["dbname"]);
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	} 
	$headers = $request->getHeaders();
	if(isset($headers["HTTP_TOKEN"])){
		$token = implode(", ", $headers["HTTP_TOKEN"]);
		// echo $token;
		$decoded = JWT::decode($token,$this->settings["secret_key"], array('HS256'));
		// print_r($decoded);
		$decoded_array = (array) $decoded;
		$decoded_data = (array) $decoded_array["data"];
		$userId = $decoded_data["userId"];
		$codes = $conn->query('SELECT * FROM generated_codes WHERE code="'.$args["code"].'"');
		if ($codes->num_rows  == 1) {
			$row = $codes->fetch_assoc();
			$group = substr($args["code"], 11);
	    	if( $conn->query("DELETE FROM `generated_codes` WHERE `generated_codes`.`id` = ".$row["id"]) === TRUE){
	    		$group_data = $conn->query('SELECT * FROM groups WHERE id='.$group);
				$row = $group_data->fetch_assoc();
				$group_users = explode( ",", $row["users"]);
				if(in_array($userId, $group_users)){
	    			$response->getBody()->write("already in");
				}
				else{
					if( $conn->query("UPDATE `users` SET `joind_groups` = CONCAT(`joind_groups`,'".$group.",') WHERE `users`.`id` = ".$userId)===TRUE){
						if( $conn->query("UPDATE `groups` SET `users` = CONCAT(`users`,'".$userId.",') WHERE `groups`.`id` = ".$group)===TRUE){
							 $joined_group_data = $conn->query('SELECT * FROM groups WHERE id='.$group);
							 $response->getBody()->write($joined_group_data->num_rows);
							if ($joined_group_data->num_rows  == 1) {
								$new_row = $joined_group_data->fetch_assoc();
								$response = $response->withJson($new_row);
							}
							// print_r('SELECT * FROM groups WHERE id='.$group);
						}
					}

				}
	    	}
		}else{
			$response->getBody()->write("invalid invitation");
		}
	 }else{
	    $response->getBody()->write("who are you");
	}
    return $response;
mysqli_close($conn);
});










$app->put('/groups/{group}/update_status', function (Request $request, Response $response,$args) {
	$servername = $this->settings["db"]["host"];
	$username = $this->settings["db"]["user"];
	$password = $this->settings["db"]["pass"];
	$conn = new mysqli($servername, $username, $password ,$this->settings["db"]["dbname"]);
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	} 
	$headers = $request->getHeaders();
	if(isset($headers["HTTP_TOKEN"])){
		$token = implode(", ", $headers["HTTP_TOKEN"]);
		$paid = implode(", ", $headers["HTTP_PAID"]);
		// echo $token;
		$decoded = JWT::decode($token,$this->settings["secret_key"], array('HS256'));
		// print_r($decoded);
		$decoded_array = (array) $decoded;
		$decoded_data = (array) $decoded_array["data"];
		$userId = $decoded_data["userId"];
		$group_data = $conn->query('SELECT * FROM groups WHERE id="'.$args["group"].'"');
		$row = $group_data->fetch_assoc();
		if($userId == $row["flag_next"]){
			$users = explode( ",", $row["users"]);
			$current_index = array_search($userId,$users);
			$payment_status = explode( "|", $row["status"]);
			$current_user_payment_status = explode(",",$payment_status[$current_index]);
			$current_user_payment_status[$paid] = 1;
			$current_string = implode(",",$current_user_payment_status);
			$payment_status[$current_index] = $current_string;
			$payment_string = implode("|",$payment_status);

			$conn->query('UPDATE `groups` SET `status` = "'.$payment_string.'" WHERE `groups`.`id` ='.$args["group"]);
			$response->getBody()->write("data written");
		}else{
	 	  	$response->getBody()->write("not ur cycle");
		}
	 }else{
	    $response->getBody()->write("who are you");
	}
    return $response;
mysqli_close($conn);
});



$app->run();

