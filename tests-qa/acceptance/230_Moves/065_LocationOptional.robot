*** Settings ***
Library         RequestsLibrary
Resource        ../ressources/Authentication.robot
Resource        ../ressources/Moves.robot
Variables       ../ressources/vars/users.yml
Variables       ../ressources/vars/geokrety.yml
Variables       ../ressources/vars/waypoints.yml
Suite Setup     Suite Setup

*** Test Cases ***

No Selection Should Show Error
    Go To Url                               ${PAGE_MOVES_URL}
    Open Panel                              ${MOVE_NEW_LOCATION_PANEL}
    Click Button                            ${MOVE_NEW_LOCATION_NEXT_BUTTON}
    Panel validation has error              ${MOVE_NEW_LOCATION_PANEL}

Dropped require location
    Go To Url                               ${PAGE_MOVES_URL}
    Open Panel                              ${MOVE_LOG_TYPE_PANEL}
    Click Move Type                         ${MOVE_LOG_TYPE_DROPPED_RADIO}
    Panel Is Open                           ${MOVE_NEW_LOCATION_PANEL}
    Click Button                            ${MOVE_NEW_LOCATION_NEXT_BUTTON}
    Panel validation has error              ${MOVE_NEW_LOCATION_PANEL}

Grabbed don't require location
    Go To Url                               ${PAGE_MOVES_URL}
    Open Panel                              ${MOVE_LOG_TYPE_PANEL}
    Click Move Type                         ${MOVE_LOG_TYPE_GRABBED_RADIO}
    Panel Is Open                           ${MOVE_ADDITIONAL_DATA_PANEL}

Meet don't require location (empty fields)
    Go To Url                               ${PAGE_MOVES_URL}
    Open Panel                              ${MOVE_LOG_TYPE_PANEL}
    Click Move Type                         ${MOVE_LOG_TYPE_MEET_RADIO}
    Panel Is Open                           ${MOVE_NEW_LOCATION_PANEL}
    Click Button                            ${MOVE_NEW_LOCATION_NEXT_BUTTON}
    Panel validation has success            ${MOVE_NEW_LOCATION_PANEL}

Meet require location (if not empty fields)
    Go To Url                               ${PAGE_MOVES_URL}
    Open Panel                              ${MOVE_LOG_TYPE_PANEL}
    Click Move Type                         ${MOVE_LOG_TYPE_MEET_RADIO}
    Panel Is Open                           ${MOVE_NEW_LOCATION_PANEL}
    Input Text                              ${MOVE_NEW_LOCATION_WAYPOINT_INPUT}         OC1234
    Click Button                            ${MOVE_NEW_LOCATION_NEXT_BUTTON}
    Panel validation has error              ${MOVE_NEW_LOCATION_PANEL}

Dipped require location
    Go To Url                               ${PAGE_MOVES_URL}
    Open Panel                              ${MOVE_LOG_TYPE_PANEL}
    Click Move Type                         ${MOVE_LOG_TYPE_DIPPED_RADIO}
    Panel Is Open                           ${MOVE_NEW_LOCATION_PANEL}
    Click Button                            ${MOVE_NEW_LOCATION_NEXT_BUTTON}
    Panel validation has error              ${MOVE_NEW_LOCATION_PANEL}

Comment don't require location
    Go To Url                               ${PAGE_MOVES_URL}
    Open Panel                              ${MOVE_LOG_TYPE_PANEL}
    Click Move Type                         ${MOVE_LOG_TYPE_COMMENT_RADIO}
    Panel Is Open                           ${MOVE_ADDITIONAL_DATA_PANEL}

*** Keywords ***

Suite Setup
    Clear Database And Seed ${1} users
    Seed ${1} geokrety owned by ${1}
    Seed ${3} waypoints OC
    Seed ${1} waypoints GC
    Sign Out Fast
