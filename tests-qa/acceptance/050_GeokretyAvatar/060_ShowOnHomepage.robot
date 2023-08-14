*** Settings ***
Library         RequestsLibrary
Library         RobotEyes
Resource        ../ressources/Authentication.robot
Resource        ../ressources/Geokrety.robot
Resource        ../ressources/Pictures.robot
Variables       ../ressources/vars/users.yml
Variables       ../ressources/vars/geokrety.yml
Resource        ../ressources/vars/pages/Home.robot
Test Setup      Test Setup

*** Test Cases ***

GeoKrety avatars should be shown on Homepage
    Post GeoKret avatar                     ${CURDIR}/sample-picture.png    position=1
    Post GeoKret avatar                     ${CURDIR}/sample-picture2.png   position=2

    Go To Home
    Page Should Contain Element             ${HOME_PICTURE_LIST_PANEL}
    Element Count Should Be                 ${HOME_PICTURE_LIST_PICTURES}     2
    Check Image                             ${HOME_PICTURE_LIST_GALERY}

*** Keywords ***

Test Setup
    Clear Database And Seed ${1} users
    Seed ${1} geokrety owned by ${1}
    Sign In ${USER_1.name} Fast
    Go To GeoKrety ${GEOKRETY_1.id}
