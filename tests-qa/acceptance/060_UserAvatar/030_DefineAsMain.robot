*** Settings ***
Library         SeleniumLibrary  timeout=10  implicit_wait=0
Library         RobotEyes
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/Images.robot
Resource        ../vars/users.resource
Force Tags      Users Details    Pictures    RobotEyes
Test Setup      Clear DB And Seed 1 users

*** Test Cases ***

Set image as main avatar
    Open Eyes                               SeleniumLibrary  5

    Sign In ${USER_1.name} Fast
    Upload user avatar via button           ${PAGE_USER_1_PROFILE_URL}          ${CURDIR}/sample-picture.png
    Click Picture Action                    ${USER_PROFILE_FIRST_IMAGE}         ${PICTURE_PULLER_SET_AS_AVATAR_BUTTON}

    Wait Until Modal                        Do you want to set this picture as main avatar?
    Capture Element                         ${MODAL_DIALOG}    name=img1
    Click Button                            ${MODAL_DIALOG_SUBMIT_BUTTON}
    # Workaround issue https://bugzilla.mozilla.org/show_bug.cgi?id=1708403
    Execute JavaScript                      window.scrollTo(0, 0)
    Capture Element                         ${USER_PROFILE_AVATAR_GALLERY}    name=img2

    Compare Images

Set second image as main avatar
    Sign In ${USER_1.name} Fast
    Upload user avatar via button           ${PAGE_USER_1_PROFILE_URL}    ${CURDIR}/sample-picture.png     1
    Upload user avatar via button           ${PAGE_USER_1_PROFILE_URL}    ${CURDIR}/sample-picture2.png    2

    Click Picture Action                    ${USER_PROFILE_SECOND_IMAGE}        ${PICTURE_PULLER_SET_AS_AVATAR_BUTTON}
    Wait Until Modal                        Do you want to set this picture as main avatar?
    Click Button                            ${MODAL_DIALOG_SUBMIT_BUTTON}
    Check Image                             ${USER_PROFILE_PICTURES_PANEL}
