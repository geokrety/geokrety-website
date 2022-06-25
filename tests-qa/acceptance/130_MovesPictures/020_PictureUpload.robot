*** Settings ***
Library         RobotEyes
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/PageMoves.robot
Resource        ../functions/Images.robot
Resource        ../vars/users.resource
Resource        ../vars/moves.resource
Force Tags      GeoKrety Details    Moves    Pictures    RobotEyes
Test Setup      Seed

*** Test Cases ***

Upload image via button
    Sign In ${USER_1.name} Fast
    Go To GeoKrety ${1} url
    Upload picture via button                       ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_DROPZONE}    ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}    ${CURDIR}/sample-picture.png
    Element Count Should Be                         ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}/figure    1
    Element Count Should Be                         ${GEOKRET_DETAILS_MOVE_2}${GEOKRET_MOVE_IMAGES}/figure    0

Upload image via Drag/Drop - same page
    Sign In ${USER_1.name} Fast
    Go To GeoKrety ${1} url
    Upload picture via via Drag/Drop - same page    ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_DROPZONE}    ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}    ${GEOKRET_DETAILS_TYPE_IMG}
    Element Count Should Be                         ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}/figure    1
    Element Count Should Be                         ${GEOKRET_DETAILS_MOVE_2}${GEOKRET_MOVE_IMAGES}/figure    0

Upload multiple image via button
    Sign In ${USER_1.name} Fast
    Go To GeoKrety ${1} url
    Upload picture via button                       ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_DROPZONE}    ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}    ${CURDIR}/sample-picture.png    position=1
    Upload picture via button                       ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_DROPZONE}    ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}    ${CURDIR}/sample-picture.png    position=2
    Element Count Should Be                         ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}/figure    2
    Element Count Should Be                         ${GEOKRET_DETAILS_MOVE_2}${GEOKRET_MOVE_IMAGES}/figure    0

Upload images to different moves
    Sign In ${USER_1.name} Fast
    Go To GeoKrety ${1} url
    Upload picture via button                       ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_DROPZONE}    ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}    ${CURDIR}/sample-picture.png    position=1
    Upload picture via button                       ${GEOKRET_DETAILS_MOVE_2}${GEOKRET_MOVE_DROPZONE}    ${GEOKRET_DETAILS_MOVE_2}${GEOKRET_MOVE_IMAGES}    ${CURDIR}/sample-picture.png    position=1
    Element Count Should Be                         ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}/figure    1
    Element Count Should Be                         ${GEOKRET_DETAILS_MOVE_2}${GEOKRET_MOVE_IMAGES}/figure    1

# See bug https://github.com/geokrety/geokrety-website/issues/742
Upload invalid image
    Sign In ${USER_1.name} Fast
    Go To GeoKrety ${1} url
    Upload picture via button base                  ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_DROPZONE}    ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}    ${CURDIR}/pulp_loose.webp
    Alert Should Be Present                         text=Image processing failed. This image type is probably not supported
    Element Count Should Be                         ${GEOKRET_DETAILS_MOVE_2}${GEOKRET_MOVE_IMAGES}/figure    0

*** Keywords ***

Seed
    Clear DB And Seed 2 users
    Seed 1 geokrety owned by 2
    Post Move                                       ${MOVE_1}
    Post Move                                       ${MOVE_2}
