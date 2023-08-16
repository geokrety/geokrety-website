*** Settings ***
Library         RequestsLibrary
Library         RobotEyes
Resource        ../../ressources/Authentication.robot
Resource        ../../ressources/Geokrety.robot
Resource        ../../ressources/Pictures.robot
Variables       ../../ressources/vars/users.yml
Variables       ../../ressources/vars/geokrety.yml
Resource        ../../ressources/vars/pages/Home.robot
Test Setup      Test Setup

*** Test Cases ***

Users avatars should be shown on Homepage
    Go To User ${USER_1.id}
    Post User avatar                        ${CURDIR}/../../ressources/pictures/sample-picture.png
    Post User avatar                        ${CURDIR}/../../ressources/pictures/sample-picture2.png

    Go To Home
    Page Should Contain Element             ${HOME_PICTURE_LIST_PANEL}
    Element Count Should Be                 ${HOME_PICTURE_LIST_PICTURES}     2
    Check Image                             ${HOME_PICTURE_LIST_GALERY}


*** Keywords ***

Test Setup
    Clear Database And Seed ${1} users
    Sign In ${USER_1.name} Fast
