*** Settings ***
Library         DependencyLibrary
Resource        ../functions/PageGeoKretyCreate.robot
Resource        ../vars/users.resource
Resource        ../vars/geokrety.resource
Force Tags      GeoKrety Details
Suite Setup     Seed

*** Test Cases ***

Public Information Visible - anonymous
    Has Public Information Visible - all geokrety

Private Information Not Visible - anonymous
    Has Not Private Information Visible - all geokrety

Public Information Visible - authenticated
    Sign In ${USER_1.name} Fast
    Has Public Information Visible - all geokrety
    Sign Out Fast

Private Information Visible - authenticated - owned
    Sign In ${USER_1.name} Fast
    Has Private Information             ${PAGE_GEOKRETY_1_DETAILS_URL}      ${GEOKRETY_1}
    Sign Out Fast

Private Information Not Visible - authenticated - not owned
    Sign In ${USER_1.name} Fast
    Has Not Private Information         ${PAGE_GEOKRETY_2_DETAILS_URL}
    Sign Out Fast


*** Keywords ***

Seed
    Clear Database
    Seed 2 users
    Seed 1 geokrety owned by 1
    Seed 1 geokrety owned by 2

Has Public Information Visible - all geokrety
    Has Public Information              ${PAGE_GEOKRETY_1_DETAILS_URL}      ${GEOKRETY_1}      ${USER_1}
    Has Public Information              ${PAGE_GEOKRETY_2_DETAILS_URL}      ${GEOKRETY_2}      ${USER_2}

Has Not Private Information Visible - all geokrety
    Has Not Private Information         ${PAGE_GEOKRETY_1_DETAILS_URL}
    Has Not Private Information         ${PAGE_GEOKRETY_2_DETAILS_URL}

Has Public Information
    [Arguments]             ${url}      ${gk}    ${owner}
    Go To Url                           ${url}
    Element Should Contain              ${GEOKRET_DETAILS_NAME}                     ${gk.name}
    Element Should Contain              ${GEOKRET_DETAILS_TYPE}                     (Traditional)
    Element Should Contain              ${GEOKRET_DETAILS_OWNER}                    ${owner.name}
    Page Should Contain Element         ${GEOKRET_DETAILS_REF_NUMBER_LABEL}
    Element Should Contain              ${GEOKRET_DETAILS_REF_NUMBER}               ${gk.ref}
    Page Should Contain Element         ${GEOKRET_DETAILS_DISTANCE_LABEL}
    Element Should Contain              ${GEOKRET_DETAILS_DISTANCE}                 0
    Page Should Contain Element         ${GEOKRET_DETAILS_CACHES_COUNT_LABEL}
    Element Should Contain              ${GEOKRET_DETAILS_CACHES_COUNT}             0
    Page Should Contain Element         ${GEOKRET_DETAILS_CREATED_ON_DATETIME_LABEL}
    Element Should Contain              ${GEOKRET_DETAILS_CREATED_ON_DATETIME}      ago

    ${_type} =     Get Element Attribute    ${GEOKRET_DETAILS_TYPE_IMG}    attribute=data-gk-type
    Should Be Equal As Strings          ${_type}    ${type}

Has Private Information
    [Arguments]             ${url}      ${gk}
    Go To Url                           ${url}
    Page Should Contain Element         ${GEOKRET_DETAILS_TRACKING_CODE_LABEL}
    Element Should Contain              ${GEOKRET_DETAILS_TRACKING_CODE}            ${gk.tc}
    Textfield Should Contain            ${GEOKRET_DETAILS_FOUND_IT_TRACKING_CODE}   ${gk.tc}

Has Not Private Information
    [Arguments]             ${url}
    Go To Url                           ${url}
    Page Should Not Contain Element     ${GEOKRET_DETAILS_TRACKING_CODE_LABEL}
    Page Should Not Contain Element     ${GEOKRET_DETAILS_TRACKING_CODE}
    Textfield Should Contain            ${GEOKRET_DETAILS_FOUND_IT_TRACKING_CODE}   ${EMPTY}
