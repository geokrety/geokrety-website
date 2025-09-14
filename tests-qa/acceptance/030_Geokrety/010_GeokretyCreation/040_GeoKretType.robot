*** Settings ***
Library         RequestsLibrary
Resource        ../../ressources/Authentication.robot
Resource        ../../ressources/Geokrety.robot
Variables       ../../ressources/vars/users.yml
Variables       ../../ressources/vars/geokrety.yml
Test Setup      Test Setup

*** Test Cases ***

Type valid
    [Template]          GeoKret is created
    0                   Traditional
    1                   A book
    2                   A human
    3                   A coin
    4                   KretyPost
    5                   A Pebble
    6                   A car
    7                   A Playing Card
    8                   A dog
    9                   Jigsaw part
    10                  Easter Egg

Admin see the Easter Egg type
    Sign In ${USER_1.name} Fast
    Go To Url                           ${PAGE_GEOKRETY_CREATE_URL}
    Page Should Contain Element         ${GEOKRET_CREATE_TYPE_SELECT}/option[@value='10']

Users won't see the Easter Egg type
    Sign In ${USER_2.name} Fast
    Go To Url                           ${PAGE_GEOKRETY_CREATE_URL}
    Page Should Not Contain Element     ${GEOKRET_CREATE_TYPE_SELECT}/option[@value='10']


*** Keywords ***

Test Setup
    Clear Database And Seed ${2} users

GeoKret is created
    [Arguments]    ${type}      ${type_name}
    Sign In ${USER_1.name} Fast
    Go To Url                           ${PAGE_GEOKRETY_CREATE_URL}
    &{gk} =    Create Dictionary        name=geokret    type=${type}    mission=${EMPTY}
    Fill Creation Form                  &{gk}
    Click Button                        ${GEOKRET_CREATE_CREATE_BUTTON}
    Flash message shown                 Your GeoKret has been created.
    Element Should Contain              ${GEOKRET_DETAILS_TYPE}                     (${type_name})
    ${_type} =     Get Element Attribute    ${GEOKRET_DETAILS_TYPE_IMG}    attribute=data-gk-type
    Should Be Equal As Strings          ${_type}    ${type}
