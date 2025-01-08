function validateName(name) {
	let nameRegEx = /^[a-zA-Z]+$/;

	if (nameRegEx.test(name))
		return true;
	else
		return false;
}

function validateDOB(dob) {
	// yyyy-mm-dd
	let dobRegEx = /^\d{4}[-]\d{2}[-]\d{2}$/;

	if (dobRegEx.test(dob))
		return true;
	else
		return false;
}
function validateAvatar(avatar) {

	let avatarRegEx = /^[^\n]+.[a-zA-Z]{3,4}$/;

	if (avatarRegEx.test(avatar))
		return true;
	else
		return false;

}
function validateUsername(uname) {

	let unameRegEx = /^[a-zA-Z0-9_]+$/;
	if (unameRegEx.test(uname))
		return true;
	else
		return false;
}


function validateLogin(event) {

	let uname = document.getElementById("username");
	let pwd = document.getElementById("password");
	let flag = true;

	if (!validateUsername(uname.value)) {
		document.getElementById(uname.id).classList.add("input-error");
		document.getElementById("error-text-" + uname.id).classList.remove("hidden");
		flag = false;
	}
	else {
		document.getElementById(uname.id).classList.remove("input-error");
		document.getElementById("error-text-" + uname.id).classList.add("hidden");
	}
	if (pwd.value.length !== 8) {
		document.getElementById(pwd.id).classList.add("input-error");
		document.getElementById("error-text-" + pwd.id).classList.remove("hidden");
		flag = false;
	}
	else {
		document.getElementById(pwd.id).classList.remove("input-error");
		document.getElementById("error-text-" + pwd.id).classList.add("hidden");
	}

	if (flag === false)
		event.preventDefault();
	else
		console.log("validation successfull, sending data to the server");
}

function fNameHandler(event) {
	let fname = event.target;
	if (!validateName(fname.value)) {
		console.log("'" + fname.value + "' is not a valid first name");
	}
}
function lNameHandler(event) {
	let lname = event.target;
	if (!validateName(lname.value)) {
		console.log("'" + lname.value + "' is not a valid last name");
	}
}

function usernameHandler(event) {
	let uname = event.target;
	if (!validateUsername(uname.value)) {
		document.getElementById(uname.id).classList.add("input-error");
		document.getElementById("error-text-" + uname.id).classList.remove("hidden");
	}
	else {
		document.getElementById(uname.id).classList.remove("input-error");
		document.getElementById("error-text-" + uname.id).classList.add("hidden");
	}
}
function pwdHandler(event) {
	let pwd = event.target;
	if (pwd.value.length !== 8) {
		console.log("Password should be exactly 8 characters long");
	}
}
function cpwdHandler(event) {
	let pwd = document.getElementById("password");
	let cpwd = event.target;
	if (pwd.value !== cpwd.value) {
		console.log("Your passwords: " + pwd.value + " and " + cpwd.value + " do not match");
	}
}
function dobHandler(event) {
	let dob = event.target;
	if (!validateDOB(dob.value)) {
		document.getElementById(dob.id).classList.add("input-error");
		document.getElementById("error-text-" + dob.id).classList.remove("hidden");
	}
	else {
		document.getElementById(dob.id).classList.remove("input-error");
		document.getElementById("error-text-" + dob.id).classList.add("hidden");
	}
}
function avatarHandler(event) {
	let avatar = event.target;
	if (!validateAvatar(avatar.value)) {
		console.log("'" + avatar.value + "' is not a valid avatar");
		flag = false;
	}
}

///////////////////////////////////////////////////////////////////////////////
// Code for Lab 11 starts here
function showLastLogin(event) {

	// TODO 4b: Get the username from the event target
	let username = event.target.value;

	if (username.length > 0) {
		let xhr = new XMLHttpRequest();
		xhr.onreadystatechange = function () {
			if (xhr.readyState == 4 && xhr.status == 200) {

				let loginHistoryArray = null;
				// TODO 6a: Parse the response text into JSON format and keep it on the 'loginHistoryArray' variable;
				loginHistoryArray = JSON.parse(this.responseText);

				let lastLoginDiv = document.getElementById("last-login");
				lastLoginDiv.innerHTML = '';
				if (loginHistoryArray.length > 0) {
					let usernameFromBackendData = loginHistoryArray[0].username;
					let pTag = document.createElement("p");
					pTag.textContent = username + " last logged in on:";
					lastLoginDiv.append(pTag);

					if (username == usernameFromBackendData) {

						// TODO 6b: Complete the logic in the for loop to iterate all items of the 'loginHistoryArray' array.
						for (let i = 0; i < loginHistoryArray.length; i++) {

						let jsonObject = loginHistoryArray[i];
						let loginTime = jsonObject.login_time;

						// TODO 6c: create p tag for each loginTime and append that tag as a child of the lastLoginDiv.  
						let pTag = document.createElement("p");
						pTag.textContent = loginTime;
						lastLoginDiv.append(pTag);

						// TODO 6b: Loop Ends
						}
					}
				} else {
					const pTag = document.createElement("p");
					const textnode = document.createTextNode("No previous login found for " + username + ".");
					pTag.appendChild(textnode);
					lastLoginDiv.appendChild(pTag);
				}
			}
		}

		// TODO 4c: Open and send a GET ajax request including the username to the 'ajax_backend.php' file. 
		xhr.open("GET", "ajax_backend.php?username=" + username, true);
		xhr.send();
	} else {
		let lastLoginText = document.getElementById("last-login");
		lastLoginText.innerHTML = "";
	}
}