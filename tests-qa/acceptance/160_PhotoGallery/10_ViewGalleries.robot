*** Settings ***
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/PageMoves.robot
Resource        ../functions/Images.robot
Resource        ../vars/users.resource
Resource        ../vars/geokrety.resource
Resource        ../vars/moves.resource
Force Tags      Gallery
Suite Setup     Seed

*** Test Cases ***

Gallery Page Should Show All Types Of Uploaded Pictures
    Sign Out Fast
    Go To Url                                       ${PAGE_PICTURES_GALLERY_URL}
    Element Count Should Be                         ${GALLERY_IMAGES}                                   4
    Page Should Contain Element                     ${GALLERY_FIRST_IMAGE}\[@data-picture-type="1"]
    Page Should Contain Element                     ${GALLERY_SECOND_IMAGE}\[@data-picture-type="0"]
    Page Should Contain Element                     ${GALLERY_THIRD_IMAGE}\[@data-picture-type="0"]
    Page Should Contain Element                     ${GALLERY_FOURTH_IMAGE}\[@data-picture-type="2"]

User Posted Pictures Page Should Show All Types Of Uploaded Pictures
    Sign Out Fast
    Go To Url                                       ${PAGE_USER_POSTED_PICTURES_URL}                    userid=1
    Element Count Should Be                         ${GALLERY_IMAGES}                                   4
    Page Should Contain Element                     ${GALLERY_FIRST_IMAGE}\[@data-picture-type="1"]
    Page Should Contain Element                     ${GALLERY_SECOND_IMAGE}\[@data-picture-type="0"]
    Page Should Contain Element                     ${GALLERY_THIRD_IMAGE}\[@data-picture-type="0"]
    Page Should Contain Element                     ${GALLERY_FOURTH_IMAGE}\[@data-picture-type="2"]

User Owned GeoKrety Posted Pictures Page Should Show GeoKrety Uploaded Pictures
    Sign Out Fast
    Go To Url                                       ${PAGE_USER_OWNED_GEOKRETY_PICTURES_URL}            userid=1
    Element Count Should Be                         ${GALLERY_IMAGES}                                   3
    Page Should Contain Element                     ${GALLERY_FIRST_IMAGE}\[@data-picture-type="1"]
    Page Should Contain Element                     ${GALLERY_SECOND_IMAGE}\[@data-picture-type="0"]
    Page Should Contain Element                     ${GALLERY_THIRD_IMAGE}\[@data-picture-type="0"]

No Pictures Placeholder - owned GeoKrety
    Sign Out Fast
    Go To Url                                       ${PAGE_USER_OWNED_GEOKRETY_PICTURES_URL}            userid=2
    Element Count Should Be                         ${GALLERY_IMAGES}                                   0
    Page Should Contain                             There is no pictures yet.

No Pictures Placeholder - posted
    Sign Out Fast
    Go To Url                                       ${PAGE_USER_POSTED_PICTURES_URL}                    userid=2
    Element Count Should Be                         ${GALLERY_IMAGES}                                   0
    Page Should Contain                             There is no pictures yet.


*** Keywords ***

Seed
    Clear DB And Seed 2 users
    Seed 2 geokrety owned by 1
    Post User Avatar
    Post GeoKret Avatar                             gkid=1
    Post GeoKret Avatar                             gkid=2    file=sample-picture3.jpg
    Post Move Picture
    Sign Out Fast

Post User Avatar
    Sign In ${USER_1.name} Fast
    Go To User ${1} url
    Upload picture via button                       ${USER_PROFILE_DROPZONE}                ${USER_PROFILE_DROPZONE_IMAGE}      ${CURDIR}/sample-picture1.jpg

Post GeoKret Avatar
    [Arguments]    ${gkid}=1    ${file}=sample-picture2.jpg
    Sign In ${USER_1.name} Fast
    Go To GeoKrety ${gkid} url
    Upload picture via button                       ${GEOKRET_DETAILS_AVATAR_DROPZONE}      ${GEOKRET_DETAILS_AVATAR_IMAGES}    ${CURDIR}/${file}

Post Move Picture
    Post Move                                       ${MOVE_1}
    Sign In ${USER_1.name} Fast
    Go To GeoKrety ${1} url
    Upload picture via button                       ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_DROPZONE}    ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}    ${CURDIR}/sample-picture4.jpg
