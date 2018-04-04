
/* global moment */

var imgExclamation = '<img src="https://cdn.geokrety.org/images/icons/exclamation.gif" border="0" />';
//var imgAccept = '<img src="https://cdn.geokrety.org/images/icons/accept.gif" border="0" />';
var imgAccept = '';

function validateUsername(e) {
  if (e) {
    var KeyID = (window.event)
      ? event.keyCode
      : e.keyCode;
    if (KeyID == 9 || (KeyID >= 16 && KeyID <= 20) || (KeyID >= 33 && KeyID <= 40))
      return;
    }

  var obj = document.getElementById('username');
  var img = document.getElementById('username_img');
  if (obj.value == "") {
    img.innerHTML = imgExclamation;
    setBorderColorRed(obj);
    return false;
  }
  img.innerHTML = imgAccept;
  setBorderColorGreen(obj);
  return true;
}
window['validateUsername'] = validateUsername;

function validateTC(e) {
  if (e) {
    var KeyID = (window.event)
      ? event.keyCode
      : e.keyCode;
    if (KeyID == 9 || (KeyID >= 16 && KeyID <= 20) || (KeyID >= 33 && KeyID <= 40))
      return;
    }

  var obj = document.getElementById('nr');
  var img = document.getElementById('nr_img');
  if (obj.value == "") {
    img.innerHTML = imgExclamation;
    setBorderColorRed(obj);
    return false;
  }
  img.innerHTML = imgAccept;
  setBorderColorGreen(obj);
  return true;
}
window['validateTC'] = validateTC;

function setBorderColorRed(obj) {
  obj.setAttribute('style', 'background-color: #FFaaaa;');
  //obj.style.border-color = '#FF0000';
}
window['setBorderColorRed'] = setBorderColorRed;

function setBorderColorGreen(obj) {
  //obj.setAttribute('style', 'border-color: #00FF00;')
  obj.setAttribute('style', 'background-color'); //: #666666;')
}
window['setBorderColorGreen'] = setBorderColorGreen;

// ----------------------------------------------------- geolocation -------------------------------------
function validateAddRuchy(thisForm) {
  var ret = false;
  ret = !validateUsername(null) || ret;
  ret = !validateTC(null) || ret;
  return !ret;
}
window['validateAddRuchy'] = validateAddRuchy;

function getLocation(trescLogu) {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(showPosition);
  } else {
    document.getElementById("wynikWpt").innerHTML = "<img src='https://cdn.geokrety.org/images/icons/error.png' alt='error' width='16' height='16' />  Geolocation is not supported by this browser.";
  }
}

function showPosition(position) {
  document.getElementById("latlon").value = position.coords.latitude + " " + position.coords.longitude;
  document.getElementById("wynikWpt").innerHTML = "<img src='https://cdn.geokrety.org/images/icons/ok.png' alt='OK' width='16' height='16' /> " + '<a href="https://maps.google.pl/maps?q=' + position.coords.latitude + "+" + position.coords.longitude + '">' + position.coords.latitude + " " + position.coords.longitude + '</a>';
  document.getElementById('wpt').disabled = true;
  document.getElementById('wpt').value = '';
  document.getElementById('btn_sprawdzskrzynke').disabled = true;
  document.getElementById('NazwaSkrzynki').disabled = true;
  document.getElementById('poledoliczenia').value = "[Geolocation] coords ±" + position.coords.accuracy + "; altitude: " + position.coords.altitude + " ±" + position.coords.altitudeAccuracy + "; timestamp: " + position.timestamp;
}
// ----------------------------------------------------- geolocation end -------------------------------------

function teraz() {
  var d = new Date();
  document.getElementById('minuta').value = d.getMinutes();
  document.getElementById('godzina').value = d.getHours();

  var day = moment();
  $('.input-group.date').datepicker('update', day.format('YYYY-MM-DD'));
}

function dzis() {
  var day = moment();
  $('.input-group.date').datepicker('update', day.format('YYYY-MM-DD'));
}
function wczoraj() {
  var day = moment().add(-1, 'day');
  $('.input-group.date').datepicker('update', day.format('YYYY-MM-DD'));
}
function przedwczoraj() {
  var day = moment().add(-2, 'day');
  $('.input-group.date').datepicker('update', day.format('YYYY-MM-DD'));
}

function logAtHomeFn(lat, lon, trescLogu) {
  document.getElementById('latlon').value = lat + ' ' + lon;
  document.getElementById('wpt').disabled = true;
  document.getElementById('wpt').value = '';
  document.getElementById('btn_sprawdzskrzynke').disabled = true;
  document.getElementById('NazwaSkrzynki').disabled = true;
  document.getElementById('poledoliczenia').value = trescLogu;
  document.getElementById("wynikWpt").innerHTML = "<img src='https://cdn.geokrety.org/images/icons/ok.png' alt='OK' width='16' height='16' /> " + '<a href="https://maps.google.pl/maps?q=' + lat + "+" + lon + '">' + lat + " " + lon + '</a>';

  teraz();
}
