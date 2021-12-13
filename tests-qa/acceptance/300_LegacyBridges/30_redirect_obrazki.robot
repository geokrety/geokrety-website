*** Settings ***
Library         RequestsLibrary
Resource        ../functions/FunctionsGlobal.robot
Test Setup      Seed
Force Tags      Redirect    legacy    obrazki

*** Test Cases ***

Should Redirect To Picture Download Link - By filename
    Create Session     geokrety          ${GK_URL}
    GET On Session     geokrety          /obrazki/fake-filename     expected_status=301  allow_redirects=${false}

Should Redirect To Picture Download Link - By key
    Create Session     geokrety          ${GK_URL}
    GET On Session     geokrety          /obrazki/fake-key          expected_status=301  allow_redirects=${false}

Non existent picture should result in 404
    Create Session     geokrety          ${GK_URL}
    GET On Session     geokrety          /obrazki/something.jpg     expected_status=404  allow_redirects=${false}

*** Keywords ***

Seed
    Clear DB And Seed 1 users
    Seed 1 geokrety owned by 1
    Go To Url                             ${PAGE_SEED_PICTURE_AVATAR}
