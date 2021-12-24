*** Settings ***
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/PageMoves.robot
Resource        ../vars/moves.resource
Resource        ../vars/users.resource
Resource        ../vars/geokrety.resource
Force Tags      GeoKrety Label
Test Setup     Seed

*** Test Cases ***

Forbidden for anonymous users
    Go To Url                               ${PAGE_GEOKRETY_DETAILS_URL}    gkid=${GEOKRETY_1.id}
    Page Should Not Contain Element         ${GEOKRET_DETAILS_PRINT_LABEL_LINK}
    Go To Url                               ${PAGE_GEOKRETY_LABEL_URL}      gkid=${GEOKRETY_1.id}
    Page Should Contain                     Unauthorized

Owner himself can access labels form
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_GEOKRETY_DETAILS_URL}    gkid=${GEOKRETY_1.id}
    Page Should Contain Element             ${GEOKRET_DETAILS_PRINT_LABEL_LINK}
    Go To Url                               ${PAGE_GEOKRETY_LABEL_URL}      gkid=${GEOKRETY_1.id}
    Page Should Contain                     Label generator

Other users cannot access labels form - Not touched
    Sign In ${USER_2.name} Fast
    Go To Url                               ${PAGE_GEOKRETY_DETAILS_URL}    gkid=${GEOKRETY_1.id}
    Page Should Not Contain Element         ${GEOKRET_DETAILS_PRINT_LABEL_LINK}
    Go To Url                               ${PAGE_GEOKRETY_LABEL_URL}      gkid=${GEOKRETY_1.id}
    Page Should Contain                     you don't have the permission to print a label for this GeoKret as you never discovered it!

Other users can access labels form
    [Template]    Other users can access labels form
    ${MOVE_21}
    ${MOVE_22}
    ${MOVE_24}
    #${MOVE_25}   # Only for owners
    ${MOVE_26}

Other users cannot access labels form
    [Template]    Other users cannot access labels form
    ${MOVE_23}    # you don't have the permission


*** Keywords ***

Seed
    Clear Database
    Seed 2 users
    Seed ${1} geokrety owned by ${USER_1.id}

Other users can access labels form
    [Arguments]    ${move}
    Seed
    Sign In ${USER_2.name} Fast
    Go To Url                               ${PAGE_GEOKRETY_DETAILS_URL}    gkid=${GEOKRETY_1.id}
    Page Should Not Contain Element         ${GEOKRET_DETAILS_PRINT_LABEL_LINK}
    Post Move                               ${move}
    Go To Url                               ${PAGE_GEOKRETY_DETAILS_URL}    gkid=${GEOKRETY_1.id}
    Page Should Contain Element             ${GEOKRET_DETAILS_PRINT_LABEL_LINK}
    Go To Page                              ${PAGE_GEOKRETY_LABEL_URL}      gkid=${GEOKRETY_1.id}
    Page Should Contain                     Label generator

Other users cannot access labels form
    [Arguments]    ${move}
    Seed
    Sign In ${USER_2.name} Fast
    Post Move                               ${move}
    Go To Url                               ${PAGE_GEOKRETY_DETAILS_URL}    gkid=${GEOKRETY_1.id}
    Page Should Not Contain Element         ${GEOKRET_DETAILS_PRINT_LABEL_LINK}
    Go To Page                              ${PAGE_GEOKRETY_LABEL_URL}      gkid=${GEOKRETY_1.id}
    Page Should Contain                     you don't have the permission to print a label for this GeoKret as you never discovered it!
