*** Settings ***
Library         RequestsLibrary
Library         RobotEyes
Resource        ../../ressources/Authentication.robot
Resource        ../../ressources/Pictures.robot
Variables       ../../ressources/vars/users.yml
Test Setup      Test Setup

*** Test Cases ***

Delete User avatar picture
    Element Count Should Be                 ${USER_PROFILE_DROPZONE_IMAGE}    ${1}
    Click Picture Action                    ${USER_PROFILE_FIRST_IMAGE}             ${PICTURE_PULLER_DELETE_BUTTON}
    Wait Until Modal                        Do you really want to delete this picture?
    Check Image                             ${USER_PROFILE_FIRST_IMAGE}
    Click Button                            ${MODAL_DIALOG_SUBMIT_BUTTON}
    Element Should Not Be Visible           ${USER_PROFILE_PICTURES_PANEL}

Delete 1 picture should leave other present
    Element Count Should Be                 ${USER_PROFILE_DROPZONE_IMAGE}    ${1}
    Post User avatar                        ${CURDIR}/../../ressources/pictures/sample-picture2.png            position=2
    Element Count Should Be                 ${USER_PROFILE_DROPZONE_IMAGE}    ${2}

    Click Picture Action                    ${USER_PROFILE_FIRST_IMAGE}             ${PICTURE_PULLER_DELETE_BUTTON}
    Wait Until Modal                        Do you really want to delete this picture?
    Click Button                            ${MODAL_DIALOG_SUBMIT_BUTTON}
    Element Should Be Visible               ${USER_PROFILE_PICTURES_PANEL}
    Page Should Contain Element             ${USER_PROFILE_IMAGES}

    Element Count Should Be                 ${USER_PROFILE_IMAGES}                  1
    Check Image                             ${USER_PROFILE_PICTURES_PANEL}

*** Keywords ***

Test Setup
    Clear Database And Seed ${1} users
    Sign In ${USER_1.name} Fast
    Go To User ${USER_1.id}
    Post User avatar    ${CURDIR}/../../ressources/pictures/sample-picture.png
