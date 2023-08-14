*** Settings ***
Library         RequestsLibrary
Resource        ../ressources/Authentication.robot
Resource        ../ressources/Geokrety.robot
Variables       ../ressources/vars/users.yml
Variables       ../ressources/vars/geokrety.yml
Test Setup      Test Setup

*** Test Cases ***

Type valid
    [Template]          GeoKret is created
    0                   Traditional
    1                   A book/CD/DVD…
    2                   A human
    3                   A coin
    4                   KretyPost



*** Keywords ***

Test Setup
    Clear Database And Seed ${1} users
    Sign In ${USER_1.name} Fast

GeoKret is created
    [Arguments]    ${type}      ${type_name}
    Go To Url                           ${PAGE_GEOKRETY_CREATE_URL}
    &{gk} =    Create Dictionary        name=geokret    type=${type}    mission=${EMPTY}
    Fill Creation Form                  &{gk}
    Click Button                        ${GEOKRET_CREATE_CREATE_BUTTON}
    Flash message shown                 Your GeoKret has been created.
    Element Should Contain              ${GEOKRET_DETAILS_TYPE}                     (${type_name})
    ${_type} =     Get Element Attribute    ${GEOKRET_DETAILS_TYPE_IMG}    attribute=data-gk-type
    Should Be Equal As Strings          ${_type}    ${type}
