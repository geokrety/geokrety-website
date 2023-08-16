*** Settings ***
Library         RequestsLibrary
Resource        ../../ressources/Authentication.robot
Resource        ../../ressources/Geokrety.robot
Variables       ../../ressources/vars/users.yml
Variables       ../../ressources/vars/geokrety.yml
Suite Setup     Suite Setup
Test Setup      Test Setup

*** Test Cases ***

Mission is shown - anonymous
    Go To GeoKrety ${GEOKRETY_1.id}
    Element Should Contain              ${GEOKRET_DETAILS_MISSION}              ${GEOKRETY_1.mission}

Placeholder when no mission - anonymous
    Go To GeoKrety ${GEOKRETY_2.id}
    Element Should Contain              ${GEOKRET_DETAILS_MISSION}              This GeoKret doesn't have a special mission…

Mission is shown - authenticated - owned
    Sign In ${USER_1.name} Fast
    Go To GeoKrety ${GEOKRETY_1.id}
    Element Should Contain              ${GEOKRET_DETAILS_MISSION}              ${GEOKRETY_1.mission}

Placeholder when no mission - authenticated - owned
    Sign In ${USER_2.name} Fast
    Go To GeoKrety ${GEOKRETY_2.id}
    Element Should Contain              ${GEOKRET_DETAILS_MISSION}              This GeoKret doesn't have a special mission…

Mission is shown - authenticated - not owned
    Sign In ${USER_2.name} Fast
    Go To GeoKrety ${GEOKRETY_1.id}
    Element Should Contain              ${GEOKRET_DETAILS_MISSION}              ${GEOKRETY_1.mission}

Placeholder when no mission - authenticated - not owned
    Sign In ${USER_1.name} Fast
    Go To GeoKrety ${GEOKRETY_2.id}
    Element Should Contain              ${GEOKRET_DETAILS_MISSION}              This GeoKret doesn't have a special mission…

*** Keywords ***

Suite Setup
    Clear Database
    Seed ${2} users

    Sign In ${USER_1.name} Fast
    Create GeoKret                      &{GEOKRETY_1}

    Sign In ${USER_2.name} Fast
    Create GeoKret                      &{GEOKRETY_2}

Test Setup
    Sign Out Fast
