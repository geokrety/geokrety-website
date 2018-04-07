function zliczaj(maxlen) {
  var licznik = document.getElementById("licznik");
  var pole = document.getElementById("poledoliczenia");
  var tekst = pole.value; // the entire text of the textarea field
  var dlTresc = pole.value.length; // long text from a textarea
  var prawdziwa = 0; // character count
  var entery = 0;

  for (var i = 0; i < dlTresc; i++) {
    if (tekst.charAt(i) == "\n") {
      prawdziwa++;
      entery++;
      if (navigator.appName != "Netscape") {
        i++;
      }
    }
    prawdziwa++;
  } // end for i

  licznik.value = maxlen - prawdziwa; // display message on the number of characters
  if (prawdziwa > maxlen) {
    if (navigator.appName != "Netscape") {
      pole.value = pole.substring(0, maxlen);
    } else {
      pole.value = pole.value.substring(0, maxlen - entery);
    }
    licznik.value = 0;
    alert("max length " + maxlen + "!");
  }
}

//simor - imgup
function count_remaining(text_id, counter_id, maxlen) {
  var text = document.getElementById(text_id);
  var counter = document.getElementById(counter_id);

  if (text.value.length > maxlen)
    text.value = text.value.substring(0, maxlen);

  counter.innerHTML = '' + (
  maxlen - text.value.length);
}

//simor - imgup
function copy_value_to_innerHTML(in_id, out_id) {
  var ele_in = document.getElementById(in_id);
  var ele_out = document.getElementById(out_id);
  ele_out.innerHTML = ele_in.value;
}

// aktywuje odpowiednie pola raksa ;)

function RuchyPola(typ) {
  if (typ == 0 || typ == 3 || typ == 5) {
    $('#locationToggle').show();
    $('#step4').html('4.');
    $('#latlon').prop('disabled', false);
    $('#wpt').prop('disabled', false);
    $('#btn_sprawdzskrzynke').prop('disabled', false);
    $('#NazwaSkrzynki').prop('disabled', false);
    $('#getGeoLocation').prop('disabled', false);
    $('#logAtHome').prop('disabled', false);
  } else if (typ == 1 || typ == 2 || typ == 4 || typ == 6) {
    $('#locationToggle').hide();
    $('#step4').html('3.');
    $('#latlon').prop('disabled', true);
    $('#wpt').prop('disabled', true);
    $('#btn_sprawdzskrzynke').prop('disabled', true);
    $('#NazwaSkrzynki').prop('disabled', true);
    $('#getGeoLocation').prop('disabled', true);
    $('#logAtHome').prop('disabled', true);
  }
}

function sprawdzWpt(e) {

  if (e) {
    var KeyID = (window.event)
      ? event.keyCode
      : e.keyCode;
    if (KeyID == 9 || (KeyID >= 16 && KeyID <= 20) || (KeyID >= 33 && KeyID <= 40)) {
      return;
    }
  }

  var doc = document;
  var poleInId = 'wpt';
  var poleOutId = 'wynikWpt';

  var poleLatlon = doc.getElementById('latlon');
  var poleNazwaSkrzynk = doc.getElementById('NazwaSkrzynki');
  var poleAjaxStatus = doc.getElementById('ajax_status');

  var poleInValue = (doc.getElementById(poleInId).value).replace(/^\s\s*/, '').replace(/\s\s*$/, '');
  var poleOut = doc.getElementById(poleOutId);

  if (poleInValue.length >= 6) {
    if (e || (poleAjaxStatus.title != poleInValue)) {
      poleAjaxStatus.innerHTML = 'Retrieving...';
      poleAjaxStatus.title = poleInValue;
      $.get('szukaj-ajax.php', {
        'skad': 'ajax',
        'wpt': poleInValue
      }, function(data) {
        poleAjaxStatus.innerHTML = '';
        var json = JSON.parse(data);
        if (json.lat != '') {
          poleLatlon.value = json.lat + " " + json.lon;
          poleLatlon.disabled = false;
          //poleLatlon.disabled=true;
          poleNazwaSkrzynk.disabled = true;
          poleOut.innerHTML = json.tresc;
        } else {
          poleLatlon.disabled = false;
          poleLatlon.value = '';
          //tmp.focus();
          poleNazwaSkrzynk.disabled = true;
          poleOut.innerHTML = json.tresc;
        }
      });
    }
  } else {
    poleLatlon.disabled = false;
    poleLatlon.value = '';

    poleNazwaSkrzynk.disabled = false;
    poleNazwaSkrzynk.value = '';

    poleOut.innerHTML = '';
    poleAjaxStatus.title = '';
  }
}

