*** Settings ***
Library         DependencyLibrary
Resource        ../functions/PageGeoKretyCreate.robot
Resource        ../vars/users.resource
Resource        ../vars/geokrety.resource
Force Tags      GeoKrety Details
Suite Setup     Seed

*** Test Cases ***

Mission is shown - anonymous
    Sign Out Fast
    Go To GeoKrety 1 url
    Element Should Contain              ${GEOKRET_DETAILS_MISSION}              ${GEOKRETY_1.mission}

Placeholder when no mission - anonymous
    Sign Out Fast
    Go To GeoKrety 2 url
    Element Should Contain              ${GEOKRET_DETAILS_MISSION}              This GeoKret doesn't have a special mission…

Mission is shown - authenticated - owned
    Sign In ${USER_1.name} Fast
    Go To GeoKrety 1 url
    Element Should Contain              ${GEOKRET_DETAILS_MISSION}              ${GEOKRETY_1.mission}
    Sign Out Fast

Placeholder when no mission - authenticated - owned
    Sign In ${USER_2.name} Fast
    Go To GeoKrety 2 url
    Element Should Contain              ${GEOKRET_DETAILS_MISSION}              This GeoKret doesn't have a special mission…
    Sign Out Fast

Mission is shown - authenticated - not owned
    Sign In ${USER_2.name} Fast
    Go To GeoKrety 1 url
    Element Should Contain              ${GEOKRET_DETAILS_MISSION}              ${GEOKRETY_1.mission}
    Sign Out Fast

Placeholder when no mission - authenticated - not owned
    Sign In ${USER_1.name} Fast
    Go To GeoKrety 2 url
    Element Should Contain              ${GEOKRET_DETAILS_MISSION}              This GeoKret doesn't have a special mission…
    Sign Out Fast

*** Keywords ***

Seed
    Clear Database
    Seed 2 users

    Sign In ${USER_1.name} Fast
    Create GeoKret                      ${GEOKRETY_1}

    Sign In ${USER_2.name} Fast
    Create GeoKret                      ${GEOKRETY_2}
