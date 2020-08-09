*** Settings ***
Library         SeleniumLibrary  timeout=10  implicit_wait=0
Resource        ../functions/PageGeoKretyCreate.robot
Resource        ../vars/users.resource
Resource        ../vars/geokrety.resource
Force Tags      Create GeoKrety
Suite Setup     Seed

*** Test Cases ***

Create A GeoKret
    Go To Url                           ${PAGE_GEOKRETY_CREATE_URL}
    Create GeoKret                      ${GEOKRETY_1}
    Element Should Contain              ${GEOKRET_DETAILS_NAME}         ${GEOKRETY_1.name}

*** Keywords ***

Seed
    Clear Database
    Seed 1 users
    Sign In ${USER_1.name} Fast
