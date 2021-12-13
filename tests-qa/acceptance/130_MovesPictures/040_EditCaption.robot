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

Picture Owner Can Set Caption
    Sign In ${USER_1.name} Fast
    Go To GeoKrety ${1} url
    Click Picture Action                            ${GEOKRET_MOVE_FIRST_IMAGE}                             ${PICTURE_PULLER_EDIT_BUTTON}
    Wait Until Modal                                Manage picture
    Input Text                                      ${USER_PROFILE_AVATAR_CAPTION_INPUT}                    ${CAPTION}
    Click Button                                    ${MODAL_DIALOG_SUBMIT_BUTTON}
    Location Should Be                              ${PAGE_GEOKRETY_1_DETAILS_URL}?#log1
    Element Text Should Be                          ${GEOKRET_MOVE_FIRST_IMAGE}/figure/figcaption/p         ${CAPTION}

Picture Owner Can Edit Caption
    Sign In ${USER_1.name} Fast
    Go To GeoKrety ${1} url
    Click Picture Action                            ${GEOKRET_MOVE_FIRST_IMAGE}                             ${PICTURE_PULLER_EDIT_BUTTON}
    Wait Until Modal                                Manage picture
    Input Text                                      ${USER_PROFILE_AVATAR_CAPTION_INPUT}                    ${CAPTION}
    Click Button                                    ${MODAL_DIALOG_SUBMIT_BUTTON}

    Click Picture Action                            ${GEOKRET_MOVE_FIRST_IMAGE}                             ${PICTURE_PULLER_EDIT_BUTTON}
    Wait Until Modal                                Manage picture
    Input Text                                      ${USER_PROFILE_AVATAR_CAPTION_INPUT}                    ${EMPTY}
    Click Button                                    ${MODAL_DIALOG_SUBMIT_BUTTON}
    Location Should Be                              ${PAGE_GEOKRETY_1_DETAILS_URL}?#log1
    Element Text Should Be                          ${GEOKRET_MOVE_FIRST_IMAGE}/figure/figcaption/p         ${EMPTY}

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
