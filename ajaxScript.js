//general request method
//dataObj is a json object holding all parameters to send
//responseObj is a json object that holds the response from the server
//  it has 2 fields: response and error
//    response is the response json returned by the server
//    error is false by default and true is the request status is not 200 ( OK ) 
function sendRequest(dataObj, callback) {
	var ajax = new XMLHttpRequest();
	ajax.open("POST", "controller.php", true);
	ajax.setRequestHeader("Content-type", "application/json");
	ajax.send(JSON.stringify(dataObj));
	
	ajax.onreadystatechange = function() {
		if (ajax.readyState == 4 && ajax.status == 200)
			callback(ajax.responseText);
	}
}

/*
NOTE:
callback is an anonymous function written into the function call that contains the code that you want to run
its only parameter is the json returned by the request.
*/

//specific request functions ------------------

//login function
//in: username, password
function login(user, pass, callback) {
	sendRequest({ request: 'login', username: user, password: pass }, callback);
}

//registering function (create new user)
//in: username, password
function register(user, pass, callback) {
	sendRequest({ request: 'register', username: user, password: pass }, callback);
}

//logout function
//in: none
function logout(callback) {
	sendRequest({ request: 'logout' }, callback);
}

//change user's password function
//in: old password (for user validation), new password
function changePass(oldPass, newPass, callback) {
	sendRequest({ request: 'change password', oldPassword: oldPass, newPassword: newPass }, callback);
}

//get current user (from session)
//in: none
function currentUser(callback) {
	sendRequest({ request: 'current user' }, callback);
}

//todo create push game results function //not needed

function getStats(callback) {
	sendRequest({ request: 'get stats' }, callback);
}
