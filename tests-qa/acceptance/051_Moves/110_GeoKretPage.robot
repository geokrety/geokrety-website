*** Settings ***
Resource        ../ressources/Authentication.robot
Resource        ../ressources/Moves.robot
Resource        ../ressources/Pictures.robot
Resource        ../ressources/vars/pages/Home.robot
Variables       ../ressources/vars/users.yml
Test Setup      Test Setup

*** Test Cases ***

Moves Should Be Shown On GeoKret Page
    Go To GeoKrety ${1}
    Element Count Should Be                 ${GEOKRET_DETAILS_MOVES}        6
    Check GeoKret Move                      ${GEOKRET_DETAILS_MOVES}    ${1}    ${MOVE_6}    distance=14
    Check GeoKret Move                      ${GEOKRET_DETAILS_MOVES}    ${2}    ${MOVE_25}   author=${USER_2.name}
    Check GeoKret Move                      ${GEOKRET_DETAILS_MOVES}    ${3}    ${MOVE_4}    distance=14
    Check GeoKret Move                      ${GEOKRET_DETAILS_MOVES}    ${4}    ${MOVE_3}
    Check GeoKret Move                      ${GEOKRET_DETAILS_MOVES}    ${5}    ${MOVE_2}
    Check GeoKret Move                      ${GEOKRET_DETAILS_MOVES}    ${6}    ${MOVE_1}    distance=0

    Wait Until Page Contains Element        //*[@id="mapid" and @data-map-loaded="true"]    timeout=30
    Check Image                             ${GEOKRET_DETAILS_MAP}

*** Keywords ***

Test Setup
    Clear Database And Seed ${2} users
    Seed ${1} geokrety owned by ${2}
    Sign Out Fast
    Post Move                               ${MOVE_1}
    Post Move                               ${MOVE_2}
    Post Move                               ${MOVE_3}
    Post Move                               ${MOVE_4}
    Post Move                               ${MOVE_25}
    Post Move                               ${MOVE_6}
