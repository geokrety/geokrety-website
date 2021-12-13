*** Settings ***
Library         RobotEyes
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/Images.robot
Resource        ../vars/users.resource
Force Tags      Users Details    Pictures    RobotEyes
Test Setup      Clear DB And Seed 1 users

*** Test Cases ***

Upload image via button
    Sign In ${USER_1.name} Fast
    Upload user avatar via button                   ${PAGE_USER_1_PROFILE_URL}    ${CURDIR}/sample-picture.png

Upload image via Drag/Drop - same page
    Sign In ${USER_1.name} Fast
    Upload user avatar via Drag/Drop - same page    ${PAGE_USER_1_PROFILE_URL}    ${USER_PROFILE_ICON_IMAGE}
