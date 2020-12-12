*** Settings ***
Library         SeleniumLibrary  timeout=10  implicit_wait=0
Library         RobotEyes
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/Images.robot
Resource        ../vars/users.resource
Force Tags      Users Details    Pictures    RobotEyes
Suite Setup     Seed

*** Test Cases ***

Define Picture Caption
    Sign In ${USER_1.name} Fast
    Upload user avatar via button           ${PAGE_USER_1_PROFILE_URL}              ${CURDIR}/sample-picture.png
    Click Picture Action                    ${USER_PROFILE_FIRST_IMAGE}             ${PICTURE_PULLER_EDIT_BUTTON}

    Wait Until Modal                        Manage picture
    Input Text                              ${USER_PROFILE_AVATAR_CAPTION_INPUT}    Bonjour
    Click Button                            ${MODAL_DIALOG_SUBMIT_BUTTON}

    Open Eyes                               SeleniumLibrary  6
    Page Should Contain                     Picture caption saved.
    Scroll Into View                        ${USER_PROFILE_FIRST_IMAGE}
    Wait Until Element Is Visible           ${USER_PROFILE_FIRST_IMAGE}
    Capture Element                         ${USER_PROFILE_FIRST_IMAGE}             name=img1

    Click Picture Action                    ${USER_PROFILE_FIRST_IMAGE}             ${PICTURE_PULLER_EDIT_BUTTON}
    Scroll Into View                        ${MODAL_DIALOG}
    Wait Until Element Is Visible           ${MODAL_DIALOG}
    Capture Element                         ${MODAL_DIALOG}                         name=img2

    Input Text                              ${USER_PROFILE_AVATAR_CAPTION_INPUT}    ${EMPTY}
    Click Button                            ${MODAL_DIALOG_SUBMIT_BUTTON}
    Scroll Into View                        ${USER_PROFILE_FIRST_IMAGE}
    Wait Until Element Is Visible           ${USER_PROFILE_FIRST_IMAGE}
    Capture Element                         ${USER_PROFILE_FIRST_IMAGE}             name=img3

    Compare Images

*** Keywords

Seed
    Clear Database
    Seed 1 users
