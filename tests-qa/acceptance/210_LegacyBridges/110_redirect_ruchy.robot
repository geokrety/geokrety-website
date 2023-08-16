*** Settings ***
Library         RequestsLibrary
Resource        ../ressources/vars/Urls.robot

*** Test Cases ***

Should Redirect To New Url
    Go To Url                            ${GK_URL}/ruchy.php    redirect=${PAGE_MOVES_URL}

Should Redirect To A 302
    Create Session     geokrety          ${GK_URL}
    GET On Session     geokrety          /ruchy.php       expected_status=302  allow_redirects=${false}

With Tracking Code
    Go To Url                            url=${GK_URL}/ruchy.php?nr=ABC132    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                   url=${PAGE_MOVES_URL}?tracking_code=ABC132

With Waypoint - wpt
    Go To Url                            url=${GK_URL}/ruchy.php?wpt=GC5BRQK    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                   url=${PAGE_MOVES_URL}?waypoint=GC5BRQK

With Waypoint - waypoint
    Go To Url                            url=${GK_URL}/ruchy.php?waypoint=GC5BRQK    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                   url=${PAGE_MOVES_URL}?waypoint=GC5BRQK

With Coordinates - latlon
    Go To Url                            url=${GK_URL}/ruchy.php?latlon=43 6    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                   url=${PAGE_MOVES_URL}?coordinates=43+6

With Coordinates - lat + lon
    Go To Url                            url=${GK_URL}/ruchy.php?lat=43&lon=6    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                   url=${PAGE_MOVES_URL}?coordinates=43+6

With Coordinates - lat
    Go To Url                            url=${GK_URL}/ruchy.php?lat=43    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                   url=${PAGE_MOVES_URL}

With Coordinates - lon
    Go To Url                            url=${GK_URL}/ruchy.php?lon=6    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                   url=${PAGE_MOVES_URL}

With Move Type - logtype
    Go To Url                            url=${GK_URL}/ruchy.php?logtype=1    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                   url=${PAGE_MOVES_URL}?move_type=1

With Move Type - gkt drop_gc
    Go To Url                            url=${GK_URL}/ruchy.php?gkt=drop_gc    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                   url=${PAGE_MOVES_URL}?move_type=0

Test GKT Links
    Go To Url                            url=${GK_URL}/ruchy.php?gkt=drop_gc&nr=ABC123&waypoint=GC8888&lat=43.6&lon=6.8    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                   url=${PAGE_MOVES_URL}?tracking_code=ABC123&waypoint=GC8888&coordinates=43.6+6.8&move_type=0

Test OpenCaching Links
    Go To Url                            url=${GK_URL}/ruchy.php?nr=ABC123&logtype=0&wpt=GC8888&latlon=43.69 6.86    redirect=${NO_REDIRECT_CHECK}
    Location Should Be                   url=${PAGE_MOVES_URL}?tracking_code=ABC123&waypoint=GC8888&coordinates=43.69+6.86&move_type=0