function sprawdzNazwe() {
  var doc = document;
  var poleInId = "NazwaSkrzynki";
  var poleOutId = 'wynikWpt';

  var poleInValue = (doc.getElementById(poleInId).value).replace(/^\s\s*/, '').replace(/\s\s*$/, '');

  var poleWpt = $('#wpt');
  var poleLatlon = $('#latlon');
  var poleAjaxStatus = doc.getElementById('ajax_status');

  var poleOut = doc.getElementById(poleOutId);

  if (poleInValue.length >= 5) {
    poleOut.innerHTML = '';
    poleAjaxStatus.innerHTML = 'Retrieving...';
    poleAjaxStatus.title = poleInValue;
    $.get('szukaj-ajax.php', {
      'skad': 'ajax',
      'NazwaSkrzynki': poleInValue
    }, function(data) {
      poleAjaxStatus.innerHTML = '';
      var json = JSON.parse(data);
      if (json.IleSkrzynek == 1) {
        poleLatlon.prop('disabled', true);
        poleLatlon.val(json.lat + ' ' + json.lon);
        poleWpt.prop('disabled', true);
        poleWpt.val(json.wpt);
        poleOut.innerHTML = json.tresc;
      } else {
        poleLatlon.prop('disabled', false);
        poleLatlon.val('');
        poleWpt.prop('disabled', false);
        poleWpt.val('');
        poleOut.innerHTML = json.tresc;
      }
    });
  } else {
    poleWpt.prop('disabled', false);
    poleWpt.val('');

    poleLatlon.prop('disabled', false);
    poleLatlon.val('');

    poleOut.innerHTML = '';
    poleAjaxStatus.title = '';
    poleAjaxStatus.innerHTML = 'Enter at least 5 characters';
  }
  $('#NazwaSkrzynki').prop('disabled', false);
}

function sprawdzGK(e) {

  if (e) {
    var KeyID = (window.event)
      ? event.keyCode
      : e.keyCode;
    if (KeyID == 9 || (KeyID >= 16 && KeyID <= 20) || (KeyID >= 33 && KeyID <= 40)) {
      return;
    }
  }

  var poleInValue = (document.getElementById('nr').value).replace(/^\s\s*/, '').replace(/\s\s*$/, '');
  var poleOut = document.getElementById('wynikNr');

  //if(poleInValue.length == 6)
  if (/^[a-zA-Z0-9]{6}(\.[a-zA-Z0-9]{6})*$/.test(poleInValue)) {
    poleOut.innerHTML = 'Retrieving...';
    $.get('szukaj-ajax.php', {
      'skad': 'ajax',
      'nr': poleInValue
    }, function(data) {
      poleOut.innerHTML = data;
    });
  } else {
    poleOut.innerHTML = '';
  }
}

function CzySkasowac(theLink, tresc) {
  var isConfirmed = confirm('Are you sure you want to delete ' + tresc);
  if (isConfirmed) {
    theLink.href += '&confirmed=1';
  }

  return isConfirmed;
}

function Potwierdz(theLink, tresc) {
  var isConfirmed = confirm(tresc);
  if (isConfirmed) {
    theLink.href += '?confirmed=1';
  }
  return isConfirmed;
}
