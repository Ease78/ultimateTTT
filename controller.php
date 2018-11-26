<?php
require_once "./dba.php";

session_start();



//handles errors like no input or invalid input
function error_response($err, $extra=null) {
	
}

//get the json data from the webpage
$json = json_decode(file_get_contents('php://input'), true);

//make sure json data isn't empty
if (!is_array($json))
	error_response('not an array');

//make sure the 'request' value is set
if (!isset($json['request']))
	error_response('no request name');

$dba = new DBAdapter()
//for getting current user:
//input: {request}
//output: {exists, username}
//  exists: boolean, true is there is a current user, false otherwise
//  username: string, the current user's username or null if there is no current user
if ($json['request'] == 'currentUser') {
	//gets current user, if there is one
	if (isset($_SESSION['currentUser']))
		echo '{exists: true, username: \''.$_SESSION['currentUser'].'\'}';
	else
		echo '{exists: false, username: null}';
} else
//for registering user:
//input: {request, username, password}
//  username: string, the user's desired username
//  password: string, the user's desired password
//output: {success}
//  success: boolean, true if the user has been registered, false otherwise
if ($json['request'] == 'register') {
	//check required variables
	if (!isset($json['username']))
		error_response('missing argument', 'username');
	if (!isset($json['password']))
		error_response('missing argument', 'password');
	
	//attempt registering user
	if ($dba.registerAccount($json['username'], $json['password']))
		echo '{success: true}';
	else
		echo '{success: false}';
} else
//for logging user in: 
//input: {request, username, password}
//  username: string, the user's username
//  password: string, the user's password
//output: {success}
//  success: boolean, true if the user was logged in, false otherwise
if ($json['request'] == 'login') {
	//check required variables
	if (!isset($json['username']))
		error_response('missing argument', 'username');
	if (!isset($json['password']))
		error_response('missing argument', 'password');
	
	//attempt login
	if (isset($_SESSION['currentUser']))
		echo '{success: false, reason: \'user "'.$_SESSION['currentUser'].'" already logged in\'}'
	if ($dba.loginAccount($json['username'], $json['password'])) {
		$_SESSION['currentUser'] = $json['username']; //set session variable on successful login
		echo '{success: true, reason: \'\'}';
	} else
		echo '{success: false, reason: \'invalid username or password\'}';
} else
//for recording game results:
//input: {request, win, timeElapsed?}
//
//output: {}
if ($json['request'] == 'game end') {
	//record game data
	
} else
//for getting player statistics:
//input: {request}
//output: {...}
//
if ($json['request'] == 'player stats') {
	//get player statistics
	
} else
//for logging the user out
//input: {request}
//output: {}
if ($json['request'] == 'logout') {
	//no database access needed, just unsetting the currentUser variable in the session
	if (isset($_SESSION['currentUser']))
		unset($_SESSION['currentUser']);
	echo '';
} else
	error_response('invalid request', $json['request']); //when the request isn't recognised



?>