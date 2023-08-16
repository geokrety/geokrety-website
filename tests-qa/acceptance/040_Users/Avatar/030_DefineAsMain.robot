*** Settings ***
Library         RequestsLibrary
Library         RobotEyes
Resource        ../../ressources/Authentication.robot
Resource        ../../ressources/Pictures.robot
Variables       ../../ressources/vars/users.yml
Test Setup      Test Setup

*** Test Cases ***

Set image as main avatar
    Open Eyes                               SeleniumLibrary  5

    Post User Avatar                        ${CURDIR}/../../ressources/pictures/sample-picture.png
    Click Picture Action                    ${USER_PROFILE_FIRST_IMAGE}         ${PICTURE_PULLER_SET_AS_AVATAR_BUTTON}

    Wait Until Modal                        Do you want to set this picture as main avatar?
    Capture Element                         ${MODAL_DIALOG}    name=img1
    Click Button                            ${MODAL_DIALOG_SUBMIT_BUTTON}
    Scroll Into View                        ${USER_PROFILE_AVATAR_GALLERY}
    Capture Element                         ${USER_PROFILE_AVATAR_GALLERY}    name=img2

    Compare Images

Set second image as main avatar
    Post User Avatar                        ${CURDIR}/../../ressources/pictures/sample-picture.png     position=1
    Post User Avatar                        ${CURDIR}/../../ressources/pictures/sample-picture2.png    position=2

    Click Picture Action                    ${USER_PROFILE_SECOND_IMAGE}        ${PICTURE_PULLER_SET_AS_AVATAR_BUTTON}
    Wait Until Modal                        Do you want to set this picture as main avatar?
    Click Button                            ${MODAL_DIALOG_SUBMIT_BUTTON}
    Check Image                             ${USER_PROFILE_PICTURES_PANEL}

*** Keywords ***

Test Setup
    Clear Database And Seed ${1} users
    Sign In ${USER_1.name} Fast
    Go To User ${USER_1.id}
