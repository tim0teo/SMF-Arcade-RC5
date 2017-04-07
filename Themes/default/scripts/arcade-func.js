/*
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.5
 * @license http://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

 // Open a new game popup window
function myGamePopupArcade(strURL,strWidth,strHeight,displayType)
{
	var strOptions="";
	var WindowObjectReference = null;
	displayType = typeof displayType !== "undefined" ? parseInt(displayType) : 0;
	if (displayType < 0 || displayType > 5)
		displayType = 0;
	else
		displayType = isNaN(displayType) ? 0 : displayType;

	if (displayType > 2)
	{
		displayType = displayType-2;
		var changeWidthDims = 1.01;
		var changeHeightDims = 1.07;
	}
	else
	{
		var changeWidthDims = 1;
		var changeHeightDims = 1;
	}

	strWidth = strWidth*changeWidthDims;
	strHeight = strHeight*changeHeightDims;

	var strType = [
		"console",
		"fixed",
		"elastic",
		"adjust"
	];

	if (strType[displayType]=="fixed")
		strOptions = "status,height=" + strHeight + ",width=" + strWidth;
	else if (strType[displayType]=="elastic")
		strOptions = "toolbar,menubar,scrollbars,resizable,location,height=" + strHeight + ",width=" + strWidth;
	else if (strType[displayType]=="adjust")
		strOptions = "status,resizable,height=" + strHeight + ",width=" + strWidth;
	else
		strOptions = "resizable,height=" + strHeight + ",width=" + strWidth;

	WindowObjectReference = window.open(strURL, "newWin", strOptions);
	if (window.focus)
		WindowObjectReference.focus();

	WindowObjectReference.resizeTo(strWidth*1.035, strHeight*1.28);
}

function getUrlVarsArcade()
{
	var vars = {};
	var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
		vars[key] = value;
	});
	return vars;
}

function convertToDateArcade(variableEpoch)
{
	var sourceEpoch = variableEpoch;
	sourceEpoch = parseInt(sourceEpoch);
	if (isNaN(sourceEpoch)) {
		var arcadeDateDisplay = 'Invalid Timestamp';
	}
	else {
		if (sourceEpoch <= 9999999999) {
			sourceEpoch *= 1000;
		}
		var arcadeDateDisplay = new Date(sourceEpoch).toUTCString();
	}
	return arcadeDateDisplay;
}

function writeArcadeCookie(name,value,days)
{
	var date, expires;
	if (days)
	{
		date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		expires = "; expires=" + date.toGMTString();
	}
	else
		expires = "";

	document.cookie = name + "=" + value + expires + "; path=/";
}

function readArcadeCookie(name)
{
	var i, c, ca, nameEQ = name + "=";
	ca = document.cookie.split(";");
	for(i=0;i < ca.length;i++)
	{
		c = ca[i];
		while (c.charAt(0)==" ")
			c = c.substring(1,c.length);

		if (c.indexOf(nameEQ) == 0)
			return c.substring(nameEQ.length,c.length);
	}

	return "";
}

function submitArcadeSkin()
{
	document.getElementById("arcadeSkin").onchange = function() {
		document.forms["admin_form_wrapper"].submit();
	};
}