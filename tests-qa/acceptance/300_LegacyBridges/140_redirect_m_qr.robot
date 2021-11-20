*** Settings ***
Library         SeleniumLibrary  timeout=10  implicit_wait=0
Library         RequestsLibrary
Resource        ../functions/FunctionsGlobal.robot
Resource        ../vars/geokrety.resource
Force Tags      Redirect    legacy    export_oc


*** Test Cases ***

Should parameter nr is mandatory
    Go To Url                            ${GK_URL}/m/qr.php
    Location Should Be                   ${GK_URL}/m/qr.php
    Page Should Contain                  "nr" parameter must be provided.

    Create Session     geokrety          ${GK_URL}
    GET On Session     geokrety          url=/m/qr.php      expected_status=400

Should Redirect To New Url
    Go To Url                            url=${GK_URL}/m/qr.php?nr=${GEOKRETY_1.tc}
    Location Should Be                   ${PAGE_MOVES_URL}?tracking_code=${GEOKRETY_1.tc}

Should Redirect To A 302
    Create Session     geokrety          ${GK_URL}
    GET On Session     geokrety          url=/m/qr.php?nr=${GEOKRETY_1.tc}      expected_status=302  allow_redirects=${false}
