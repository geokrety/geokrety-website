var map;
var userid;

function load_data(page) {
	map.clearOverlays();

	var latNE = map.getBounds().getNorthEast().lat();
	var lonNE = map.getBounds().getNorthEast().lng();
	var latSW = map.getBounds().getSouthWest().lat();
	var lonSW = map.getBounds().getSouthWest().lng();
	var baseIcon = new GIcon();
	baseIcon.shadow = "https://cdn.geokrety.org/images/icons/pins/shadow.png";
	baseIcon.iconSize = new GSize(12, 20);
	baseIcon.shadowSize = new GSize(22, 20);
	baseIcon.iconAnchor = new GPoint(6, 20);
	baseIcon.infoWindowAnchor = new GPoint(5, 1);
	baseIcon.image = "https://cdn.geokrety.org/images/icons/pins/yellow.png"

	function createMarker(point, marker) {
		var geokret_id = marker.getAttribute("id");
		var geokret_name = marker.firstChild.nodeValue;
		var geokret_distance = marker.getAttribute("dist");
		var geokret_owner = marker.getAttribute("owner");
		var geokret_owner_id = marker.getAttribute("owner_id");
		var geokret_image = marker.getAttribute("image");
		var geokret_waypoint = marker.getAttribute("waypoint");
		var geokret_lon = marker.getAttribute("lon");
		var geokret_lat = marker.getAttribute("lat");
		var icon = new GIcon(baseIcon);
		if (geokret_distance == 0) { icon.image = "https://cdn.geokrety.org/images/icons/pins/1.png"; }
		else if (geokret_distance < 100) { icon.image = "https://cdn.geokrety.org/images/icons/pins/2.png"; }
		else if (geokret_distance < 200) { icon.image = "https://cdn.geokrety.org/images/icons/pins/3.png"; }
		else if (geokret_distance < 500) { icon.image = "https://cdn.geokrety.org/images/icons/pins/4.png"; }
		else if (geokret_distance < 1000) { icon.image = "https://cdn.geokrety.org/images/icons/pins/5.png"; }
		else { icon.image = "https://cdn.geokrety.org/images/icons/pins/6.png"; }

	var marker = new GMarker(point, icon);
	GEvent.addListener(marker, "click", function() {
		var geokret_image_html = (geokret_image!="") ? "<div id=\"gk_map_image\"><img src=\"https://cdn.geokrety.org/images/obrazki-male/"+geokret_image+"\"/></div>" : "";
		var geokret_location_html = "<img id=\"gk_map_cache\" src=\"https://cdn.geokrety.org/images/icons/cache.gif\" />" + ((geokret_waypoint!="") ? "<a href=\"https://geokrety.org/go2geo/index.php?wpt="+geokret_waypoint+"\">"+geokret_waypoint+"</a>" : geokret_lat+"/"+geokret_lon);
		var geokret_distance_html = "<img id=\"gk_map_dist\" src=\"https://cdn.geokrety.org/images/icons/dist.gif\" />"+geokret_distance+" km";
		marker.openInfoWindowHtml("<table id=\"gk_map_table\"><tr><td class=\"gk_map_left\"><img src=\"https://cdn.geokrety.org/images/log-icons/0/icon_25.jpg\" style=\"vertical-align:middle;\"> <a href=\"https://geokrety.org/konkret.php?id="+geokret_id+"\">"+geokret_name+"</a><br/><br/>"+geokret_location_html+"<br/>"+geokret_distance_html+"</td><td class=\"gk_map_right\">"+geokret_image_html+"</td></tr></table>");

	});
	return marker;
}

//var homeIcon = new GIcon(G_DEFAULT_ICON);
//homeIcon.image = "templates/icons_mapka/home.png";
//markerOptions = { icon:homeIcon };
var point = new GLatLng(center_lat,center_lon);
var marker = new GMarker(point);
map.addOverlay(marker);


	var num=0;
	var request = GXmlHttp.create();
	//request.open("GET", "export_mapka_kretow.php", true);
	request.open("GET", adresxml+"&latNE="+latNE+"&lonNE="+lonNE+"&latSW="+latSW+"&lonSW="+lonSW, true);
	request.onreadystatechange = function() {
		if (request.readyState == 4) {
			var xmlDoc = request.responseXML;
			var markers = xmlDoc.documentElement.getElementsByTagName("geokret");
			var old = 0;

			for (var i = 0; i < markers.length; i++)
			{
				var lat = markers[i].getAttribute("lat");
				var lng = markers[i].getAttribute("lon");
				if(lat*lng == 0 || markers[i].getAttribute("type")==2 || lat > latNE || lat < latSW || lng > lonNE || lng < lonSW)
					continue;
				var point = new GLatLng(lat, lng);
				var marker = createMarker(point, markers[i]);
				map.addOverlay(marker);
				num++;
			}
			document.getElementById("number").innerHTML="Visible GeoKrets: "+num+".";

		}
	}

	request.send(null);
}

function load() {
	if (GBrowserIsCompatible()) {
		map = new GMap2(document.getElementById("map"));
		map.addControl(new GLargeMapControl());
		map.removeMapType(G_HYBRID_MAP);
		map.addMapType(G_PHYSICAL_MAP);
		map.addControl(new GMapTypeControl());
		map.addControl(new GOverviewMapControl());

	map.setCenter(new GLatLng(center_lat,center_lon),8,G_PHYSICAL_MAP);

		GEvent.addListener(map, "zoomend", function() {
		load_data(0);
		});
		GEvent.addListener(map, "dragend", function() {
			load_data(0);
		});

      load_data(0);
		}

}
