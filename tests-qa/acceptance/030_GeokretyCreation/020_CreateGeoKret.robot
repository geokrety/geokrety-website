*** Settings ***
Library         RequestsLibrary
Resource        ../ressources/Authentication.robot
Resource        ../ressources/Geokrety.robot
Variables       ../ressources/vars/users.yml
Variables       ../ressources/vars/geokrety.yml
Test Setup      Test Setup

*** Test Cases ***

Create A GeoKret
    Go To Url                           ${PAGE_GEOKRETY_CREATE_URL}
    Create GeoKret                      &{GEOKRETY_1}
    Element Should Contain              ${GEOKRET_DETAILS_NAME}         ${GEOKRETY_1.name}

Check csrf
    ${params.newsid}    Set Variable        ${1}
    Create Session                          gk      ${GK_URL}
    ${auth} =           GET On Session      gk      /devel/users/${USER_1.name}/login
    ${resp} =           POST On Session     gk      url=${PAGE_GEOKRETY_CREATE_URL}?skip_csrf=False
    ...                                             data=${GEOKRETY_2}
    ...                                             expected_status=200
    ${body} =           Convert To String   ${resp.content}
    Should Contain                          ${body}    CSRF error, please try again.
    Delete All Sessions

*** Keywords ***

Test Setup
    Clear Database And Seed ${1} users
    Sign In ${USER_1.name} Fast
