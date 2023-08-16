*** Settings ***
Resource        ../ressources/Authentication.robot
Resource        ../ressources/vars/Urls.robot
Resource        ../ressources/Pictures.robot
Resource        ../ressources/Moves.robot
Variables       ../ressources/vars/users.yml
Variables       ../ressources/vars/geokrety.yml
Suite Setup     Suite Setup

*** Test Cases ***

Gallery Page Should Show All Types Of Uploaded Pictures
    Go To Url                                       ${PAGE_PICTURES_GALLERY_URL}
    Element Count Should Be                         ${GALLERY_IMAGES}                                   4
    Page Should Contain Element                     ${GALLERY_FIRST_IMAGE}\[@data-picture-type="1"]
    Page Should Contain Element                     ${GALLERY_SECOND_IMAGE}\[@data-picture-type="0"]
    Page Should Contain Element                     ${GALLERY_THIRD_IMAGE}\[@data-picture-type="0"]
    Page Should Contain Element                     ${GALLERY_FOURTH_IMAGE}\[@data-picture-type="2"]

User Posted Pictures Page Should Show All Types Of Uploaded Pictures
    Go To Url                                       ${PAGE_USER_POSTED_PICTURES_URL}                    userid=1
    Element Count Should Be                         ${GALLERY_IMAGES}                                   4
    Page Should Contain Element                     ${GALLERY_FIRST_IMAGE}\[@data-picture-type="1"]
    Page Should Contain Element                     ${GALLERY_SECOND_IMAGE}\[@data-picture-type="0"]
    Page Should Contain Element                     ${GALLERY_THIRD_IMAGE}\[@data-picture-type="0"]
    Page Should Contain Element                     ${GALLERY_FOURTH_IMAGE}\[@data-picture-type="2"]

User Owned GeoKrety Posted Pictures Page Should Show GeoKrety Uploaded Pictures
    Go To Url                                       ${PAGE_USER_OWNED_GEOKRETY_PICTURES_URL}            userid=1
    Element Count Should Be                         ${GALLERY_IMAGES}                                   3
    Page Should Contain Element                     ${GALLERY_FIRST_IMAGE}\[@data-picture-type="1"]
    Page Should Contain Element                     ${GALLERY_SECOND_IMAGE}\[@data-picture-type="0"]
    Page Should Contain Element                     ${GALLERY_THIRD_IMAGE}\[@data-picture-type="0"]

User Owned GeoKrety Posted Pictures Garbage Page Number
    Go To Url                                       ${PAGE_USER_OWNED_GEOKRETY_PICTURES_URL}/page/FOOBAR            userid=1

User Owned GeoKrety Posted Pictures Unexistant Page Number
    Go To Url                                       ${PAGE_USER_OWNED_GEOKRETY_PICTURES_URL}/page/12345            userid=1


No Pictures Placeholder - owned GeoKrety
    Go To Url                                       ${PAGE_USER_OWNED_GEOKRETY_PICTURES_URL}            userid=2
    Element Count Should Be                         ${GALLERY_IMAGES}                                   0
    Page Should Contain                             There is no pictures yet.

No Pictures Placeholder - posted
    Go To Url                                       ${PAGE_USER_POSTED_PICTURES_URL}                    userid=2
    Element Count Should Be                         ${GALLERY_IMAGES}                                   0
    Page Should Contain                             There is no pictures yet.


*** Keywords ***

Suite Setup
    Clear Database And Seed ${2} users
    Seed ${2} geokrety owned by ${1}
    Post User Avatar Wrapper                                                 file=${CURDIR}/sample-picture1.jpg
    Post GeoKret Avatar Wrapper                     gkid=${GEOKRETY_1.id}    file=${CURDIR}/sample-picture2.jpg
    Post GeoKret Avatar Wrapper                     gkid=${GEOKRETY_2.id}    file=${CURDIR}/sample-picture3.jpg
    Post Move Picture Wrapper                                                file=${CURDIR}/sample-picture4.jpg
    Sign Out Fast

Post User Avatar Wrapper
    [Arguments]    ${file}=${CURDIR}/sample-picture1.jpg
    Sign In ${USER_1.name} Fast
    Go To User ${1}
    Post User Avatar                                file=${file}

Post GeoKret Avatar Wrapper
    [Arguments]    ${gkid}=1    ${file}=${CURDIR}/sample-picture1.jpg
    Sign In ${USER_1.name} Fast
    Go To GeoKrety ${gkid}
    Post GeoKret Avatar                             ${file}

Post Move Picture Wrapper
    [Arguments]    ${file}=${CURDIR}/sample-picture1.jpg
    Post Move                                       ${MOVE_1}
    Sign In ${USER_1.name} Fast
    Go To GeoKrety ${1}
    Post Move Picture                               ${file}    ${1}
