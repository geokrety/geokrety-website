//~ prepare map markers
// custom GK Icon (to improve with shadow?)
// https://leafletjs.com/examples/custom-icons/
var GKIcon = L.Icon.extend({
      options: {
          iconSize:     [12, 20],
          iconAnchor:   [6, 20],
          popupAnchor:  [0, -20]
          // shadowUrl: "leaf-shadow.png",
          // shadowSize:   [50, 64],
          // shadowAnchor: [4, 62]
      }
});
// start
var redIcon = new GKIcon({iconUrl: "https://cdn.geokrety.org/images/icons/pins/red.png"});
// trip points
var yellowIcon = new GKIcon({iconUrl: "https://cdn.geokrety.org/images/icons/pins/yellow.png"});
// recently seen
var greenIcon = new GKIcon({iconUrl: "https://cdn.geokrety.org/images/icons/pins/green.png"});

function getMapDataSample() {
  return [
    {"lat":"43.10150","lon":"-88.28948","htmlContent":"Hidden: 2017-10-11 12:00:00<br />in <a href=\"http://www.geocaching.com/seek/cache_details.aspx?wp=GC6QRCR\" target=\"_blank\">GC6QRCR</a><br />"},
    {"lat":"43.05797","lon":"-88.05373","htmlContent":"Hidden: 2017-10-03 12:00:00<br />in 43.05797/-88.05373"},
    {"lat":"43.05797","lon":"-88.05373","htmlContent":"Hidden: 2017-10-03 11:55:00<br />in 43.05797/-88.05373"},
    {"lat":"35.70210","lon":"139.77835","htmlContent":"Hidden: 2013-02-09 19:00:00<br />in <a href=\"http://www.geocaching.com/seek/cache_details.aspx?wp=GC44Q3M\" target=\"_blank\">GC44Q3M</a>"},
    {"lat":"51.02720","lon":"13.76465","htmlContent":"Hidden: 2013-01-06 14:30:00<br />in <a href=\"http://www.geocaching.com/seek/cache_details.aspx?wp=GC3F4BP\" target=\"_blank\">GC3F4BP</a>"},
    {"lat":"52.42368","lon":"13.03637","htmlContent":"Hidden: 2012-11-18 17:37:00<br />in 52.42368/13.03637"},
    {"lat":"52.31002","lon":"10.81525","htmlContent":"Hidden: 2012-09-25 12:00:00<br />in <a href=\"http://www.geocaching.com/seek/cache_details.aspx?wp=GC375D6\" target=\"_blank\">GC375D6</a>"}
  ];
}

function removeMarkers(plotlayers) {
    for (i=0;i<plotlayers.length;i++) {
        map.removeLayer(plotlayers[i]);
    }
    plotlayers=[];
}

function displayMapDataAsPlotLayers(map, plotList, lastSeenMessage) {
    var plotlayers=[];
    var group = new L.featureGroup();

    for (i=0;i<plotList.length;i++) {
      //~ create marker
      var markIcon = greenIcon; // last seen position
      if (i == plotList.length - 1) {
        markIcon = redIcon; // first position
      } else if (i > 0) {
        markIcon = yellowIcon;
      }
      var plot = new L.LatLng(plotList[i].lat,plotList[i].lon, true);
      var markOptions = {
            icon: markIcon,
            zIndexOffset: plotList.length-i // we want to see the last seen position marker (0 index)!
      }
      var plotmark = new L.Marker(plot, markOptions);
      plotmark.data=plotList[i];

      //~ add marker to the group
      plotmark.addTo(group);

      //~ bind popup
      if (markIcon == greenIcon) {
        plotmark.bindPopup(lastSeenMessage + plotList[i].htmlContent);
      } else {
        plotmark.bindPopup(plotList[i].htmlContent);
      }

      //~ add it as result layer
      plotlayers.push(plotmark);
      // DEBUG // console.debug("plot added",plotList[i]);

      //~ center the map on the last seen point
      if (i == 0) {
        map.setView([plotList[i].lat, plotList[i].lon], 4);
      }
    }
    group.addTo(map);
    map.fitBounds(group.getBounds());
    return plotlayers;
}

function displayMapDataAsPolyLayer(map, plotList) {
   var polyColor = "#004080";
   var polyWeight = 3;
   var polyPoints = []
   for (i=0;i<plotList.length;i++) {
     polyPoints.push(new L.LatLng(plotList[i].lat,plotList[i].lon));
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
  var osmUrl="http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";
  var osmAttrib="Map data © <a href=\"http://openstreetmap.org\">OpenStreetMap</a> contributors";
  var osm = new L.TileLayer(osmUrl, {minZoom: 0, maxZoom: 12, attribution: osmAttrib});

  // start the map in South-East England
  map.setView(new L.LatLng(51.3, 0.7),9);
  map.addLayer(osm);
  return map;
}


function initMapForGeokrety(geokretyId, errorLabel, lastSeenMessage) {
  console.debug("initMapForGeokrety",geokretyId);
  if (!Number.isInteger(geokretyId)) {
    console.error("unable to init map : initMapForGeokrety 'geokretyId' is not a number", geokretyId)
    $("#mapid").height(10).html(errorLabel);
    return;
  }
  var map = initMap();
  // var plotList = getMapDataSample();
  $.getJSON( "rest/konkret/trip/read.php?id="+geokretyId, function( tripResponse ) {
      if (!tripResponse.data) {
          console.error(tripResponse.details);
          return;
      }
      plotList = tripResponse.data;
      var plotLayers = displayMapDataAsPlotLayers(map, plotList, lastSeenMessage);
      var polyLayer = displayMapDataAsPolyLayer(map, plotList);
      //removeMarkers(plotLayers);
      console.info("map for geokrety ",geokretyId, " plot count ", plotLayers.length);
  });

};
