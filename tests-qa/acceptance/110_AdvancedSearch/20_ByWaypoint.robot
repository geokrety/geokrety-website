*** Settings ***
Resource        ../ressources/Authentication.robot
Resource        ../ressources/vars/Urls.robot
Resource        ../ressources/Moves.robot
Variables       ../ressources/vars/geokrety.yml
Variables       ../ressources/vars/waypoints.yml
Suite Setup     Suite Setup

*** Variables ***

${PERCENT} =          %25

*** Test Cases ***

Moves Should Be Shown
    Go To Url                               ${PAGE_SEARCH_BY_WAYPOINT_URL}              waypoint=${MOVE_1.waypoint}
    Element Count Should Be                 ${SEARCH_BY_WAYPOINT_TABLE}/tbody/tr        2
    Check Search By Waypoint                ${1}    ${GEOKRETY_1}    ${MOVE_1}          distance=0
    Check Search By Waypoint                ${2}    ${GEOKRETY_2}    ${MOVE_31}         distance=0
    Wait until page contains element        ${SEARCH_BY_WAYPOINT_TABLE}/tbody/tr[1][not(contains(@class, "success"))]    timeout=2
    Wait until page contains element        ${SEARCH_BY_WAYPOINT_TABLE}/tbody/tr[2][contains(@class, "success")]        timeout=2

Wildcard Search Is Disabled
    Go To Url                               ${PAGE_SEARCH_BY_WAYPOINT_URL}              waypoint=GC${PERCENT}
    Element Count Should Be                 ${SEARCH_BY_USER_TABLE}/tbody/tr        0
    Page Should Contain                     No GeoKrety has visited cache GC% yet.

Waypoint Without Moves
    Go To Url                               ${PAGE_SEARCH_BY_WAYPOINT_URL}              waypoint=${INVALID_GC_WPT}
    Element Count Should Be                 ${SEARCH_BY_WAYPOINT_TABLE}/tbody/tr        0
    Page Should Contain                     No GeoKrety has visited cache ${INVALID_GC_WPT} yet.

Empty Request
    Go To Url                               ${PAGE_SEARCH_BY_WAYPOINT_URL}              waypoint=${EMPTY}    redirect=${PAGE_ADVANCED_SEARCH_URL}

*** Keywords ***

Suite Setup
    Clear Database And Seed ${2} users
    Seed ${2} geokrety owned by ${2}
    Sign Out Fast
    Post Move                               ${MOVE_1}
    Post Move                               ${MOVE_2}
    Post Move                               ${MOVE_3}
    Post Move                               ${MOVE_4}
    Post Move                               ${MOVE_25}
    Post Move                               ${MOVE_6}
    Post Move                               ${MOVE_31}
