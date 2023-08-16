*** Settings ***
Library         RequestsLibrary
Resource        ../../ressources/Authentication.robot
Resource        ../../ressources/Pictures.robot
Variables       ../../ressources/vars/users.yml
Test Setup      Test Setup

*** Test Cases ***

Upload image via button
    Sign In ${USER_1.name} Fast
    Post User Avatar                  ${CURDIR}/../../ressources/pictures/sample-picture.png

Upload image via Drag/Drop - same page
    Sign In ${USER_1.name} Fast
    Post User Avatar Via Drag/Drop    ${USER_PROFILE_ICON_IMAGE}

*** Keywords ***

Test Setup
    Clear Database And Seed ${1} users
    Sign In ${USER_1.name} Fast
    Go To User ${USER_1.id}
