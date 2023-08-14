*** Settings ***
Library         RequestsLibrary
Resource        ../ressources/Authentication.robot
Resource        ../ressources/Geokrety.robot
Variables       ../ressources/vars/users.yml
Variables       ../ressources/vars/geokrety.yml
Suite Setup     Suite Setup
Test Setup      Test Setup

*** Test Cases ***

Public Information Visible - anonymous
    Has Public Information Visible - all users

Private Information Not Visible - anonymous
    Has Not Private Information Visible - all users

Public Information Visible - authenticated
    Sign In ${USER_1.name} Fast
    Has Public Information Visible - all users

Private Information Visible - authenticated - himself
    Sign In ${USER_1.name} Fast
    Has Private Information             ${PAGE_USER_1_PROFILE_URL}              ${USER_1}

Private Information Not Visible - authenticated - someone else
    Sign In ${USER_1.name} Fast
    Has Not Private Information             ${PAGE_USER_2_PROFILE_URL}

Access User By Id
    Go To User ${USER_1.id}
    Location Should Be                  ${GK_URL}/en/users/1

Access User By Not An Id
    Go To User ABCDEF
    Location Should Be                  ${PAGE_HOME_URL}
    Flash message shown                 This user does not exist.

    Go To User ${SPACE}
    Location Should Be                  ${PAGE_HOME_URL}
    Flash message shown                 HTTP 404


*** Keywords ***

Suite Setup
    Clear Database
    Seed ${2} users
    Seed ${1} geokrety owned by ${1}
    Seed ${1} geokrety owned by ${2}

Test Setup
    Sign Out Fast

Has Public Information Visible - all users
    Has Public Information              ${PAGE_USER_1_PROFILE_URL}              ${USER_1}
    Has Public Information              ${PAGE_USER_2_PROFILE_URL}              ${USER_2}

Has Not Private Information Visible - all users
    Has Not Private Information          ${PAGE_USER_1_PROFILE_URL}
    Has Not Private Information          ${PAGE_USER_2_PROFILE_URL}

Has Public Information
    [Arguments]             ${url}      ${user}
    Go To Url                           ${url}
    Element Should Contain              ${USER_PROFILE_USERNAME}                ${user.name}
    Page Should Contain Element         ${USER_PROFILE_JOIN_TIME_LABEL}
    Element Should Contain              ${USER_PROFILE_JOIN_TIME}               ago
    Page Should Contain Element         ${USER_PROFILE_LANGUAGE_LABEL}
    Element Should Contain              ${USER_PROFILE_LANGUAGE}                English

Has Private Information
    [Arguments]             ${url}      ${user}
    Go To Url                           ${url}
    Page Should Contain Element         ${USER_PROFILE_EMAIL_LABEL}
    Element Should Contain              ${USER_PROFILE_EMAIL}                   ${user.email}
    Page Should Contain Element         ${USER_PROFILE_SECID_LABEL}
    Textfield Should Not Contain        ${USER_PROFILE_SECID}                   ${EMPTY}
    Page Should Contain Element         ${USER_PROFILE_MINI_MAP_PANEL}
    Page Should Contain Element         ${USER_PROFILE_DANGER_ZONE_PANEL}
    Page Should Contain Element         ${USER_PROFILE_DELETE_ACCOUNT_BUTTON}

Has Not Private Information
    [Arguments]             ${url}
    Go To Url                           ${url}
    Page Should Not Contain Element     ${USER_PROFILE_EMAIL_LABEL}
    Page Should Not Contain Element     ${USER_PROFILE_EMAIL}
    Page Should Not Contain Element     ${USER_PROFILE_SECID_LABEL}
    Page Should Not Contain Element     ${USER_PROFILE_SECID}
    Page Should Not Contain Element     ${USER_PROFILE_MINI_MAP_PANEL}
    Page Should Not Contain Element     ${USER_PROFILE_DANGER_ZONE_PANEL}
    Page Should Not Contain Element     ${USER_PROFILE_DELETE_ACCOUNT_BUTTON}
