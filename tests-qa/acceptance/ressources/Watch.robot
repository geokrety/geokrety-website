*** Settings ***
Resource    ../ressources/ComponentsLocator.robot
Resource    ../ressources/vars/Urls.robot

*** Keywords ***

Watch GeoKret
    [Arguments]    ${GKID}
    Go To Url                                       ${PAGE_GEOKRETY_WATCH_URL}          gkid=${GKID}
    Page Should Not Contain                         ${UNAUTHORIZED}
    Wait Until Panel                                Add this GeoKret to your watch list?
    Click Button                                    ${MODAL_PANEL_SUBMIT_BUTTON}

Unwatch GeoKret
    [Arguments]    ${GKID}
    Go To Url                                      ${PAGE_GEOKRETY_UNWATCH_URL}          gkid=${GKID}
    Page Should Not Contain                         ${UNAUTHORIZED}
    Wait Until Panel                                Remove this GeoKret from your watch list?
    Click Button                                    ${MODAL_PANEL_SUBMIT_BUTTON}


Check GeoKrety Watched
    [Arguments]    ${row}    ${gk}    ${owner}    ${move}    ${last_mover}=${EMPTY}    ${distance}=${EMPTY}    ${caches}=${EMPTY}    ${button}=${EMPTY}
    # TODO check status icon
    # Page Should Contain Element             ${USER_INVENTORY_TABLE}//tr[${row}]/td[1]//img[@data-gk-move-type=${move.move_type}]
    Table Cell Should Contain               ${USER_WATCHED_TABLE}    ${row + 1}    2    ${gk.name}
    Table Cell Should Contain               ${USER_WATCHED_TABLE}    ${row + 1}    2    ${gk.ref}
    Table Cell Should Contain               ${USER_WATCHED_TABLE}    ${row + 1}    3    ${owner.name}
    Table Cell Should Contain               ${USER_WATCHED_TABLE}    ${row + 1}    4    ${move.waypoint}
    Table Cell Should Contain               ${USER_WATCHED_TABLE}    ${row + 1}    5    ${last_mover.name}
    # TODO check last move type icon
    Table Cell Should Contain               ${USER_WATCHED_TABLE}    ${row + 1}    6    ${distance} km
    Table Cell Should Contain               ${USER_WATCHED_TABLE}    ${row + 1}    7    ${caches}
    Table Cell Should Contain               ${USER_WATCHED_TABLE}    ${row + 1}    8    ${button}
