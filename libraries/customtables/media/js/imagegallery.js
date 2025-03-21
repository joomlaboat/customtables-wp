function SelectAll(s) {

	for (let i = 0; i < idList.length; i++)
		document.getElementById("esphoto" + idList[i]).checked = s;
}

function SaveOrder() {
	document.getElementById("photoedit_task").value = "saveorder";
	document.getElementById("eseditphotos").submit();
}

function ShowAddPhoto() {
	const obj = document.getElementById("addphotoblock");
	if (obj.style.display == "block")
		obj.style.display = "none";
	else
		obj.style.display = "block";
}

function DeletePhotos(photoid) {
	let count = 0;
	let photoids = "";

	for (let i = 0; i < idList.length; i++) {
		if (document.getElementById("esphoto" + idList[i]).checked) {
			count++;
			photoids += "*" + idList[i];
		}
	}
	if (count === 0) {
		alert("Select photo(s) first.");
		return false;
	}

	if (confirm("Are you sure to delete " + count + " photo(s)?")) {
		document.getElementById("photoedit_task").value = "delete";
		document.getElementById("photoids").value = photoids;
		document.getElementById("eseditphotos").submit();
	}
	return true;
}
