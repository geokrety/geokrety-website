*** Settings ***
Library         RobotEyes
Resource        ../ressources/Authentication.robot
Resource        ../ressources/Moves.robot
Resource        ../ressources/Users.robot
Suite Setup     Suite Setup

*** Test Cases ***

GeoKrety Should Be Shown On User Owned GeoKrety Page
    Go To Url                               ${PAGE_USER_OWNED_GEOKRETY_RECENT_MOVES_URL}    userid=${USER_2.id}
    Wait Until Page Contains Element        ${USER_OWNED_GEOKRETY_RECENT_MOVES_TABLE}/tbody/tr
    Element Count Should Be                 ${USER_OWNED_GEOKRETY_RECENT_MOVES_TABLE}/tbody/tr        6
    Check Move                              ${USER_OWNED_GEOKRETY_RECENT_MOVES_TABLE}    ${1}    ${MOVE_6}    distance=14
    Check Move                              ${USER_OWNED_GEOKRETY_RECENT_MOVES_TABLE}    ${2}    ${MOVE_25}   author=${USER_2.name}
    Check Move                              ${USER_OWNED_GEOKRETY_RECENT_MOVES_TABLE}    ${3}    ${MOVE_4}    distance=14
    Check Move                              ${USER_OWNED_GEOKRETY_RECENT_MOVES_TABLE}    ${4}    ${MOVE_3}
    Check Move                              ${USER_OWNED_GEOKRETY_RECENT_MOVES_TABLE}    ${5}    ${MOVE_2}
    Check Move                              ${USER_OWNED_GEOKRETY_RECENT_MOVES_TABLE}    ${6}    ${MOVE_1}    distance=0

Owner Inventory Page Should Be Empty
    Go To Url                               ${PAGE_USER_OWNED_GEOKRETY_RECENT_MOVES_URL}    userid=${USER_1.id}
    Page Should Not Contain Element         ${USER_OWNED_GEOKRETY_RECENT_MOVES_TABLE}
    Page Should Contain                     ${USER_1.name}'s GeoKrety didn't moved yet.

*** Keywords ***

Suite Setup
    Clear Database And Seed ${2} users
    Seed ${1} geokrety owned by ${2}
    Sign Out Fast
    Post Move                               ${MOVE_1}
    Post Move                               ${MOVE_2}
    Post Move                               ${MOVE_3}
    Post Move                               ${MOVE_4}
    Post Move                               ${MOVE_25}
    Post Move                               ${MOVE_6}
