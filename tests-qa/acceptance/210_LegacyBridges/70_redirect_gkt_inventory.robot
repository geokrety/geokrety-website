*** Settings ***
Library         RequestsLibrary
Resource        ../ressources/vars/Urls.robot

*** Test Cases ***

Should Redirect To New Url
    Go To Url                            ${GK_URL}/gkt/inventory_v3.php    redirect=${PAGE_LEGACY_GKT_INVENTORY_URL}

Should Redirect To Be A 302
    Create Session     geokrety          ${GK_URL}
    GET On Session     geokrety          /gkt/inventory_v3.php         expected_status=302  allow_redirects=${false}
