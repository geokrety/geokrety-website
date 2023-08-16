*** Settings ***
Library         String
Resource        ../ressources/Authentication.robot
Resource        ../ressources/Moves.robot
Resource        ../ressources/vars/Urls.robot
Variables       ../ressources/vars/users.yml
Variables       ../ressources/vars/geokrety.yml
Suite Setup     Suite Setup

*** Variables ***

${PERCENT} =          %25

*** Test Cases ***

GeoKrety Search By Name
    Go To Url                               ${PAGE_SEARCH_BY_GEOKRETY_URL}              geokret=${GEOKRETY_1.name}
    Wait Until Page Contains Element        ${SEARCH_BY_GEOKRETY_TABLE}/tbody/tr
    Element Count Should Be                 ${SEARCH_BY_GEOKRETY_TABLE}/tbody/tr        1
    Check Search By GeoKrety                ${1}    ${GEOKRETY_1}

GeoKrety Search By GKID
    Go To Url                               ${PAGE_SEARCH_BY_GEOKRETY_URL}              geokret=${GEOKRETY_1.ref}
    Wait Until Page Contains Element        ${SEARCH_BY_GEOKRETY_TABLE}/tbody/tr
    Element Count Should Be                 ${SEARCH_BY_GEOKRETY_TABLE}/tbody/tr        1
    Check Search By GeoKrety                ${1}    ${GEOKRETY_1}

GeoKrety Search By GKID But Not Hex
    Go To Url                               ${PAGE_SEARCH_BY_GEOKRETY_URL}              geokret=GKXYZ00
    Element Count Should Be                 ${SEARCH_BY_GEOKRETY_TABLE}/tbody/tr        0

GeoKrety Search By Tracking Code
    Go To Url                               ${PAGE_SEARCH_BY_GEOKRETY_URL}              geokret=${GEOKRETY_1.tc}
    Wait Until Page Contains Element        ${SEARCH_BY_GEOKRETY_TABLE}/tbody/tr
    Element Count Should Be                 ${SEARCH_BY_GEOKRETY_TABLE}/tbody/tr        1
    Check Search By GeoKrety                ${1}    ${GEOKRETY_1}

Case Insensitive Match
    ${geokret_name_uppercase} =             Convert To Uppercase                        ${GEOKRETY_1.name}
    Go To Url                               ${PAGE_SEARCH_BY_GEOKRETY_URL}              geokret=${geokret_name_uppercase}
    Wait Until Page Contains Element        ${SEARCH_BY_GEOKRETY_TABLE}/tbody/tr
    Element Count Should Be                 ${SEARCH_BY_GEOKRETY_TABLE}/tbody/tr        1
    Check Search By GeoKrety                ${1}    ${GEOKRETY_1}

Wildcard Search
    Go To Url                               ${PAGE_SEARCH_BY_GEOKRETY_URL}              geokret=geokrety${PERCENT}
    Wait Until Page Contains Element        ${SEARCH_BY_GEOKRETY_TABLE}/tbody/tr
    Element Count Should Be                 ${SEARCH_BY_GEOKRETY_TABLE}/tbody/tr        2
    Check Search By GeoKrety                ${1}    ${GEOKRETY_1}
    Check Search By GeoKrety                ${2}    ${GEOKRETY_2}

    Go To Url                               ${PAGE_SEARCH_BY_GEOKRETY_URL}              geokret=${PERCENT}
    Wait Until Page Contains Element        ${SEARCH_BY_GEOKRETY_TABLE}/tbody/tr
    Element Count Should Be                 ${SEARCH_BY_GEOKRETY_TABLE}/tbody/tr        2
    Check Search By GeoKrety                ${1}    ${GEOKRETY_1}
    Check Search By GeoKrety                ${2}    ${GEOKRETY_2}

    Go To Url                               ${PAGE_SEARCH_BY_GEOKRETY_URL}              geokret=${PERCENT}krety02
    Wait Until Page Contains Element        ${SEARCH_BY_GEOKRETY_TABLE}/tbody/tr
    Element Count Should Be                 ${SEARCH_BY_GEOKRETY_TABLE}/tbody/tr        1
    Check Search By GeoKrety                ${1}    ${GEOKRETY_2}

Unexistent GeoKrety
    Go To Url                               ${PAGE_SEARCH_BY_GEOKRETY_URL}              geokret=idontexist
    Element Count Should Be                 ${SEARCH_BY_GEOKRETY_TABLE}/tbody/tr        0
    Page Should Contain                     No GeoKrety matching: idontexist

Empty Request
    Go To Url                               ${PAGE_SEARCH_BY_GEOKRETY_URL}              geokret=${EMPTY}    redirect=${PAGE_ADVANCED_SEARCH_URL}

*** Keywords ***

Suite Setup
    Clear Database And Seed ${2} users
    Seed ${2} geokrety owned by ${2}
    Sign Out Fast

#Check Search By GeoKrety
#    [Arguments]    ${row}    ${geokret}
#    Table Cell Should Contain               ${SEARCH_BY_GEOKRETY_TABLE}    ${row + 1}    2    ${geokret.name}
