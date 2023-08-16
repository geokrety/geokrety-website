*** Settings ***
Resource        ../ressources/Authentication.robot
Resource        ../ressources/Moves.robot
Resource        ../ressources/Pictures.robot
Variables       ../ressources/vars/users.yml
Test Setup      Test Setup

*** Test Cases ***

Anonymous should not see draggable
    Sign Out Fast
    Go To GeoKrety ${1}
    Page Should Not Contain Element                 ${GEOKRET_MOVE_DROPZONE}
    Page Should Not Contain Element                 ${GEOKRET_MOVE_DROPZONE_PICTURE_UPLOAD_BUTTON}

Author himself should see draggable
    Sign In ${USER_1.name} Fast
    Go To GeoKrety ${1}
    Page Should Contain Element                     ${GEOKRET_MOVE_DROPZONE}
    Page Should Contain Element                     ${GEOKRET_MOVE_DROPZONE_PICTURE_UPLOAD_BUTTON}

Authenticated should not see draggable for other users moves
    Sign In ${USER_2.name} Fast
    Go To Url                                       ${PAGE_GEOKRETY_1_DETAILS_URL}
    Page Should Not Contain Element                 ${GEOKRET_MOVE_DROPZONE}
    Page Should Not Contain Element                 ${GEOKRET_MOVE_DROPZONE_PICTURE_UPLOAD_BUTTON}

*** Keywords ***

Test Setup
    Clear Database And Seed ${2} users
    Seed ${1} geokrety owned by ${1}
    Post Move                               ${MOVE_1}
