*** Settings ***
Resource        ../ressources/Authentication.robot
Resource        ../ressources/Moves.robot
Resource        ../ressources/Pictures.robot
Variables       ../ressources/vars/users.yml
Test Setup      Test Setup

*** Test Cases ***

Upload image via button
    Upload picture via button                       ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_DROPZONE}    ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}    ${PICTURES_DIR}/sample-picture.png
    Element Count Should Be                         ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}/figure    1
    Element Count Should Be                         ${GEOKRET_DETAILS_MOVE_2}${GEOKRET_MOVE_IMAGES}/figure    0

Upload image via Drag/Drop - same page
    Upload Picture Via Drag/Drop                    ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_DROPZONE}    ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}    ${GEOKRET_DETAILS_TYPE_IMG}
    Element Count Should Be                         ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}/figure    1
    Element Count Should Be                         ${GEOKRET_DETAILS_MOVE_2}${GEOKRET_MOVE_IMAGES}/figure    0

Upload multiple image via button
    Upload picture via button                       ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_DROPZONE}    ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}    ${PICTURES_DIR}/sample-picture.png    position=1
    Upload picture via button                       ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_DROPZONE}    ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}    ${PICTURES_DIR}/sample-picture.png    position=2
    Element Count Should Be                         ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}/figure    2
    Element Count Should Be                         ${GEOKRET_DETAILS_MOVE_2}${GEOKRET_MOVE_IMAGES}/figure    0

Upload images to different moves
    Upload picture via button                       ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_DROPZONE}    ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}    ${PICTURES_DIR}/sample-picture.png    position=1
    Upload picture via button                       ${GEOKRET_DETAILS_MOVE_2}${GEOKRET_MOVE_DROPZONE}    ${GEOKRET_DETAILS_MOVE_2}${GEOKRET_MOVE_IMAGES}    ${PICTURES_DIR}/sample-picture.png    position=1
    Element Count Should Be                         ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}/figure    1
    Element Count Should Be                         ${GEOKRET_DETAILS_MOVE_2}${GEOKRET_MOVE_IMAGES}/figure    1

Upload webp image
    Upload picture via button                       ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_DROPZONE}    ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}    ${PICTURES_DIR}/pulp_loose.webp
    Element Count Should Be                         ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}/figure    1
    Element Count Should Be                         ${GEOKRET_DETAILS_MOVE_2}${GEOKRET_MOVE_IMAGES}/figure    0

Upload invalid image
    Upload Picture Via Button Base                  ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_DROPZONE}    ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}    ${PICTURES_DIR}/not-an-image.jpg
    Alert Should Be Present                         text=Image processing failed. This image type is probably not supported
    Element Count Should Be                         ${GEOKRET_DETAILS_MOVE_2}${GEOKRET_MOVE_IMAGES}/figure    0

Upload invalid image - EntityTooSmall
    Sign In ${USER_1.name} Fast
    Go To GeoKrety ${1}
    Upload Picture Via Button Base                  ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_DROPZONE}    ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}    ${PICTURES_DIR}/not-an-image2.jpg
    Alert Should Be Present                         text=Your upload does not meet the minimum allowed size.
    Element Count Should Be                         ${GEOKRET_DETAILS_MOVE_2}${GEOKRET_MOVE_IMAGES}/figure    0

*** Keywords ***

Test Setup
    Clear Database And Seed ${2} users
    Seed ${1} geokrety owned by ${2}
    Post Move                               ${MOVE_1}
    Post Move                               ${MOVE_2}
    Sign In ${USER_1.name} Fast
    Go To GeoKrety ${1}
