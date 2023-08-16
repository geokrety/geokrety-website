*** Settings ***
Resource        ../ressources/Authentication.robot
Resource        ../ressources/Moves.robot
Resource        ../ressources/Watch.robot
Variables       ../ressources/vars/users.yml
Variables       ../ressources/vars/geokrety.yml
Test Setup      Test Setup

*** Test Cases ***

Forbidden for anonymous users
    Go To Url                               ${PAGE_GEOKRETY_DETAILS_URL}    gkid=${GEOKRETY_1.id}
    Page Should Not Contain Element         ${GEOKRET_DETAILS_PRINT_LABEL_LINK}
    Go To Url                               ${PAGE_GEOKRETY_LABEL_URL}      gkid=${GEOKRETY_1.id}    redirect=${PAGE_SIGN_IN_URL}
    Flash message shown                     ${UNAUTHORIZED}

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
    Go To Url                               ${PAGE_GEOKRETY_LABEL_URL}      gkid=${GEOKRETY_1.id}    redirect=${PAGE_GEOKRETY_1_DETAILS_URL}
    Flash message shown                     you don't have the permission to print a label for this GeoKret as you never discovered it!

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

Test Setup
    Clear Database And Seed ${2} users
    Seed ${1} geokrety owned by ${1}

Other users can access labels form
    [Arguments]    ${move}
    Test Setup
    Sign In ${USER_2.name} Fast
    Go To Url                               ${PAGE_GEOKRETY_DETAILS_URL}    gkid=${GEOKRETY_1.id}
    Page Should Not Contain Element         ${GEOKRET_DETAILS_PRINT_LABEL_LINK}
    Post Move                               ${move}
    Go To Url                               ${PAGE_GEOKRETY_DETAILS_URL}    gkid=${GEOKRETY_1.id}
    Page Should Contain Element             ${GEOKRET_DETAILS_PRINT_LABEL_LINK}
    Go To Url                               ${PAGE_GEOKRETY_LABEL_URL}      gkid=${GEOKRETY_1.id}
    Page Should Contain                     Label generator

Other users cannot access labels form
    [Arguments]    ${move}
    Test Setup
    Sign In ${USER_2.name} Fast
    Post Move                               ${move}
    Go To Url                               ${PAGE_GEOKRETY_DETAILS_URL}    gkid=${GEOKRETY_1.id}
    Page Should Not Contain Element         ${GEOKRET_DETAILS_PRINT_LABEL_LINK}
    Go To Url                               ${PAGE_GEOKRETY_LABEL_URL}      gkid=${GEOKRETY_1.id}    redirect=${PAGE_GEOKRETY_1_DETAILS_URL}
    Flash message shown                     you don't have the permission to print a label for this GeoKret as you never discovered it!
