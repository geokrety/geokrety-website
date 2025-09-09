*** Settings ***
Resource        ../ressources/Authentication.robot
Resource        ../ressources/Moves.robot
Resource        ../ressources/Pictures.robot
Variables       ../ressources/vars/users.yml
Test Setup      Test Setup

*** Variables ***

*** Test Cases ***

Anonymous Don't Have Manage Buttons
    Sign Out Fast
    Go To GeoKrety ${1}
    Scroll Into View                                ${GEOKRET_DETAILS_MOVE_1}
    Page Should Not Contain Element                 ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}\[1]${PICTURE_ACTIONS}

Picture Owner Has Manage Buttons
    Sign In ${USER_1.name} Fast
    Go To GeoKrety ${1}
    Scroll Into View                                ${GEOKRET_DETAILS_MOVE_1}
    Page Should Contain Element                     ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}\[1]${PICTURE_ACTIONS}
    Page Should Contain Element                     ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}\[1]${PICTURE_ACTIONS_EDIT_BUTTON}
    Page Should Contain Element                     ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}\[1]${PICTURE_ACTIONS_DELETE_BUTTON}
    Page Should Not Contain Element                 ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}\[1]${PICTURE_ACTIONS_SET_AS_AVATAR_BUTTON}

GeoKret Owner Has Manage Buttons
    Sign In ${USER_2.name} Fast
    Go To GeoKrety ${1}
    Scroll Into View                                ${GEOKRET_DETAILS_MOVE_1}
    Page Should Contain Element                     ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}\[1]${PICTURE_ACTIONS}
    Page Should Not Contain Element                 ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}\[1]${PICTURE_ACTIONS_EDIT_BUTTON}
    Page Should Not Contain Element                 ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}\[1]${PICTURE_ACTIONS_DELETE_BUTTON}
    Page Should Contain Element                     ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}\[1]${PICTURE_ACTIONS_SET_AS_AVATAR_BUTTON}

Other Users Don't Have Manage Buttons
    Sign In ${USER_3.name} Fast
    Go To GeoKrety ${1}
    Scroll Into View                                ${GEOKRET_DETAILS_MOVE_1}
    Page Should Not Contain Element                 ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}\[1]${PICTURE_ACTIONS}


*** Keywords ***

Test Setup
    Clear Database And Seed ${3} users
    Seed ${1} geokrety owned by ${2}
    Post Move                               ${MOVE_1}
    Post Move                               ${MOVE_2}
    Sign In ${USER_1.name} Fast
    Go To GeoKrety ${1}
    Upload picture via button               ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_DROPZONE}    ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}    ${PICTURES_DIR}/sample-picture.png
