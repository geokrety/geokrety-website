var img_exclamation = '<img src="templates/exclamation.gif" border="0" />';
//var img_accept = '<img src="templates/accept.gif" border="0" />';
var img_accept = '';

function validateLogin(e) {
  if (e) {
    var KeyID = (window.event)
      ? event.keyCode
      : e.keyCode;
    if (KeyID == 9 || (KeyID >= 16 && KeyID <= 20) || (KeyID >= 33 && KeyID <= 40))
      return;
    }

  var obj = document.getElementById('login');
  var img = document.getElementById('login_img');
  if (obj.value == "") {
    img.innerHTML = img_exclamation;
    setBorderColorRed(obj);
    return false;
  }
  img.innerHTML = img_accept;
  setBorderColorGreen(obj);
  return true;
}
window['validateLogin'] = validateLogin;

function validatePassword1(e) {
  if (e) {
    var KeyID = (window.event)
      ? event.keyCode
      : e.keyCode;
    if (KeyID == 9 || (KeyID >= 16 && KeyID <= 20) || (KeyID >= 33 && KeyID <= 40))
      return;
    }
  var haslo1 = document.getElementById('haslo1');
  var img1 = document.getElementById('haslo1_img');

  if (haslo1.value.length < 5) {
    img1.innerHTML = img_exclamation;
    setBorderColorRed(haslo1);
    return false;
  }
  img1.innerHTML = img_accept;
  setBorderColorGreen(haslo1);

  var haslo2 = document.getElementById('haslo2');
  var img2 = document.getElementById('haslo2_img');
  if (haslo2.value.length >= 5) {
    if ((haslo1.value != haslo2.value)) {
      img2.innerHTML = img_exclamation;
      setBorderColorRed(haslo2);
      return false;
    } else {
      img2.innerHTML = img_accept;
      setBorderColorGreen(haslo2);
    }
  }
  return true;
}
window['validatePassword1'] = validatePassword1;

function validatePassword2(e) {
  if (e) {
    var KeyID = (window.event)
      ? event.keyCode
      : e.keyCode;
    if (KeyID == 9 || (KeyID >= 16 && KeyID <= 20) || (KeyID >= 33 && KeyID <= 40))
      return;
    }
  var haslo1 = document.getElementById('haslo1');
  var haslo2 = document.getElementById('haslo2');
  var img1 = document.getElementById('haslo1_img');
  var img2 = document.getElementById('haslo2_img');

  if ((haslo1.value.length < 5) || (haslo2.value.length < 5) || (haslo1.value != haslo2.value)) {
    img2.innerHTML = img_exclamation;
    setBorderColorRed(haslo2);
    return false;
  }

  //img1.innerHTML = img_accept;
  setBorderColorGreen(haslo1);
  img2.innerHTML = img_accept;
  setBorderColorGreen(haslo2);
  return true;
}
window['validatePassword2'] = validatePassword2;

function validateEmail(e) {
  if (e) {
    var KeyID = (window.event)
      ? event.keyCode
      : e.keyCode;
    if (KeyID == 9 || (KeyID >= 16 && KeyID <= 20) || (KeyID >= 33 && KeyID <= 40))
      return;
    }

  var regex = /^[\S]+\@([\S]+\.)+[A-Za-z]{2,4}$/;
  var obj = document.getElementById('email');
  var img = document.getElementById('email_img');
  var email = obj.value;
  email = email.replace(/^\s\s*/, '');
  email = email.replace(/\s\s*$/, '');
  obj.value = email;

  if (email.length <= 5 || !email.match(regex)) {
    img.innerHTML = img_exclamation;
    setBorderColorRed(obj);
    return false;
  }
  img.innerHTML = img_accept;
  setBorderColorGreen(obj);
  return true;
}
window['validateEmail'] = validateEmail;

function validateCaptcha(e) {
  if (e) {
    var KeyID = (window.event)
      ? event.keyCode
      : e.keyCode;
    if (KeyID == 9 || (KeyID >= 16 && KeyID <= 20) || (KeyID >= 33 && KeyID <= 40))
      return;
    }
  var obj = document.getElementById('captcha');
  var img = document.getElementById('captcha_img');
  if (obj.value == "") {
    img.innerHTML = img_exclamation;
    setBorderColorRed(obj);
    return false;
  }
  img.innerHTML = img_accept;
  setBorderColorGreen(obj);
  return true;
}
window['validateCaptcha'] = validateCaptcha;

function setBorderColorRed(obj) {
  obj.setAttribute('style', 'border-color: #FF0000;')
  //obj.style.border-color = '#FF0000';
}
window['setBorderColorRed'] = setBorderColorRed;

function setBorderColorGreen(obj) {
  //obj.setAttribute('style', 'border-color: #00FF00;')
  obj.setAttribute('style', 'border-color: #666666;')
}
window['setBorderColorGreen'] = setBorderColorGreen;

function validateAddUser(thisForm) {
  var ret = false;
  ret = !validateLogin(null) || ret;
  ret = !validatePassword1(null) || ret;
  ret = !validatePassword2(null) || ret;
  ret = !validateEmail(null) || ret;
  ret = !validateCaptcha(null) || ret;
  return !ret;
}
window['validateAddUser'] = validateAddUser;

function passwordChanged() {
  var strength = document.getElementById('strength');
  var strongRegex = new RegExp("^(?=.{8,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*\\W).*$", "g");
  var mediumRegex = new RegExp("^(?=.{7,})(((?=.*[A-Z])(?=.*[a-z]))|((?=.*[A-Z])(?=.*[0-9]))|((?=.*[a-z])(?=.*[0-9]))).*$", "g");
  var enoughRegex = new RegExp("(?=.{5,}).*", "g");
  var klucz = ' <img src="https://cdn.geokrety.org/images/icons/key.png" alt="key"/> ';
  var pwd = document.getElementById("haslo1");
  if (pwd.value.length == 0) {
    strength.innerHTML = '';
  } else if (false == enoughRegex.test(pwd.value)) {
    strength.innerHTML = '';
  } else if (strongRegex.test(pwd.value)) {
    strength.innerHTML = klucz + klucz + klucz + '<span style="color:green">Strong!</span>';
  } else if (mediumRegex.test(pwd.value)) {
    strength.innerHTML = klucz + klucz + '<span style="color:orange">Medium!</span>';
  } else {
    strength.innerHTML = klucz + '<span style="color:red">Weak!</span>';
  }
}
window['passwordChanged'] = passwordChanged;
