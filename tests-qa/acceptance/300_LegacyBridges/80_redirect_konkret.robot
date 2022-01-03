*** Settings ***
Library         RequestsLibrary
Resource        ../functions/FunctionsGlobal.robot
Force Tags      Redirect    legacy    konkret
Suite Setup     Seed

*** Test Cases ***

At Least One Parameter Is Required
    Create Session     geokrety          ${GK_URL}
    GET On Session     geokrety          /konkret.php         expected_status=400

Should Redirect To New Url - by id
    Go To Url                            url=${GK_URL}/konkret.php?id=1
    Location With Param Should Be        ${PAGE_GEOKRETY_DETAILS_URL}   gkid=GK0001

Should Redirect To New Url - by gkid - lowercase
    Go To Url                            url=${GK_URL}/konkret.php?gk=gk0001
    Location With Param Should Be        ${PAGE_GEOKRETY_DETAILS_URL}   gkid=GK0001

Should Redirect To New Url - by gkid - integer
    Go To Url                            url=${GK_URL}/konkret.php?gk=1
    Location With Param Should Be        ${PAGE_GEOKRETY_DETAILS_URL}   gkid=1

Wrong id numeric value
    Create Session     geokrety          ${GK_URL}
    GET On Session     geokrety          url=/konkret.php?id=asc        expected_status=400

*** Keywords ***

Seed
    Clear DB And Seed 2 users
    Seed 1 geokrety owned by 1
