*** Settings ***
Library         String
Resource        ../ressources/Authentication.robot
Resource        ../ressources/vars/Urls.robot
Variables       ../ressources/vars/users.yml
Suite Setup     Suite Setup

*** Variables ***

${PERCENT} =          %25

*** Test Cases ***

Users Should Be Shown
    Go To Url                               ${PAGE_SEARCH_BY_USERNAME_URL}              username=${USER_1.name}
    Element Count Should Be                 ${SEARCH_BY_USER_TABLE}/tbody/tr        1
    Check Search By User                    ${1}    ${USER_1}

Case Insensitive Match
    ${username_uppercase} =                 Convert To Uppercase                        ${USER_1.name}
    Go To Url                               ${PAGE_SEARCH_BY_USERNAME_URL}              username=${username_uppercase}
    Element Count Should Be                 ${SEARCH_BY_USER_TABLE}/tbody/tr        1
    Check Search By User                    ${1}    ${USER_1}

Wildcard Search
    Go To Url                               ${PAGE_SEARCH_BY_USERNAME_URL}              username=user${PERCENT}
    Element Count Should Be                 ${SEARCH_BY_USER_TABLE}/tbody/tr        2
    Check Search By User                    ${1}    ${USER_1}
    Check Search By User                    ${2}    ${USER_2}

    Go To Url                               ${PAGE_SEARCH_BY_USERNAME_URL}              username=${PERCENT}
    Element Count Should Be                 ${SEARCH_BY_USER_TABLE}/tbody/tr        2
    Check Search By User                    ${1}    ${USER_1}
    Check Search By User                    ${2}    ${USER_2}

    Go To Url                               ${PAGE_SEARCH_BY_USERNAME_URL}              username=${PERCENT}name 2
    Element Count Should Be                 ${SEARCH_BY_USER_TABLE}/tbody/tr        1
    Check Search By User                    ${1}    ${USER_2}

Unexistent User
    Go To Url                               ${PAGE_SEARCH_BY_USERNAME_URL}              username=idontexist
    Element Count Should Be                 ${SEARCH_BY_USER_TABLE}/tbody/tr        0
    Page Should Contain                     No users matching: idontexist

Empty Request
    Go To Url                               ${PAGE_SEARCH_BY_USERNAME_URL}              username=${EMPTY}    redirect=${PAGE_ADVANCED_SEARCH_URL}

*** Keywords ***

Suite Setup
    Clear Database And Seed ${2} users
    Seed ${2} geokrety owned by ${2}
    Sign Out Fast

Check Search By User
    [Arguments]    ${row}    ${user}
    Table Cell Should Contain               ${SEARCH_BY_USER_TABLE}    ${row + 1}    1    ${user.name}
