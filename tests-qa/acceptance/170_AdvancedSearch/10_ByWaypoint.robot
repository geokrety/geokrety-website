*** Settings ***
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/PageMoves.robot
Resource        ../vars/moves.resource
Resource        ../vars/users.resource
Resource        ../vars/geokrety.resource
Resource        ../vars/waypoints.resource
Force Tags      Moves    Owned
Suite Setup     Seed

*** Test Cases ***

Moves Should Be Shown
    Go To Url                               ${PAGE_SEARCH_BY_WAYPOINT_URL}              waypoint=${MOVE_1.waypoint}
    Element Count Should Be                 ${SEARCH_BY_WAYPOINT_TABLE}/tbody/tr        2
    Check Search By Waypoint                ${1}    ${GEOKRETY_1}    ${MOVE_1}          distance=0
    Check Search By Waypoint                ${2}    ${GEOKRETY_2}    ${MOVE_31}         distance=0
    Wait until page contains element        ${SEARCH_BY_WAYPOINT_TABLE}/tbody/tr[1][not(contains(@class, "success"))]    timeout=2
    Wait until page contains element        ${SEARCH_BY_WAYPOINT_TABLE}/tbody/tr[2][contains(@class, "success")]        timeout=2

Waypoint Without Moves
    Go To Url                               ${PAGE_SEARCH_BY_WAYPOINT_URL}              waypoint=${INVALID_GC_WPT}
    Element Count Should Be                 ${SEARCH_BY_WAYPOINT_TABLE}/tbody/tr        0
    Page Should Contain                     No GeoKrety has visited cache ${INVALID_GC_WPT} yet.

*** Keywords ***

Seed
    Clear DB And Seed 2 users
    Seed 2 geokrety owned by ${USER_2.id}
    Sign Out Fast
    Post Move                               ${MOVE_1}
    Post Move                               ${MOVE_2}
    Post Move                               ${MOVE_3}
    Post Move                               ${MOVE_4}
    Post Move                               ${MOVE_25}
    Post Move                               ${MOVE_6}
    Post Move                               ${MOVE_31}
