*** Settings ***
Library         RequestsLibrary
Library         RobotEyes
Resource        ../../ressources/Authentication.robot
Resource        ../../ressources/Pictures.robot
Variables       ../../ressources/vars/users.yml
Test Setup      Test Setup

*** Test Cases ***

First uploaded image is set as main avatar
    [Tags]    OpenEyes
    Open Eyes                               Browser  5

    # Post User Avatar                        ${CURDIR}/../../ressources/pictures/sample-picture.png
    Post User Avatar                        ${CURDIR}/../../ressources/pictures/sample-picture.png     position=1
    Post User Avatar                        ${CURDIR}/../../ressources/pictures/sample-picture2.png    position=2

    Wait Until Page Contains Element        ${USER_PROFILE_FIRST_IMAGE}${PICTURE_ACTIONS}
    Scroll Into View                        ${USER_PROFILE_FIRST_IMAGE}${PICTURE_ACTIONS}
    Mouse Over                              ${USER_PROFILE_FIRST_IMAGE}${PICTURE_ACTIONS}
    Page Should Not Contain Button          ${USER_PROFILE_FIRST_IMAGE}${PICTURE_ACTIONS}${PICTURE_ACTIONS_SET_AS_AVATAR_BUTTON}

    Check Image                             ${USER_PROFILE_PICTURES_PANEL}//div[@class="gallery"]

Set second image as main avatar
    Post User Avatar                        ${CURDIR}/../../ressources/pictures/sample-picture.png     position=1
    Post User Avatar                        ${CURDIR}/../../ressources/pictures/sample-picture2.png    position=2

    Click Picture Action                    ${USER_PROFILE_SECOND_IMAGE}        ${PICTURE_ACTIONS_SET_AS_AVATAR_BUTTON}
    Wait Until Modal                        Do you want to set this picture as main avatar?
    Click Button                            ${MODAL_DIALOG_SUBMIT_BUTTON}
    Check Image                             ${USER_PROFILE_PICTURES_PANEL}//div[@class="gallery"]

*** Keywords ***

Test Setup
    Clear Database And Seed ${1} users
    Sign In ${USER_1.name} Fast
    Go To User ${USER_1.id}
