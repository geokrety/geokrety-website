*** Settings ***
Resource        ../ressources/Authentication.robot
Resource        ../ressources/Moves.robot
Resource        ../ressources/Users.robot
Suite Setup     Suite Setup

*** Test Cases ***

Anonymous Cannot Delete Moves
    Sign Out Fast
    Go To Url                                       ${PAGE_MOVES_DELETE_URL}    moveid=1    redirect=${PAGE_SIGN_IN_URL}
    Flash message shown                             ${UNAUTHORIZED}

Author Can Delete It's Moves
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_MOVES_DELETE_URL}    moveid=1
    Page Should Not Contain                         ${FORBIDEN}

Other Users Cannot Delete Others Moves
    Sign In ${USER_2.name} Fast
    Go To Url                                       ${PAGE_MOVES_DELETE_URL}    moveid=1    redirect=${PAGE_HOME_URL_EN}
    Flash message shown                             You are not allowed to edit this move

Delete Move
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_GEOKRETY_1_DETAILS_URL}
    Scroll Into View                                ${GEOKRET_DETAILS_MOVE_1}
    Click Button                                    ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_DETAILS_MOVES_DELETE_BUTTONS}
    Wait Until Modal                                Do you really want to delete this move?
    Check GeoKret Move                              ${MODAL_PANEL}    ${1}    ${MOVE_1}
    Click Button                                    ${MODAL_DIALOG_SUBMIT_BUTTON}
    Page Should Not Contain Element                 ${GEOKRET_DETAILS_MOVE_1}
    Location Should Contain                         ${PAGE_GEOKRETY_1_DETAILS_URL}

*** Keywords ***

Suite Setup
    Clear Database And Seed ${2} users
    Seed ${1} geokrety owned by ${2}
    Sign Out Fast
    Post Move                               ${MOVE_1}
