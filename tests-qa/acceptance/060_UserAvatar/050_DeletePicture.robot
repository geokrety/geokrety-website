*** Settings ***
Library         RobotEyes
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/Images.robot
Resource        ../vars/users.resource
Force Tags      Users Details    Pictures    RobotEyes
Test Setup      Clear DB And Seed 1 users

*** Test Cases ***

Delete Picture
    Sign In ${USER_1.name} Fast
    Upload user avatar via button           ${PAGE_USER_1_PROFILE_URL}              ${CURDIR}/sample-picture.png
    Click Picture Action                    ${USER_PROFILE_FIRST_IMAGE}             ${PICTURE_PULLER_DELETE_BUTTON}
    Wait Until Modal                        Do you really want to delete this picture?
    Check Image                             ${USER_PROFILE_FIRST_IMAGE}
    Click Button                            ${MODAL_DIALOG_SUBMIT_BUTTON}
    Element Should Not Be Visible           ${USER_PROFILE_PICTURES_PANEL}

Delete 1 picture should leave other present
    Sign In ${USER_1.name} Fast
    Upload user avatar via button           ${PAGE_USER_1_PROFILE_URL}              ${CURDIR}/sample-picture.png     count=1
    Upload user avatar via button           ${PAGE_USER_1_PROFILE_URL}              ${CURDIR}/sample-picture2.png    count=2
    Click Picture Action                    ${USER_PROFILE_FIRST_IMAGE}             ${PICTURE_PULLER_DELETE_BUTTON}
    Wait Until Modal                        Do you really want to delete this picture?
    Click Button                            ${MODAL_DIALOG_SUBMIT_BUTTON}
    Element Should Be Visible               ${USER_PROFILE_PICTURES_PANEL}
    Page Should Contain Element             ${USER_PROFILE_IMAGES}

    Element Count Should Be                 ${USER_PROFILE_IMAGES}                  1
    Check Image                             ${USER_PROFILE_PICTURES_PANEL}
