function zliczaj(maxlen) {
var licznik = document.getElementById("licznik");
var pole = document.getElementById("poledoliczenia");
  var tekst = pole.value; // the entire text of the textarea field
  var dl_tresc = pole.value.length; // long text from a textarea
  var prawdziwa = 0; // character count
  var entery = 0;

  for (i=0; i<dl_tresc; i++) {
     if (tekst.charAt(i) == "\n") {
       prawdziwa++;
       entery++;
       if (navigator.appName != "Netscape") i++;
     }
     prawdziwa++;
  } // end for i

  licznik.value= maxlen - prawdziwa; // display message on the number of characters
  if (prawdziwa>maxlen) {
    if (navigator.appName != "Netscape")
      pole.value = pole.substring(0,maxlen);
    else pole.value = pole.value.substring(0,maxlen-entery);
    licznik.value = 0;
    alert("max length " + maxlen +"!");
  }
}

//simor - imgup
function count_remaining(text_id, counter_id, maxlen) {
	var text = document.getElementById(text_id);
	var counter = document.getElementById(counter_id);

	if (text.value.length > maxlen)
		text.value = text.value.substring(0, maxlen);

	counter.innerHTML = '' + (maxlen - text.value.length);
}

//simor - imgup
function copy_value_to_innerHTML(in_id, out_id) {
	var ele_in = document.getElementById(in_id);
	var ele_out = document.getElementById(out_id);
	ele_out.innerHTML = ele_in.value;
}

// aktywuje odpowiednie pola raksa ;)

function RuchyPola(typ){
var doc = document;
if(typ==0 || typ==3 || typ==5){
		doc.getElementById('latlon').disabled = false;
		doc.getElementById('wpt').disabled = false;
		doc.getElementById('btn_sprawdzskrzynke').disabled = false;
		doc.getElementById('NazwaSkrzynki').disabled = false;
		}
else if(typ==1 || typ==2 || typ==4 || typ==6) {
		doc.getElementById('latlon').disabled = true;
		doc.getElementById('wpt').disabled = true;
		doc.getElementById('btn_sprawdzskrzynke').disabled = true;
		doc.getElementById('NazwaSkrzynki').disabled = true;
	}
}

function sprawdzWpt(e) {

	if(e) {
		var KeyID = (window.event) ? event.keyCode : e.keyCode;
		if(KeyID==9 ||(KeyID>=16 && KeyID<=20) || (KeyID>=33 && KeyID<=40)) return;
	}

	var doc = document;
	var pole_in_id='wpt';
	var pole_out_id='wynikWpt';

	var pole_latlon = doc.getElementById('latlon');
	var pole_NazwaSkrzynk = doc.getElementById('NazwaSkrzynki');
	var pole_ajax_status = doc.getElementById('ajax_status');

	var pole_in_value = (doc.getElementById(pole_in_id).value).replace(/^\s\s*/, '').replace(/\s\s*$/, '');
	var pole_out = doc.getElementById(pole_out_id);

	if(pole_in_value.length >= 6)
	{
		if(e || (pole_ajax_status.title != pole_in_value))
		{
			pole_ajax_status.innerHTML='Retrieving...';
			pole_ajax_status.title=pole_in_value;
			$.get('szukaj-ajax.php', {'skad':'ajax', 'wpt' : pole_in_value },
				function(data)
				{
					pole_ajax_status.innerHTML='';
					var json = JSON.parse(data);
						if(json.lat != '')
						{
							pole_latlon.value=json.lat+" "+json.lon;
							pole_latlon.disabled=false;
							//pole_latlon.disabled=true;
							pole_NazwaSkrzynk.disabled=true;
							pole_out.innerHTML=json.tresc;
						}
						else
						{
							pole_latlon.disabled=false;
							pole_latlon.value='';
							//tmp.focus();
							pole_NazwaSkrzynk.disabled=true;
							pole_out.innerHTML=json.tresc;
						}
				}
			);
		}
	}
	else
	{
		pole_latlon.disabled=false;
		pole_latlon.value='';

		pole_NazwaSkrzynk.disabled=false;
		pole_NazwaSkrzynk.value='';

		pole_out.innerHTML='';
		pole_ajax_status.title='';
	}
}


function sprawdzNazwe() {
	var doc = document;
	var pole_in_id ="NazwaSkrzynki";
	var pole_out_id='wynikWpt';

	var	pole_in_value = (doc.getElementById(pole_in_id).value).replace(/^\s\s*/, '').replace(/\s\s*$/, '');;

	var pole_wpt = doc.getElementById('wpt');
	var pole_latlon = doc.getElementById('latlon');
	var pole_NazwaSkrzynk = doc.getElementById('NazwaSkrzynki');
	var pole_ajax_status = doc.getElementById('ajax_status');

	var pole_out = doc.getElementById(pole_out_id);

	if(pole_in_value.length >= 5)
	{
		pole_out.innerHTML='';
		pole_ajax_status.innerHTML='Retrieving...';
		pole_ajax_status.title=pole_in_value;
		$.get('szukaj-ajax.php', {'skad':'ajax', 'NazwaSkrzynki' : pole_in_value },
			function(data)
			{
				pole_ajax_status.innerHTML='';
				var json = JSON.parse(data);
					if(json.IleSkrzynek == 1)
					{
						pole_latlon.disabled=true;
						pole_latlon.value=json.lat+' '+json.lon;
						pole_wpt.value=json.wpt;
						pole_out.innerHTML=json.tresc;
					}
					else
					{
						pole_latlon.disabled=false;
						pole_latlon.value='';
						pole_wpt.disabled=false;
						pole_wpt.value='';
						pole_out.innerHTML=json.tresc;
					}
			}
		);
	}
	else
	{
		pole_wpt.disabled=false;
		pole_wpt.value='';

		pole_latlon.disabled=false;
		pole_latlon.value='';

		pole_out.innerHTML='';
		pole_ajax_status.title='';
		pole_ajax_status.innerHTML='Enter at least 5 characters';
	}

}

function sprawdzGK(e) {

	if(e) {
		var KeyID = (window.event) ? event.keyCode : e.keyCode;
		if(KeyID==9 || (KeyID>=16 && KeyID<=20) || (KeyID>=33 && KeyID<=40)) return;
	}

	var	pole_in_value = (document.getElementById('nr').value).replace(/^\s\s*/, '').replace(/\s\s*$/, '');
	var pole_out = document.getElementById('wynikNr');

	//if(pole_in_value.length == 6)
	if (/^[a-zA-Z0-9]{6}(\.[a-zA-Z0-9]{6})*$/.test(pole_in_value))
	{
		pole_out.innerHTML='Retrieving...';
		$.get('szukaj-ajax.php', {'skad':'ajax', 'nr' : pole_in_value },
			function(data)
			{
				pole_out.innerHTML=data;
			}
		);
	}
	else {
		pole_out.innerHTML='';
	}
}

function CzySkasowac(theLink, tresc)
{
    var is_confirmed = confirm('Are you sure you want to delete ' + tresc);
    if (is_confirmed) {
        theLink.href += '&confirmed=1';
    }

    return is_confirmed;
}

function Potwierdz(theLink, tresc){    var is_confirmed = confirm(tresc);
    if (is_confirmed) {
        theLink.href += '?confirmed=1';
    }
    return is_confirmed;}
