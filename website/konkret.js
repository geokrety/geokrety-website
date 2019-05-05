//~ prepare map markers
// custom GK Icon (to improve with shadow?)
// https://leafletjs.com/examples/custom-icons/

// 'L' is provided by leafletjs library
var GKIcon = L.Icon.extend({
  options: {
    iconSize: [12, 20],
    iconAnchor: [6, 20],
    popupAnchor: [0, -20]
    // shadowUrl: "leaf-shadow.png",
    // shadowSize:   [50, 64],
    // shadowAnchor: [4, 62]
  }
});
// start
var redIcon = new GKIcon({
  iconUrl: "https://cdn.geokrety.org/images/icons/pins/red.png"
});
// trip points
var yellowIcon = new GKIcon({
  iconUrl: "https://cdn.geokrety.org/images/icons/pins/yellow.png"
});
// recently seen
var greenIcon = new GKIcon({
  iconUrl: "https://cdn.geokrety.org/images/icons/pins/green.png"
});

/*
    function removeMarkers(plotlayers) {
        for (i=0;i<plotlayers.length;i++) {
            map.removeLayer(plotlayers[i]);
        }
        plotlayers=[];
    }
*/

// create marker
function getPlotIcon(isFirstPosition, isLastPosition) {
  if (isFirstPosition) {
    return redIcon;
  }
  if (isLastPosition) {
    return greenIcon;
  }
  return yellowIcon;
}

function displayMapDataAsPlotLayers(map, plotList, lastSeenMessage) {
  var plotlayers = [];
  var group = new L.featureGroup();

  for (var i = 0; i < plotList.length; i++) {
    var lat = plotList[i].lat;
    var lon = plotList[i].lon;
    var popupContent = plotList[i].htmlContent;

    var markIcon = getPlotIcon((i === plotList.length - 1), (i === 0));
    var plot = new L.LatLng(lat, lon, true);
    var markOptions = {
      icon: markIcon,
      zIndexOffset: plotList.length - i // we want to see the last seen position marker (0 index)!
    };
    var plotmark = new L.Marker(plot, markOptions);
    plotmark.data = plotList[i];

    //~ add marker to the group
    plotmark.addTo(group);

    //~ bind popup
    if (markIcon == greenIcon) {
      popupContent = lastSeenMessage + popupContent;
    }
    plotmark.bindPopup(popupContent);

    //~ add it as result layer
    plotlayers.push(plotmark);
  }
  group.addTo(map);
  map.fitBounds(group.getBounds());
  return plotlayers;
}

function displayMapDataAsPolyLayer(map, plotList) {
  var polyColor = "#004080";
  var polyWeight = 3;
  var polyPoints = [];
  for (var i = 0; i < plotList.length; i++) {
    polyPoints.push(new L.LatLng(plotList[i].lat, plotList[i].lon));
  }
  var polyLine = new L.Polyline(polyPoints, {
    color: polyColor,
    weight: polyWeight,
    opacity: 0.5,
    smoothFactor: 1
  });
  polyLine.addTo(map);
  return polyLine;
}

function initMap() {
  var map = L.map("mapid");
  var osmUrl = "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";
  var osmAttrib = "Map data © <a href=\"https://www.openstreetmap.org\">OpenStreetMap</a> contributors";
  var osm = new L.TileLayer(osmUrl, {
    minZoom: 0,
    maxZoom: 12,
    attribution: osmAttrib
  });

  // start the map in South-East England
  map.setView(new L.LatLng(51.3, 0.7), 9);
  map.addLayer(osm);
  return map;
}

// used by konkret.php
function initMapForGeokrety(geokretyId, errorLabel, lastSeenMessage) {
  if (!Number.isInteger(geokretyId)) {
    console.error("unable to init map : initMapForGeokrety 'geokretyId' is not a number", geokretyId);
    $("#mapid").height(10).html(errorLabel);
    return;
  }
  var map = initMap();
  $.getJSON("rest/konkret/trip/read.php?id=" + geokretyId, function(tripResponse) {
    if (!tripResponse.data) {
      console.error(tripResponse.details);
      return;
    }
    var plotList = tripResponse.data;
    var plotLayers = displayMapDataAsPlotLayers(map, plotList, lastSeenMessage);
    displayMapDataAsPolyLayer(map, plotList);
    //removeMarkers(plotLayers);
    console.info("map for geokrety ", geokretyId, " plot count ", plotLayers.length);
  });

}
