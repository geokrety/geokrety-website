*** Settings ***
Library         RequestsLibrary
Library         RobotEyes
Resource        ../../ressources/Authentication.robot
Resource        ../../ressources/Geokrety.robot
Resource        ../../ressources/Pictures.robot
Variables       ../../ressources/vars/users.yml
Variables       ../../ressources/vars/geokrety.yml
Test Setup      Test Setup

*** Test Cases ***

First image is set as main geokret avatar
    [Tags]    OpenEyes
    Open Eyes                               Browser  5

    Post GeoKret Avatar                     ${CURDIR}/../../ressources/pictures/sample-picture.png

    Wait Until Page Contains Element        ${GEOKRET_DETAILS_AVATAR_FIRST_IMAGE}${PICTURE_ACTIONS}
    Scroll Into View                        ${GEOKRET_DETAILS_AVATAR_FIRST_IMAGE}${PICTURE_ACTIONS}
    Mouse Over                              ${GEOKRET_DETAILS_AVATAR_FIRST_IMAGE}${PICTURE_ACTIONS}

    Page Should Not Contain Button          ${PICTURE_ACTIONS_SET_AS_AVATAR_BUTTON}
    Capture Element                         ${GEOKRET_DETAILS_AVATAR_IMAGES}    name=img2

    Compare Images

Set second image as main geokret avatar
    Post GeoKret Avatar                     ${CURDIR}/../../ressources/pictures/sample-picture.png     position=1
    Post GeoKret Avatar                     ${CURDIR}/../../ressources/pictures/sample-picture2.png    position=2

    Click Picture Action                    ${GEOKRET_DETAILS_AVATAR_SECOND_IMAGE}    ${PICTURE_ACTIONS_SET_AS_AVATAR_BUTTON}
    Wait Until Modal                        Do you want to set this picture as main avatar?
    Click Button                            ${MODAL_DIALOG_SUBMIT_BUTTON}
    Check Image                             ${GEOKRET_DETAILS_AVATAR_IMAGES}

*** Keywords ***

Test Setup
    Clear Database And Seed ${1} users
    Seed ${1} geokrety owned by ${1}
    Sign In ${USER_1.name} Fast
    Go To GeoKrety ${GEOKRETY_1.id}
