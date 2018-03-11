function sprawdzWpt(e) {

  if (e) {
    var KeyID = (window.event)
      ? event.keyCode
      : e.keyCode;
    if (KeyID == 9 || (KeyID >= 16 && KeyID <= 20) || (KeyID >= 33 && KeyID <= 40))
      return;
    }

  var doc = document;
  var pole_in_id = 'wpt';
  var pole_out_id = 'wynikWpt';

  var pole_ajax_status = doc.getElementById('ajax_status');

  var pole_in_value = (doc.getElementById(pole_in_id).value).replace(/^\s\s*/, '').replace(/\s\s*$/, '');
  var pole_out = doc.getElementById(pole_out_id);

  if (pole_in_value.length >= 6) {
    if (e || (pole_ajax_status.title != pole_in_value)) {
      pole_ajax_status.innerHTML = 'Retrieving...';
      pole_ajax_status.title = pole_in_value;
      $.get('szukaj-ajax.php', {
        'skad': 'ajax',
        'wpt': pole_in_value
      }, function(data) {
        pole_ajax_status.innerHTML = '';
        var json = JSON.parse(data);
        pole_out.innerHTML = json.tresc;
      });
    }
  } else {

    pole_out.innerHTML = '';
    pole_ajax_status.title = '';
  }
}

function zliczaj(maxlen) {
  var licznik = document.getElementById("licznik");
  var pole = document.getElementById("poledoliczenia");
  var tekst = pole.value; // ca٣y text z pola textarea
  var dl_tresc = pole.value.length; // d٣ugo^¬ tekstu z textarea
  var prawdziwa = 0; // licznik znakﻩw
  var entery = 0;

  for (i = 0; i < dl_tresc; i++) {
    if (tekst.charAt(i) == "\n") {
      prawdziwa++;
      entery++;
      if (navigator.appName != "Netscape")
        i++;
      }
    prawdziwa++;
  } // koniec for i

  licznik.value = maxlen - prawdziwa; // wy^¬wietl komunikat o licznie znakﻩw
  if (prawdziwa > maxlen) {
    if (navigator.appName != "Netscape")
      pole.value = pole.substring(0, maxlen);
    else
      pole.value = pole.value.substring(0, maxlen - entery);
    licznik.value = 0;
    alert("max length " + maxlen + "!");
  }
}
