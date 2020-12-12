*** Settings ***
Library         SeleniumLibrary  timeout=10  implicit_wait=0
Library         RobotEyes
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/PageMoves.robot
Resource        ../functions/Images.robot
Resource        ../vars/users.resource
Resource        ../vars/moves.resource
Force Tags      GeoKrety Details    Moves    Pictures    RobotEyes
Suite Setup     Seed

*** Test Cases ***

Anonymous should not see draggable
    Go To Url                                       ${PAGE_GEOKRETY_1_DETAILS_URL}
    Page Should Not Contain Element                 ${GEOKRET_MOVE_DROPZONE}
    Page Should Not Contain Element                 ${GEOKRET_MOVE_DROPZONE_PICTURE_UPLOAD_BUTTON}

Author himself should see draggable
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_GEOKRETY_1_DETAILS_URL}
    Page Should Contain Element                     ${GEOKRET_MOVE_DROPZONE}
    Page Should Contain Element                     ${GEOKRET_MOVE_DROPZONE_PICTURE_UPLOAD_BUTTON}

Authenticated should not see draggable for other users moves
    Sign In ${USER_2.name} Fast
    Go To Url                                       ${PAGE_GEOKRETY_1_DETAILS_URL}
    Page Should Not Contain Element                 ${GEOKRET_MOVE_DROPZONE}
    Page Should Not Contain Element                 ${GEOKRET_MOVE_DROPZONE_PICTURE_UPLOAD_BUTTON}

*** Keywords ***

Seed
    Clear DB And Seed 2 users
    Seed 1 geokrety owned by 1
    Post Move                                       ${MOVE_1}
