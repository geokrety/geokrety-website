*** Settings ***
Library         RequestsLibrary
Resource        ../ressources/vars/Urls.robot
Suite Setup     Suite Setup

*** Test Cases ***

At Least One Parameter Is Required
    Create Session     geokrety          ${GK_URL}
    GET On Session     geokrety          /konkret.php         expected_status=400

Should Redirect To New Url - by id
    Go To Url                            url=${GK_URL}/konkret.php?id=1    redirect=${PAGE_GEOKRETY_1_DETAILS_URL}

Should Redirect To New Url - by gkid - lowercase
    Go To Url                            url=${GK_URL}/konkret.php?gk=gk0001    redirect=${PAGE_GEOKRETY_1_DETAILS_URL}

Should Redirect To New Url - by gkid - integer
    Go To Url                            url=${GK_URL}/konkret.php?gk=1    redirect=${PAGE_GEOKRETY_1_DETAILS_URL}

Wrong id numeric value
    Create Session                         geokrety          ${GK_URL}
    ${resp} =    GET On Session            geokrety          url=/konkret.php?id=asc          expected_status=400
    ${body} =    Convert To String         ${resp.content}
    Variable Without Warning Or Failure    ${body}
    Should Contain                         ${body}    &quot;id&quot; must be numeric

Wrong gkid numeric value
    Create Session                         geokrety          ${GK_URL}
    ${resp} =    GET On Session            geokrety          url=/konkret.php?gkid\=GKABCD    expected_status=400
    ${body} =    Convert To String         ${resp.content}
    Variable Without Warning Or Failure    ${body}
    Should Contain                         ${body}    &quot;id&quot; or &quot;gk&quot; parameter must be provided

*** Keywords ***

Suite Setup
    Clear Database And Seed ${2} users
    Seed ${1} geokrety owned by ${1}
