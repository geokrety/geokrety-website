*** Settings ***
Library         RequestsLibrary
Resource        ../functions/FunctionsGlobal.robot
Force Tags      Redirect    legacy    konkret

*** Test Cases ***

At Least One Parameter Is Required
    Create Session     geokrety          ${GK_URL}
    GET On Session     geokrety          /konkret.php         expected_status=400

Should Redirect To New Url - by id
    Go To Url                            url=${GK_URL}/konkret.php?id=1234
    Location With Param Should Be        ${PAGE_GEOKRETY_DETAILS_URL}   gkid=GK04D2

Should Redirect To New Url - by gkid - lowercase
    Go To Url                            url=${GK_URL}/konkret.php?gk=gk07E5
    Location With Param Should Be        ${PAGE_GEOKRETY_DETAILS_URL}   gkid=GK07E5

Should Redirect To New Url - by gkid - integer
    Go To Url                            url=${GK_URL}/konkret.php?gk=1234
    Location With Param Should Be        ${PAGE_GEOKRETY_DETAILS_URL}   gkid=1234

Wrong id numeric value
    Create Session     geokrety          ${GK_URL}
    GET On Session     geokrety          url=/konkret.php?id=asc        expected_status=400
