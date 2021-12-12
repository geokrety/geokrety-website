*** Settings ***
Library         SeleniumLibrary  timeout=10  implicit_wait=0
Library         RequestsLibrary
Resource        ../functions/PageGeoKretyCreate.robot
Resource        ../vars/users.resource
Resource        ../vars/geokrety.resource
Force Tags      Create GeoKrety
Suite Setup     Seed

*** Test Cases ***

Create A GeoKret
    Go To Url                           ${PAGE_GEOKRETY_CREATE_URL}
    Create GeoKret                      ${GEOKRETY_1}
    Element Should Contain              ${GEOKRET_DETAILS_NAME}         ${GEOKRETY_1.name}

Check csrf
    ${params.newsid}    Set Variable        ${1}
    Create Session                          gk      ${GK_URL}
    ${auth} =           GET On Session      gk      /devel/
    ${auth} =           GET On Session      gk      /devel/users/${USER_1.name}/login
    ${resp} =           POST On Session     gk      url=${PAGE_GEOKRETY_CREATE_URL}?skip_csrf=False    data=${GEOKRETY_2}    expected_status=200
    ${body} =           Convert To String   ${resp.content}
    Should Contain                          ${body}    CSRF error, please try again.
    Delete All Sessions

*** Keywords ***

Seed
    Clear Database
    Seed 1 users
    Sign In ${USER_1.name} Fast
