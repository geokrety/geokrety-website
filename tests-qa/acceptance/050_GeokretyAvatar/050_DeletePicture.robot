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

Delete GeoKret avatar picture
    Element Count Should Be                 ${GEOKRET_DETAILS_AVATAR_IMAGES_ALL}    ${1}
    Click Picture Action                    ${GEOKRET_DETAILS_AVATAR_FIRST_IMAGE}    ${PICTURE_PULLER_DELETE_BUTTON}
    Wait Until Modal                        Do you really want to delete this picture?
    Check Image                             ${GEOKRET_DETAILS_AVATAR_FIRST_IMAGE}
    Click Button                            ${MODAL_DIALOG_SUBMIT_BUTTON}
    Element Should Not Be Visible           ${GEOKRET_DETAILS_PICTURES_PANEL}

Delete 1 GeoKret avatar should leave others present
    Element Count Should Be                 ${GEOKRET_DETAILS_AVATAR_IMAGES_ALL}    ${1}
    Post GeoKret avatar                     ${CURDIR}/sample-picture2.png            position=2
    Element Count Should Be                 ${GEOKRET_DETAILS_AVATAR_IMAGES_ALL}    ${2}

    Click Picture Action                    ${GEOKRET_DETAILS_AVATAR_FIRST_IMAGE}    ${PICTURE_PULLER_DELETE_BUTTON}
    Wait Until Modal                        Do you really want to delete this picture?
    Click Button                            ${MODAL_DIALOG_SUBMIT_BUTTON}
    Element Should Be Visible               ${GEOKRET_DETAILS_PICTURES_PANEL}
    Page Should Contain Element             ${GEOKRET_DETAILS_AVATAR_IMAGES}

    Element Count Should Be                 ${GEOKRET_DETAILS_AVATAR_IMAGES_ALL}    ${1}
    Check Image                             ${GEOKRET_DETAILS_PICTURES_PANEL}

*** Keywords ***

Test Setup
    Clear Database And Seed ${1} users
    Seed ${1} geokrety owned by ${1}
    Sign In ${USER_1.name} Fast
    Go To GeoKrety ${GEOKRETY_1.id}
    Post GeoKret avatar    ${CURDIR}/sample-picture.png
