*** Settings ***
Resource        ../ressources/Authentication.robot
Resource        ../ressources/Moves.robot
Resource        ../ressources/Users.robot
Suite Setup     Suite Setup

*** Test Cases ***

GeoKrety Should Be Shown On User Owned GeoKrety Page
    Go To Url                               ${PAGE_USER_OWNED_GEOKRETY_URL}             userid=${USER_2.id}
    Wait Until Page Contains Element        ${USER_OWNED_GEOKRETY_TABLE}/tbody/tr
    Element Count Should Be                 ${USER_OWNED_GEOKRETY_TABLE}/tbody/tr       1
    Check GeoKrety Owned                    ${1}    ${GEOKRETY_1}    ${MOVE_6}          last_mover=${USER_1}     distance=28    caches=3

Owned GeoKrety Page Should Be Empty
    Go To Url                               ${PAGE_USER_OWNED_GEOKRETY_URL}             userid=${USER_1.id}
    Page Should Not Contain Element         ${USER_OWNED_GEOKRETY_TABLE}
    Page Should Contain                     ${USER_1.name} doesn't own any GeoKrety yet.

Owned GeoKrety Page Should Be Empty - Authenticated
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_USER_OWNED_GEOKRETY_URL}             userid=${USER_1.id}
    Page Should Not Contain Element         ${USER_OWNED_GEOKRETY_TABLE}
    Page Should Contain                     You did not created any GeoKrety yet.

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
