*** Settings ***
Resource        ../ressources/Authentication.robot
Resource        ../ressources/Moves.robot
Resource        ../ressources/Pictures.robot
Resource        ../ressources/Users.robot
Test Setup      Test Setup

*** Variables ***

*** Test Cases ***

Picture Owner Can Delete Picture
    Sign In ${USER_1.name} Fast
    Go To GeoKrety ${1}
    Click Picture Action                            ${GEOKRET_MOVE_FIRST_IMAGE}             ${PICTURE_PULLER_DELETE_BUTTON}
    Wait Until Modal                                Do you really want to delete this picture?
    Check Image                                     ${MODAL_DIALOG}${GEOKRET_MOVE_FIRST_IMAGE}
    Click Button                                    ${MODAL_DIALOG_SUBMIT_BUTTON}
    Element Should Not Be Visible                   ${GEOKRET_MOVE_FIRST_IMAGE}

*** Keywords ***

Test Setup
    Clear Database And Seed ${3} users
    Seed ${1} geokrety owned by ${2}
    Post Move                               ${MOVE_1}
    Post Move                               ${MOVE_2}
    Sign In ${USER_1.name} Fast
    Go To GeoKrety ${1}
    Upload picture via button               ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_DROPZONE}    ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}    ${PICTURES_DIR}/sample-picture.png
    Sign Out Fast
