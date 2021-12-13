*** Settings ***
Library         RequestsLibrary
Resource        ../functions/FunctionsGlobal.robot
# Resource        ../vars/moves.resource
Force Tags      Redirect    legacy    gkt
# Test Setup      Seed

*** Test Cases ***

Should Redirect To New Url
    Go To Url                            ${GK_URL}/gkt/search_v3.php
    Location Should Be                   ${PAGE_LEGACY_GKT_SEARCH_URL}

Should Redirect To A 302
    Create Session     geokrety          ${GK_URL}
    GET On Session     geokrety          /gkt/search_v3.php         expected_status=302  allow_redirects=${false}

Unset lat/lon
    Go To Url                            ${GK_URL}/gkt/search_v3.php
    Location Should Be                   ${PAGE_LEGACY_GKT_SEARCH_URL}

Valid lat/lon
    Go To Url                            url=${GK_URL}/gkt/search_v3.php?lat=43&lon=6
    Location Should Be                   url=${PAGE_LEGACY_GKT_SEARCH_URL}?lat=43&lon=6

Empty lat/lon
    Go To Url                            url=${GK_URL}/gkt/search_v3.php?lat=&lon=
    Location Should Be                   url=${PAGE_LEGACY_GKT_SEARCH_URL}?lat=&lon=

# *** Keywords ***
#
# Seed
#     Clear DB And Seed 1 users
#     Seed 1 geokrety owned by 1
#     Post Move                            ${MOVE_1}
