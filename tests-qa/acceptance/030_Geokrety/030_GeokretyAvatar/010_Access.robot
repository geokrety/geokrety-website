*** Settings ***
Library         RequestsLibrary
Resource        ../../ressources/Authentication.robot
Resource        ../../ressources/Geokrety.robot
Resource        ../../ressources/Pictures.robot
Variables       ../../ressources/vars/users.yml
Variables       ../../ressources/vars/geokrety.yml
Suite Setup     Suite Setup

*** Test Cases ***

Anonymous should not see draggable
    Sign Out Fast
    Go To GeoKrety ${GEOKRETY_1.id}
    Page Should Not Contain Element                 ${GEOKRET_DETAILS_AVATAR_DROPZONE}

User himself should see draggable
    Sign In ${USER_1.name} Fast
    Go To GeoKrety ${GEOKRETY_1.id}
    Page Should Contain Element                     ${GEOKRET_DETAILS_AVATAR_DROPZONE}

Authenticated should not see draggable for other users
    Sign In ${USER_1.name} Fast
    Go To GeoKrety ${GEOKRETY_2.id}
    Page Should Not Contain Element                 ${GEOKRET_DETAILS_AVATAR_DROPZONE}

*** Keywords ***

Suite Setup
    Clear Database And Seed ${2} users
    Seed ${1} geokrety owned by ${1}
    Seed ${1} geokrety owned by ${2}
