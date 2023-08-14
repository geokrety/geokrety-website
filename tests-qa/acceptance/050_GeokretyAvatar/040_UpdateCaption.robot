*** Settings ***
Library         RequestsLibrary
Library         RobotEyes
Resource        ../ressources/Authentication.robot
Resource        ../ressources/Geokrety.robot
Resource        ../ressources/Pictures.robot
Variables       ../ressources/vars/users.yml
Variables       ../ressources/vars/geokrety.yml
Test Setup      Test Setup

*** Test Cases ***

Define GeoKret avatar caption
    Click Picture Action                    ${GEOKRET_DETAILS_AVATAR_FIRST_IMAGE}    ${PICTURE_PULLER_EDIT_BUTTON}
    Wait Until Modal                        Manage picture
    Input Text                              ${CAPTION_INPUT}    Bonjour
    Click Button                            ${MODAL_DIALOG_SUBMIT_BUTTON}
    Page Should Contain                     Picture caption saved.

    Open Eyes                               SeleniumLibrary  6
    Scroll Into View                        ${GEOKRET_DETAILS_AVATAR_FIRST_IMAGE}
    Wait Until Element Is Visible           ${GEOKRET_DETAILS_AVATAR_FIRST_IMAGE}
    # Workaround issue https://bugzilla.mozilla.org/show_bug.cgi?id=1708403
    Execute JavaScript                      window.scrollTo(0, 0)
    Capture Element                         ${GEOKRET_DETAILS_AVATAR_FIRST_IMAGE}    name=img1

    Click Picture Action                    ${GEOKRET_DETAILS_AVATAR_FIRST_IMAGE}    ${PICTURE_PULLER_EDIT_BUTTON}
    Wait Until Modal                        Manage picture
    # Workaround issue https://bugzilla.mozilla.org/show_bug.cgi?id=1708403
    Execute JavaScript                      window.scrollTo(0, 0)
    Capture Element                         ${MODAL_DIALOG}    name=img2

    Input Text                              ${CAPTION_INPUT}    ${EMPTY}
    Click Button                            ${MODAL_DIALOG_SUBMIT_BUTTON}
    Page Should Contain                     Picture caption saved.

    Scroll Into View                        ${GEOKRET_DETAILS_AVATAR_FIRST_IMAGE}
    Wait Until Element Is Visible           ${GEOKRET_DETAILS_AVATAR_FIRST_IMAGE}
    # Workaround issue https://bugzilla.mozilla.org/show_bug.cgi?id=1708403
    Execute JavaScript                      window.scrollTo(0, 0)
    Capture Element                         ${GEOKRET_DETAILS_AVATAR_FIRST_IMAGE}    name=img3

    Compare Images

*** Keywords ***

Test Setup
    Clear Database And Seed ${1} users
    Seed ${1} geokrety owned by ${1}
    Sign In ${USER_1.name} Fast
    Go To GeoKrety ${GEOKRETY_1.id}
    Post GeoKret avatar    ${CURDIR}/sample-picture.png
