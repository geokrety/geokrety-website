*** Settings ***
Library         RobotEyes
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/PageMoves.robot
Resource        ../functions/Images.robot
Resource        ../vars/users.resource
Resource        ../vars/moves.resource
Force Tags      GeoKrety Details    Moves    Pictures    RobotEyes
Test Setup      Seed

*** Variables ***
${CAPTION} =    Bonjour!

*** Test Cases ***

Picture Owner Can Delete Picture
    Sign In ${USER_1.name} Fast
    Go To GeoKrety ${1} url
    Click Picture Action                            ${GEOKRET_MOVE_FIRST_IMAGE}             ${PICTURE_PULLER_DELETE_BUTTON}
    Wait Until Modal                                Do you really want to delete this picture?
    Check Image                                     ${MODAL_DIALOG}${GEOKRET_MOVE_FIRST_IMAGE}
    Click Button                                    ${MODAL_DIALOG_SUBMIT_BUTTON}
    Element Should Not Be Visible                   ${GEOKRET_MOVE_FIRST_IMAGE}

*** Keywords ***

Seed
    Clear DB And Seed 3 users
    Seed 1 geokrety owned by 2
    Post Move                                       ${MOVE_1}
    Post Move                                       ${MOVE_2}
    Sign In ${USER_1.name} Fast
    Go To GeoKrety ${1} url
    Upload picture via button                       ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_DROPZONE}    ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}    ${CURDIR}/sample-picture.png
    Sign Out Fast
