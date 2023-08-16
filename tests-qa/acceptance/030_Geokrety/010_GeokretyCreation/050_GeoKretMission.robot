*** Settings ***
Library         RequestsLibrary
Resource        ../../ressources/Authentication.robot
Resource        ../../ressources/Geokrety.robot
Variables       ../../ressources/vars/users.yml
Variables       ../../ressources/vars/geokrety.yml
Test Setup      Test Setup

*** Test Cases ***

Text valid
    [Template]          GeoKret is created
    ${EMPTY}                            This GeoKret doesn't have a special mission…
    ${GEOKRETY_1.mission}               ${GEOKRETY_1.mission}
    A${SPACE*5}A                        A A
    🦦 Otter                            🦦 Otter
    🐨🐼🦥 Sloth                         🐨🐼🦥 Sloth
    🦨                                  🦨
    \# hello                            hello
    \#hello                             \#hello
    [geokrety website](http://geokrety.org)                 geokrety website
    ![GeoKrety logo](/assets/images/the-mole-grey.svg)      ${EMPTY}


*** Keywords ***

Test Setup
    Clear Database And Seed ${1} users
    Sign In ${USER_1.name} Fast

GeoKret is created
    [Arguments]    ${mission}    ${expected}=${mission}
    Go To Url                           ${PAGE_GEOKRETY_CREATE_URL}
    &{gk} =    Create Dictionary        name=geokret    type=0    mission=${mission}
    Fill Creation Form                  &{gk}
    Click Button                        ${GEOKRET_CREATE_CREATE_BUTTON}
    Flash message shown                 Your GeoKret has been created.
    Element Should Contain              ${GEOKRET_DETAILS_MISSION}    ${expected}
