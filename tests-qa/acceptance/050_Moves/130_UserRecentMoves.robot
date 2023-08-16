*** Settings ***
Resource        ../ressources/Authentication.robot
Resource        ../ressources/Moves.robot
Resource        ../ressources/Users.robot
Suite Setup     Suite Setup

*** Test Cases ***

Moves Should Be Shown On User Recent Moves Page
    Go To Url                               ${PAGE_USER_RECENT_MOVES_URL}    userid=${USER_1.id}
    Wait Until Page Contains Element        ${USER_RECENT_MOVES_TABLE}/tbody/tr
    Element Count Should Be                 ${USER_RECENT_MOVES_TABLE}/tbody/tr        5
    Check Move                              ${USER_RECENT_MOVES_TABLE}    ${1}    ${MOVE_6}    distance=14
    Check Move                              ${USER_RECENT_MOVES_TABLE}    ${2}    ${MOVE_4}    distance=14
    Check Move                              ${USER_RECENT_MOVES_TABLE}    ${3}    ${MOVE_3}
    Check Move                              ${USER_RECENT_MOVES_TABLE}    ${4}    ${MOVE_2}
    Check Move                              ${USER_RECENT_MOVES_TABLE}    ${5}    ${MOVE_1}    distance=0

    Go To Url                               ${PAGE_USER_RECENT_MOVES_URL}    userid=${USER_2.id}
    Wait Until Page Contains Element        ${USER_RECENT_MOVES_TABLE}/tbody/tr
    Element Count Should Be                 ${USER_RECENT_MOVES_TABLE}/tbody/tr        1
    Check Move                              ${USER_RECENT_MOVES_TABLE}    ${1}    ${MOVE_25}   author=${USER_2.name}

Owner Recent Moves Page Should Be Empty
    Go To Url                               ${PAGE_USER_RECENT_MOVES_URL}    userid=${USER_3.id}
    Page Should Not Contain Element         ${USER_RECENT_MOVES_TABLE}
    Page Should Contain                     ${USER_3.name} didn't moved any GeoKrety yet.

*** Keywords ***

Suite Setup
    Clear Database And Seed ${3} users
    Seed ${1} geokrety owned by ${2}
    Sign Out Fast
    Post Move                               ${MOVE_1}
    Post Move                               ${MOVE_2}
    Post Move                               ${MOVE_3}
    Post Move                               ${MOVE_4}
    Post Move                               ${MOVE_25}
    Post Move                               ${MOVE_6}
