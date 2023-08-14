*** Settings ***
Documentation     Website URLS
Resource          ../CustomActions.robot
Library           SeleniumLibrary  timeout=10  implicit_wait=0
Library           ../libraries/ReplaceVariablesAsUrl.py

*** Variables ***
${NO_REDIRECT_CHECK}    NO_REDIRECT_CHECK

${GK_URL}               http://localhost:3001

# LANGUAGE PAGES
${PAGE_HOME_URL}                        ${GK_URL}
${PAGE_HOME_URL_EN}                     ${PAGE_HOME_URL}/en
${PAGE_HOME_URL_FR}                     ${PAGE_HOME_URL}/fr

# AUTH
${PAGE_REGISTER_URL}                    ${PAGE_HOME_URL_EN}/registration
${PAGE_SIGN_IN_URL}                     ${PAGE_HOME_URL_EN}/login
${PAGE_SIGN_OUT_URL}                    ${PAGE_HOME_URL_EN}/logout

# USER
${PAGE_USER_1_PROFILE_URL}              ${PAGE_HOME_URL_EN}/users/1
${PAGE_USER_2_PROFILE_URL}              ${PAGE_HOME_URL_EN}/users/2
${PAGE_USER_1_PROFILE_URL_FR}           ${PAGE_HOME_URL_FR}/users/1
${PAGE_USER_X_PROFILE_URL}              ${PAGE_HOME_URL_EN}/users/\${params.userid}

# NEWS
${PAGE_NEWS_LIST_URL}                   ${PAGE_HOME_URL_EN}/news
${PAGE_NEWS_URL}                        ${PAGE_HOME_URL_EN}/news/\${params.newsid}

# MOVES
${PAGE_MOVES_URL}                       ${PAGE_HOME_URL_EN}/moves
${PAGE_MOVES_FROM_INVENTORY_URL}        ${PAGE_HOME_URL_EN}/moves/select-from-inventory
${PAGE_MOVES_EDIT_URL}                  ${PAGE_HOME_URL_EN}/moves/\${params.moveid}/edit
${PAGE_MOVES_COMMENT_URL}               ${PAGE_HOME_URL_EN}/moves/\${params.moveid}/comment
${PAGE_MOVES_COMMENT_MISSING_URL}       ${PAGE_HOME_URL_EN}/moves/\${params.moveid}/missing
${PAGE_MOVES_DELETE_URL}                ${PAGE_HOME_URL_EN}/moves/\${params.moveid}/delete

# STATIC
${PAGE_TERMS_OF_USE_URL}                ${PAGE_HOME_URL_EN}/terms-of-use

# GEOKRETY
${PAGE_GEOKRETY_CREATE_URL}                     ${PAGE_HOME_URL_EN}/geokrety/create
${PAGE_GEOKRETY_EDIT_URL}                       ${PAGE_HOME_URL_EN}/geokrety/1/edit
${PAGE_GEOKRETY_CLAIM_URL}                      ${PAGE_HOME_URL_EN}/geokrety/claim
${PAGE_GEOKRETY_WATCH_URL}                      ${PAGE_HOME_URL_EN}/geokrety/\${params.gkid}/watch
${PAGE_GEOKRETY_UNWATCH_URL}                    ${PAGE_HOME_URL_EN}/geokrety/\${params.gkid}/unwatch
${PAGE_GEOKRETY_WATCHERS_URL}                   ${PAGE_HOME_URL_EN}/geokrety/\${params.gkid}/watchers
${PAGE_GEOKRETY_DETAILS_URL}                    ${PAGE_HOME_URL_EN}/geokrety/\${params.gkid}
${PAGE_GEOKRETY_X_DETAILS_URL}                  ${PAGE_HOME_URL_EN}/geokrety/\${params.gkid}
${PAGE_GEOKRETY_1_DETAILS_URL}                  ${PAGE_HOME_URL_EN}/geokrety/GK0001
${PAGE_GEOKRETY_2_DETAILS_URL}                  ${PAGE_HOME_URL_EN}/geokrety/GK0002
${PAGE_GEOKRETY_3_DETAILS_URL}                  ${PAGE_HOME_URL_EN}/geokrety/GK0003
${PAGE_GEOKRETY_LABEL_URL}                      ${PAGE_HOME_URL_EN}/geokrety/\${params.gkid}/label
${PAGE_GEOKRETY_DETAILS_CONTACT_OWNER_URL}      ${PAGE_HOME_URL_EN}/geokrety/\${params.gkid}/contact-owner
${PAGE_GEOKRETY_DETAILS_1_CONTACT_OWNER_URL}    ${PAGE_HOME_URL_EN}/geokrety/GK0001/contact-owner
${PAGE_GEOKRETY_DETAILS_2_CONTACT_OWNER_URL}    ${PAGE_HOME_URL_EN}/geokrety/GK0002/contact-owner
${PAGE_GEOKRETY_DETAILS_3_CONTACT_OWNER_URL}    ${PAGE_HOME_URL_EN}/geokrety/GK0003/contact-owner

*** Keywords ***

Go To Url
    [Arguments]    ${url}    ${redirect}=${EMPTY}    &{params}
    ${url_} =                  Replace Variables    ${url}
    ${url_} =                  Requote Uri          ${url_}
    Go To                      ${url_}
    Page WithoutWarningOrFailure
    Run Keyword If    "${redirect}" == "${EMPTY}"
    ...             Location Should Be    ${url_}
    ...  ELSE IF    "${redirect}" == "${NO_REDIRECT_CHECK}"
    ...             No Operation
    ...  ELSE
    ...             Location Should Match Regexp    ${redirect}

Location Should Not Be
    [Arguments]    ${url}
    Run Keyword And Expect Error    Location should have been '${url}' but was *    Location Should Be    ${url}

# Location With Param Should Be
#     [Arguments]    ${url}    &{params}
#     ${url_} =       Replace Variables    ${url}
#     Location Should Be    ${url_}

Location Should Match Regexp
    [Arguments]    ${regex}
    ${url} =                   Get Location
    Should Match Regexp        ${url}        ${regex}


# Shortcut to common pages
Go To Home
    Go To Url                  ${GK_URL}    ${PAGE_HOME_URL_EN}

Go To User ${userid}
    [Arguments]    ${redirect}=${EMPTY}
    Go To Url                  ${PAGE_USER_X_PROFILE_URL}    userid=${userid}    redirect=${redirect}

Go To GeoKrety ${gkid}
    [Arguments]    ${redirect}=${EMPTY}
    Go To Url                  ${PAGE_GEOKRETY_X_DETAILS_URL}    gkid=${gkid}    redirect=${redirect}
