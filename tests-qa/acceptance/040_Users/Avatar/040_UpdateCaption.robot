*** Settings ***
Library         RequestsLibrary
Library         RobotEyes
Resource        ../../ressources/Authentication.robot
Resource        ../../ressources/Pictures.robot
Variables       ../../ressources/vars/users.yml
Test Setup      Test Setup

*** Test Cases ***

Define User Avatar Caption
    Click Picture Action                    ${USER_PROFILE_FIRST_IMAGE}             ${PICTURE_PULLER_EDIT_BUTTON}
    Wait Until Modal                        Manage picture
    Input Text                              ${CAPTION_INPUT}    Bonjour
    Click Button                            ${MODAL_DIALOG_SUBMIT_BUTTON}

    Open Eyes                               SeleniumLibrary  6
    Page Should Contain                     Picture caption saved.
    Scroll Into View                        ${USER_PROFILE_FIRST_IMAGE}
    Wait Until Element Is Visible           ${USER_PROFILE_FIRST_IMAGE}
    Capture Element                         ${USER_PROFILE_FIRST_IMAGE}             name=img1

    Click Picture Action                    ${USER_PROFILE_FIRST_IMAGE}             ${PICTURE_PULLER_EDIT_BUTTON}
    Wait Until Modal                        Manage picture
    Scroll Into View                        ${MODAL_DIALOG}
    Wait Until Element Is Visible           ${MODAL_DIALOG}
    Capture Element                         ${MODAL_DIALOG}                         name=img2

    Input Text                              ${CAPTION_INPUT}    ${EMPTY}
    Click Button                            ${MODAL_DIALOG_SUBMIT_BUTTON}
    Scroll Into View                        ${USER_PROFILE_FIRST_IMAGE}
    Wait Until Element Is Visible           ${USER_PROFILE_FIRST_IMAGE}
    Capture Element                         ${USER_PROFILE_FIRST_IMAGE}             name=img3

    Compare Images

*** Keywords ***

Test Setup
    Clear Database And Seed ${1} users
    Sign In ${USER_1.name} Fast
    Go To User ${USER_1.id}
    Post User avatar    ${CURDIR}/../../ressources/pictures/sample-picture.png
