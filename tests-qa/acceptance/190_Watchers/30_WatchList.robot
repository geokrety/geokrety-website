*** Settings ***
Resource        ../ressources/Authentication.robot
Resource        ../ressources/Moves.robot
Resource        ../ressources/Watch.robot
Variables       ../ressources/vars/users.yml
Variables       ../ressources/vars/geokrety.yml
Test Setup      Test Setup

*** Test Cases ***

Watch appear in personal watch list
    Sign In ${USER_2.name} Fast
    Watch GeoKret                                   ${GEOKRETY_1.id}

    Go To Url                                       ${PAGE_USER_WATCHED_GEOKRETY_URL}       userid=${USER_2.id}
    Wait Until Page Contains Element                ${USER_WATCHED_TABLE}/tbody/tr
    Element Count Should Be                         ${USER_WATCHED_TABLE}/tbody/tr          1
    Check GeoKrety Watched                          ${1}    ${GEOKRETY_1}    ${USER_1}      ${MOVE_EMPTY}    last_mover=${USER_EMPTY}     distance=0    caches=0    button=❌

    Watch GeoKret                                   ${GEOKRETY_2.id}

    Go To Url                                       ${PAGE_USER_WATCHED_GEOKRETY_URL}       userid=${USER_2.id}
    Wait Until Page Contains Element                ${USER_WATCHED_TABLE}/tbody/tr
    Element Count Should Be                         ${USER_WATCHED_TABLE}/tbody/tr          2
    Check GeoKrety Watched                          ${1}    ${GEOKRETY_1}    ${USER_1}      ${MOVE_EMPTY}    last_mover=${USER_EMPTY}     distance=0    caches=0    button=❌
    Check GeoKrety Watched                          ${2}    ${GEOKRETY_2}    ${USER_1}      ${MOVE_EMPTY}    last_mover=${USER_EMPTY}     distance=0    caches=0    button=❌

Other users can see others watch list
    Sign In ${USER_2.name} Fast
    Watch GeoKret                                   ${GEOKRETY_1.id}

    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_USER_WATCHED_GEOKRETY_URL}       userid=${USER_2.id}
    Wait Until Page Contains Element                ${USER_WATCHED_TABLE}/tbody/tr
    Element Count Should Be                         ${USER_WATCHED_TABLE}/tbody/tr          1
    Check GeoKrety Watched                          ${1}    ${GEOKRETY_1}    ${USER_1}      ${MOVE_EMPTY}    last_mover=${USER_EMPTY}     distance=0    caches=0    button=${EMPTY}

Move Button Displayed For Current GeoKrety Holder
    Sign In ${USER_2.name} Fast
    Watch GeoKret                                   ${GEOKRETY_1.id}

    Go To Url                                       ${PAGE_USER_WATCHED_GEOKRETY_URL}       userid=${USER_2.id}
    Wait Until Page Contains Element                ${USER_WATCHED_TABLE}/tbody/tr
    Element Count Should Be                         ${USER_WATCHED_TABLE}/tbody/tr          1
    Check GeoKrety Watched                          ${1}    ${GEOKRETY_1}    ${USER_1}      ${MOVE_EMPTY}    last_mover=${USER_EMPTY}     distance=0    caches=0    button=❌
    Run Keyword And Expect Error    * should have contained text *         Table Cell Should Contain       ${USER_WATCHED_TABLE}    ${1 + 1}    8    🛩️

    Post Move                                       ${MOVE_22}
    Go To Url                                       ${PAGE_USER_WATCHED_GEOKRETY_URL}       userid=${USER_2.id}
    Wait Until Page Contains Element                ${USER_WATCHED_TABLE}/tbody/tr
    Table Cell Should Contain                       ${USER_WATCHED_TABLE}    ${1 + 1}    8    🛩️

    Post Move                                       ${MOVE_21}
    Go To Url                                       ${PAGE_USER_WATCHED_GEOKRETY_URL}       userid=${USER_2.id}
    Wait Until Page Contains Element                ${USER_WATCHED_TABLE}/tbody/tr
    Run Keyword And Expect Error    * should have contained text *         Table Cell Should Contain       ${USER_WATCHED_TABLE}    ${1 + 1}    8    🛩️

Move Button Is Working
    Sign In ${USER_2.name} Fast
    Watch GeoKret                                   ${GEOKRETY_1.id}
    Post Move                                       ${MOVE_22}
    Go To Url                                       ${PAGE_USER_WATCHED_GEOKRETY_URL}       userid=${USER_2.id}
    Wait Until Page Contains Element                ${USER_WATCHED_TABLE}/tbody/tr
    Click Element                                   ${USER_WATCHED_ROW_1_MOVE_LINK}
    Location Should Be                              ${PAGE_MOVES_URL}?tracking_code=${GEOKRETY_1.tc}
    Panel validation has success                    ${MOVE_TRACKING_CODE_PANEL}
    Page Should Contain                             ${GEOKRETY_1.name}

Unwatch Button Is Working
    Sign In ${USER_2.name} Fast
    Watch GeoKret                                   ${GEOKRETY_1.id}

    Go To Url                                       ${PAGE_USER_WATCHED_GEOKRETY_URL}       userid=${USER_2.id}
    Wait Until Page Contains Element                ${USER_WATCHED_TABLE}/tbody/tr
    Element Count Should Be                         ${USER_WATCHED_TABLE}/tbody/tr          1
    Click Element                                   ${USER_WATCHED_ROW_1_UNWATCH_LINK}
    Wait Until Modal                                Remove this GeoKret from your watch list?
    Click Button                                    ${MODAL_DIALOG_SUBMIT_BUTTON}

    Go To Url                                       ${PAGE_USER_WATCHED_GEOKRETY_URL}       userid=${USER_2.id}
    Wait Until Page Contains                        You did not watch any GeoKrety yet

*** Keywords ***

Test Setup
    Clear Database And Seed ${2} users
    Seed ${2} geokrety owned by ${1}
