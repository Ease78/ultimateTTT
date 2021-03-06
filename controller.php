<?php
require_once('./dba.php');

session_start();


/* for copy and pasting
	if (!isset($json['']))
		error_response('missing argument', '');
*/

$REASON = array(
	//login function returned false for either incorrect username or password
	'bad login' => 'Invalid username or password',
	//register returned false because the username was taken
	'bad register' => 'Username already taken', 
	//logging in or registering when already signed in
	'logged in' => "User '".(isset($_SESSION['currentUser'])?$_SESSION['currentUser']:'')."' already logged in",
	//logging out but no one is signed in
	'logged out' => 'No user logged in',
	//if old password is wrong when changing password
	'bad password' => 'Invalid password'
);


//handles errors like no input or invalid input
function error_response($err, $extra=null) {
	if ($extra != null)
		$err .= ": ".$extra;
	echo $err;
}

//get the json data from the webpage
$json = json_decode(file_get_contents('php://input'), true);

//make sure json data isn't empty
if (!is_array($json))
	error_response('not an array');

//make sure the 'request' value is set
if (!isset($json['request']))
	error_response('no request name');


$dba = new DBAdapter();
//for getting current user:
//input: {request}
//output: {exists, username}
//  exists: boolean, true is there is a current user, false otherwise
//  username: string, the current user's username or null if there is no current user
if ($json['request'] == 'current user') {
	//gets current user, if there is one
	if (isset($_SESSION['currentUser']))
		echo json_encode(array('exists' => true, 'username' => $_SESSION['currentUser']));
	else
		echo json_encode(array('exists' => false, 'username' => ''));
} else
//for registering user:
//input: {request, username, password}
//  username: string, the user's desired username
//  password: string, the user's desired password
//output: {success}
//  success: boolean, true if the user has been registered, false otherwise
//  reason: string, the reason success is false or empty is success is true
if ($json['request'] == 'register') {
	//check required variables
	if (!isset($json['username']))
		error_response('missing argument', 'username');
	if (!isset($json['password']))
		error_response('missing argument', 'password');
	
	//attempt registering user
	if (isset($_SESSION['currentUser']))
		echo json_encode(array('success' => false, 'reason' => $REASON['logged in']));
	else if ($dba->registerAccount($json['username'], $json['password']))
		echo json_encode(array('success' => true, 'reason' => ''));
	else
		echo json_encode(array('success' => false, 'reason' => $REASON['bad register']));
} else
//for logging user in: 
//input: {request, username, password}
//  username: string, the user's username
//  password: string, the user's password
//output: {success, reason}
//  success: boolean, true if the user was logged in, false otherwise
//  reason: string, the reason success is false or empty is success is true
if ($json['request'] == 'login') {
	//check required variables
	if (!isset($json['username']))
		error_response('missing argument', 'username');
	if (!isset($json['password']))
		error_response('missing argument', 'password');
	
	//attempt login
	if (isset($_SESSION['currentUser']))
		echo json_encode(array('success' => false, 'reason' => $REASON['logged in']));
	else {
		$validLogin = $dba->loginAccount($json['username'], $json['password']);
		if ($validLogin[0]) {
			$_SESSION['currentUser'] = $json['username']; //set session variable on successful login
			$_SESSION['currentUserId'] = $validLogin[1];
			echo json_encode(array('success' => true, 'reason' => ''));
		} else
			echo json_encode(array('success' => false, 'reason' => $REASON['bad login']));
	}
} else
//for recording game results:
//input: {request, result, gameType, opponent}
//
//output: success: false if user DNE
if ($json['request'] == 'push stats') {
	if (!isset($json['result']))
		error_response('missing argument', 'result');
	if (!isset($json['gameType']))
		error_response('missing argument', 'gameType');
	if (!isset($json['opponent']))
		error_response('missing argument', 'opponent');
	if (!isset($_SESSION['currentUser']))
		echo json_encode(array('success' => false));
	
	else if ($dba->saveGameData($_SESSION['currentUser'], $json['result'], $json['gameType'], $json['opponent']))
		echo json_encode(array('success' => true)); 
	else
		echo json_encode(array('success' => false));
} else
//for getting player statistics:
//input: {request}
//output: {...}
//
if ($json['request'] == 'get stats') {
	if (!isset($_SESSION['currentUser']))
		echo json_encode(array('success' => false));
	else
		echo json_encode(array_merge(array('success' => true), $dba->getGameData($_SESSION['currentUser'])));
} else
//for logging the user out
//input: {request}
//output: {success}
//  success: boolean, false iff no user was logged in
if ($json['request'] == 'logout') {
	//no database access needed, just unsetting the currentUser variable in the session
	if (isset($_SESSION['currentUser'])) {
		session_destroy();
		echo json_encode(array('success' => true));
	} else
		echo json_encode(array('success' => false));
} else
//for changing a user's password
//input: {request, oldPassword, newPassword}
//  oldPassword: string, the user's current password
//  newPassword: string, the user's new password
//output: {success, reason}
//  success: boolean, true if user's password was changed, false otherwise
//  reason: string, reason success is false, empty if success is true
if ($json['request'] == 'change password') {
	if (!isset($json['oldPassword']))
		error_response('missing argument', 'oldPassword');
	if (!isset($json['newPassword']))
		error_response('missing argument', 'newPassword');
	if (!isset($_SESSION['currentUser']))
		echo json_encode(array('success' => false, 'reason' => $REASON['logged out']));
	
	//attempt password change
	else if ($dba->changePassword($_SESSION['username'], $json['oldPassword'], $json['newPassword']))
		echo json_encode(array('success' => true, 'reason' => ''));
	else
		echo json_encode(array('success' => false, 'reason' => $REASON['bad password']));
} else
	error_response('invalid request', $json['request']); //when the request isn't recognised



?>