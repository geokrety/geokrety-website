*** Settings ***
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/Watch.robot
Resource        ../vars/users.resource
Resource        ../vars/geokrety.resource
Force Tags      Watch    Access
Test Setup      Seed


*** Test Cases ***

Create watch from link on GeoKret details - Modal
    Sign In ${USER_2.name} Fast
    Go To Url With Param                            ${PAGE_GEOKRETY_DETAILS_URL}            gkid=${GEOKRETY_1.id}
    Click Element                                   ${GEOKRET_DETAILS_WATCH_LINK}
    Wait Until Modal                                Add this GeoKret to your watch list?
    Click Button                                    ${MODAL_DIALOG_SUBMIT_BUTTON}

Create watch from link on GeoKret details - Full Page
    Sign In ${USER_2.name} Fast
    Watch GeoKret                                   ${GEOKRETY_1.id}

Unwatch from link on GeoKret details - Modal
    Sign In ${USER_2.name} Fast
    Watch GeoKret                                   ${GEOKRETY_1.id}

    Go To Url With Param                            ${PAGE_GEOKRETY_DETAILS_URL}            gkid=${GEOKRETY_1.id}
    Click Element                                   ${GEOKRET_DETAILS_UNWATCH_LINK}
    Wait Until Modal                                Remove this GeoKret from your watch list?
    Click Button                                    ${MODAL_DIALOG_SUBMIT_BUTTON}

Unwatch from link on GeoKret details - Full Page
    Sign In ${USER_2.name} Fast
    Watch GeoKret                                   ${GEOKRETY_1.id}
    Unwatch GeoKret                                 ${GEOKRETY_1.id}

Already watched
    Sign In ${USER_2.name} Fast
    Watch GeoKret                                   ${GEOKRETY_1.id}
    Watch GeoKret                                   ${GEOKRETY_1.id}
    Page Should Contain                             This GeoKret is already in your watch list

Unwatch not watched
    Sign In ${USER_2.name} Fast
    Unwatch GeoKret                                 ${GEOKRETY_1.id}
    Page Should Contain                             This GeoKret is not your watch list

*** Keywords ***

Seed
    Clear DB And Seed 2 users
    Seed 2 geokrety owned by ${USER_1.id}
