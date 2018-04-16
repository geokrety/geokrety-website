function passwordChanged() {
var strength = document.getElementById('strength');
var strongRegex = new RegExp("^(?=.{8,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*\\W).*$", "g");
var mediumRegex = new RegExp("^(?=.{7,})(((?=.*[A-Z])(?=.*[a-z]))|((?=.*[A-Z])(?=.*[0-9]))|((?=.*[a-z])(?=.*[0-9]))).*$", "g");
var enoughRegex = new RegExp("(?=.{5,}).*", "g");
var klucz = ' <img src="https://cdn.geokrety.org/images/icons/key.png" alt="key"/> ';
var pwd = document.getElementById("haslo1");
if (pwd.value.length==0) {
strength.innerHTML = '';
} else if (false == enoughRegex.test(pwd.value)) {
strength.innerHTML = '';
} else if (strongRegex.test(pwd.value)) {
strength.innerHTML = klucz+klucz+klucz+'<span style="color:green">Strong!</span>';
} else if (mediumRegex.test(pwd.value)) {
strength.innerHTML = klucz+klucz+'<span style="color:orange">Medium!</span>';
} else {
strength.innerHTML = klucz+'<span style="color:red">Weak!</span>';
}
}
