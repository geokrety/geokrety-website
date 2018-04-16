var OneDegLonToMArray = new Array(111195, 111177, 111127, 111042, 110924, 110771, 110585, 110366, 110112, 109825, 109505, 109151, 108764, 108344, 107891, 107405, 106887, 106336, 105752, 105136, 104488, 103809, 103097, 102355, 101581, 100776, 99941, 99075, 98179, 97252, 96297, 95312, 94298, 93255, 92184, 91085, 89958, 88803, 87622, 86414, 85179, 83919, 82633, 81322, 79986, 78626, 77241, 75834, 74403, 72949, 71474, 69976, 68457, 66918, 65358, 63778, 62178, 60560, 58923, 57269, 55596, 53907, 52202, 50480, 48744, 46992, 45226, 43446, 41653, 39848, 38030, 36201, 34360, 32509, 30649, 28779, 26900, 25013, 23118, 21216, 19308, 17394, 15475, 13551, 11622, 9691, 7756, 5819, 3880, 1940);

var OneDegLatToM = 111195;
var circle;
var rect;
var map;

function updateLatLon(latLng) {
  document.getElementById('latlon').value = Math.round(latLng.lat() * 100000) / 100000 + ' ' + Math.round(latLng.lng() * 100000) / 100000;
}

function updateRectangle(center, remove) {
  if (remove == true) {
    rect.setMap(null);
    return;
  } else {
    rect.setMap(map);
  }

  var lat = center.lat();
  var lon = center.lng();
  if (lat < 0)
    lat = lat * -1;
  var lat_rounded = Math.round(lat);
  if (lat_rounded == 90)
    lat_rounded = 89;
  var OneDegLonToM = OneDegLonToMArray[lat_rounded];

  var rad = document.getElementById('radius');
  var d_lat = (1 / OneDegLatToM) * rad.value * 1000;
  var d_lon = (1 / OneDegLonToM) * rad.value * 1000;

  rect.setBounds(new google.maps.LatLngBounds(new google.maps.LatLng(lat - d_lat, lon - d_lon), new google.maps.LatLng(lat + d_lat, lon + d_lon)));
}

function initialize() {

  var latLng = new google.maps.LatLng(initial_map_lat, initial_map_lon);

  map = new google.maps.Map(document.getElementById('map_canvas'), {
    zoom: initial_zoom,
    center: latLng,
    mapTypeId: google.maps.MapTypeId.ROADMAP,
    streetViewControl: false,
    scaleControl: true
  });

  // circle = new google.maps.Circle({
  // map: null,
  // radius: $map_promien,
  // strokeWeight: 1,
  // strokeColor: '#FF0000',
  // fillOpacity: 0.15
  // });

  rect = new google.maps.Rectangle({map: map, strokeWeight: 1, strokeColor: '#FF0000', fillOpacity: 0.15});

  rect.setBounds(new google.maps.LatLngBounds(new google.maps.LatLng(initial_lat1, initial_lon1), new google.maps.LatLng(initial_lat2, initial_lon2)));

  google.maps.event.addListener(map, 'drag', function() {
    var center = map.getCenter();
    updateLatLon(center);
    //circle.setCenter(center);
    updateRectangle(center);
    var reticule = document.getElementById('reticule');
    reticule.style.display = 'block';
  });

  //circle.setCenter(latLng);
}

google.maps.event.addDomListener(window, 'load', initialize);

function latlon_keyup(e) {
  if (e) {
    var KeyID = (window.event)
      ? event.keyCode
      : e.keyCode;
    if (KeyID == 9 || (KeyID >= 16 && KeyID <= 20) || (KeyID >= 33 && KeyID <= 40))
      return;
    }

  var latlon = document.getElementById('latlon');
  var reticule = document.getElementById('reticule');

  if (latlon.value == '') {
    reticule.style.display = 'none';
    updateRectangle(null, true);
  } else {
    var re = /([\+\-]?\d+\.?\d*)[\s\,\/]+([\+\-]?\d+\.?\d*)/.exec(latlon.value);
    if (re != null) {
      var nc = new google.maps.LatLng(re[1], re[2]);
      map.setCenter(nc);
      updateRectangle(nc);
      reticule.style.display = 'block';
      if (map.getZoom() <= 1)
        map.setZoom(8);
      }
    else {
      reticule.style.display = 'none';
    }

  }

  return true;
}

function radius_keyup(e) {
  if (e) {
    var KeyID = (window.event)
      ? event.keyCode
      : e.keyCode;
    if (KeyID == 9 || (KeyID >= 16 && KeyID <= 20) || (KeyID >= 33 && KeyID <= 40))
      return;
    }

  var rad = document.getElementById('radius');
  var radvalue = rad.value;
  var radrange = document.getElementById('radrange');

  if ((radvalue >= 0) && (radvalue <= 10)) {
    radrange.style.color = '';
    radrange.style.fontWeight = '';
    updateRectangle(map.getCenter());
  } else {
    radrange.style.color = '#ff0000';
    radrange.style.fontWeight = 'bold';
    updateRectangle(null, true);
  }
  return true;
}
