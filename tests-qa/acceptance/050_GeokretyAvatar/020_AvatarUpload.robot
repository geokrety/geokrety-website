*** Settings ***
Library         RequestsLibrary
Resource        ../ressources/Authentication.robot
Resource        ../ressources/Geokrety.robot
Resource        ../ressources/Pictures.robot
Variables       ../ressources/vars/users.yml
Variables       ../ressources/vars/geokrety.yml
Test Setup      Test Setup

*** Test Cases ***

Upload image via button
    Post GeoKret Avatar                  ${CURDIR}/sample-picture.png

Upload image via Drag/Drop
    Post GeoKret Avatar Via Drag/Drop    ${GEOKRET_DETAILS_TYPE_IMG}

*** Keywords ***

Test Setup
    Clear Database And Seed ${1} users
    Seed ${1} geokrety owned by ${1}
    Sign In ${USER_1.name} Fast
    Go To GeoKrety ${GEOKRETY_1.id}
