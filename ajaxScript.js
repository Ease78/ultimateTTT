var connErr = 'connection error';

//general request method
//dataObj is a json object holding all parameters to send
//responseObj is a json object that holds the response from the server
//  it has 2 fields: response and error
//    response is the response json returned by the server
//    error is false by default and true is the request status is not 200 ( OK ) 
function sendRequest(dataObj, responseObj) {
	var ajax = new XMLHttpRequest();
	ajax.open("POST", "controller.php", true);
	ajax.setRequestHeader("Content-type", "application/json");
	ajax.send(JSON.stringify(dataObj));
	
	ajax.onreadystatechange = function() {
		if (ajax.readyState == 4) {
			if (ajax.status == 200)
				responseObj.response = JSON.parse(ajax.responseText);
			else
				responseObj.error = true;
		}
	}
}

//creates responseObj with correct fields and initializations
function createResponseObj() {
	return { response: {}, error: false };
}

//specific request functions
function login(user, pass) {
	var rsp = createResponseObj();
	sendRequest({ request: 'login', username: user, password: pass }, rsp);
	if (rsp.error)
		return connErr;
	return [rsp.response.success, rsp.response.reason];
}

function register(user, pass) {
	var rsp = createResponseObj();
	sendRequest({ request: 'register', username: user, password: pass }, rsp);
	if (rsp.error)
		return connErr;
	return [rsp.response.success, rsp.response.reason];
}

function logout() {
	var rsp = createResponseObj();
	sendRequest({ request: 'logout' }, rsp);
	if (rsp.error)
		return connErr;
	return rsp.response.success;
}

function changePass(oldPass, newPass) {
	var rsp = createResponseObj();
	sendRequest({ request: 'change password', oldPassword: oldPass, newPassword: newPass }, rsp);
	if (rsp.error)
		return connErr;
	return [rsp.response.success, rsp.response.reason];
}
