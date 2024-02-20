*** Settings ***
Documentation     Website URLS
Resource          ../CustomActions.robot
Library           SeleniumLibrary  timeout=10  implicit_wait=0
Library           ../libraries/ReplaceVariablesAsUrl.py

*** Variables ***
${NO_REDIRECT_CHECK}    NO_REDIRECT_CHECK

${GK_URL}               http://localhost:3001

# LANGUAGE PAGES
${PAGE_HOME_URL}                            ${GK_URL}
${PAGE_HOME_URL_EN}                         ${PAGE_HOME_URL}/en
${PAGE_HOME_URL_FR}                         ${PAGE_HOME_URL}/fr

# AUTH
${PAGE_REGISTER_URL}                        ${PAGE_HOME_URL_EN}/registration
${PAGE_SIGN_IN_URL}                         ${PAGE_HOME_URL_EN}/login
${PAGE_SIGN_OUT_URL}                        ${PAGE_HOME_URL_EN}/logout

# USER
${PAGE_USER_PROFILE_BASE_URL}               ${PAGE_HOME_URL_EN}/users/
${PAGE_USER_1_PROFILE_URL}                  ${PAGE_HOME_URL_EN}/users/1
${PAGE_USER_2_PROFILE_URL}                  ${PAGE_HOME_URL_EN}/users/2
${PAGE_USER_3_PROFILE_URL}                  ${PAGE_HOME_URL_EN}/users/3
${PAGE_USER_1_PROFILE_URL_FR}               ${PAGE_HOME_URL_FR}/users/1
${PAGE_USER_X_PROFILE_URL}                  ${PAGE_HOME_URL_EN}/users/\${params.userid}

${PAGE_USER_1_BANER_TEMPLATE_URL}           ${GK_URL}/en/users/choose-statpic-template
${PAGE_USER_1_OBSERVATION_AREA_URL}         ${GK_URL}/en/users/observation-area
${PAGE_USER_1_OBSERVATION_AREA_URL_FR}      ${GK_URL}/fr/users/observation-area
${PAGE_USER_CHANGE_PASSWORD_URL}            ${GK_URL}/en/user/update-password
${PAGE_USER_CHANGE_LANGUAGE_URL}            ${GK_URL}/en/user/preferred-language
${PAGE_USER_REFRESH_SECID_URL}              ${GK_URL}/en/user/refresh-secid
${PAGE_USER_CHANGE_EMAIL_URL}               ${GK_URL}/en/user/email/update
${PAGE_USER_CHANGE_USERNAME_URL}            ${GK_URL}/en/user/username/update
${PAGE_USER_DELETE_ACCOUNT_URL}             ${GK_URL}/en/user/delete
${PAGE_USER_AUTHENTICATION_HISTORY_URL}     ${GK_URL}/en/user/authentication-history

${PAGE_USER_CONTACT_URL}                ${GK_URL}/en/users/\${params.userid}/contact
${PAGE_USER_1_CONTACT_URL}              ${GK_URL}/en/users/1/contact
${PAGE_USER_2_CONTACT_URL}              ${GK_URL}/en/users/2/contact
${PAGE_USER_3_CONTACT_URL}              ${GK_URL}/en/users/3/contact

${PAGE_USER_RECENT_MOVES_URL}           ${GK_URL}/en/users/\${params.userid}/recent-moves
${PAGE_USER_INVENTORY_URL}              ${GK_URL}/en/users/\${params.userid}/inventory
${PAGE_USER_WATCHED_GEOKRETY_URL}       ${GK_URL}/en/users/\${params.userid}/watched-geokrety
${PAGE_USER_OWNED_GEOKRETY_URL}         ${GK_URL}/en/users/\${params.userid}/owned-geokrety
${PAGE_USER_OWNED_GEOKRETY_RECENT_MOVES_URL}    ${GK_URL}/en/users/\${params.userid}/owned/recent-moves
${PAGE_USER_POSTED_PICTURES_URL}                ${GK_URL}/en/users/\${params.userid}/pictures
${PAGE_USER_OWNED_GEOKRETY_PICTURES_URL}        ${GK_URL}/en/users/\${params.userid}/owned/pictures

# NEWS
${PAGE_NEWS_LIST_URL}                       ${PAGE_HOME_URL_EN}/news
${PAGE_NEWS_URL}                            ${PAGE_HOME_URL_EN}/news/\${params.newsid}

# MOVES
${PAGE_MOVES_URL}                           ${PAGE_HOME_URL_EN}/moves
${PAGE_MOVES_FROM_INVENTORY_URL}            ${PAGE_HOME_URL_EN}/moves/select-from-inventory
${PAGE_MOVES_EDIT_URL}                      ${PAGE_HOME_URL_EN}/moves/\${params.moveid}/edit
${PAGE_MOVES_COMMENT_URL}                   ${PAGE_HOME_URL_EN}/moves/\${params.moveid}/comment
${PAGE_MOVES_COMMENT_MISSING_URL}           ${PAGE_HOME_URL_EN}/moves/\${params.moveid}/missing
${PAGE_MOVES_DELETE_URL}                    ${PAGE_HOME_URL_EN}/moves/\${params.moveid}/delete

# STATIC
${PAGE_TERMS_OF_USE_URL}                          ${PAGE_HOME_URL_EN}/terms-of-use
${PAGE_PASSWORD_RECOVERY_URL}                     ${PAGE_HOME_URL_EN}/password-recovery
${PAGE_PASSWORD_RECOVERY_RESET_PASSWORD_URL}      ${PAGE_HOME_URL_EN}/recover-password/
${PAGE_PASSWORD_RECOVERY_ACTIVATE_URL}            ${GK_URL}\/en\/recover-password\/[^\/]+
${PAGE_PICTURES_GALLERY_URL}                      ${GK_URL}/en/picture/gallery

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

# SEARCH
${PAGE_ADVANCED_SEARCH_URL}             ${GK_URL}/en/search/advanced
${PAGE_SEARCH_BY_WAYPOINT_URL}          ${GK_URL}/en/search/waypoint/\${params.waypoint}
${PAGE_SEARCH_BY_USERNAME_URL}          ${GK_URL}/en/search/user/\${params.username}
${PAGE_SEARCH_BY_GEOKRETY_URL}          ${GK_URL}/en/search/geokret/\${params.geokret}

# LEGACY BRIDGES
${PAGE_LEGACY_INDEX_URL}                ${GK_URL}/index.php
${PAGE_LEGACY_REGISTRATION_URL}         ${GK_URL}/adduser.php
${PAGE_LEGACY_LONGIN_URL}               ${GK_URL}/longin.php
${PAGE_LEGACY_SEARCH_BY_WAYPOINT_URL}   ${GK_URL}/szukaj.php
${PAGE_LEGACY_GKT_INVENTORY_URL}        ${GK_URL}/gkt/v3/inventory
${PAGE_LEGACY_GKT_SEARCH_URL}           ${GK_URL}/gkt/v3/search
${PAGE_LEGACY_API_EXPORT_URL}           ${GK_URL}/api/v1/export
${PAGE_LEGACY_API_EXPORT2_URL}          ${GK_URL}/api/v1/export2
${PAGE_LEGACY_API_EXPORT_OC_URL}        ${GK_URL}/api/v1/export_oc

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

Go To Url Fast
    [Arguments]    ${url}    ${redirect}=${EMPTY}    &{params}
    ${url_} =                  Replace Variables    ${url}
    ${url_} =                  Requote Uri          ${url_}

    ${resp} =    GET                       ${url_}
    ${body} =    Convert To String         ${resp.content}
    Variable Without Warning Or Failure    ${body}

Get Url With Param
    [Arguments]    ${url}    &{params}
    ${url_} =                  Replace Variables    ${url}
    ${url_} =                  Requote Uri          ${url_}
    RETURN    ${url_}

Location Should Not Be
    [Arguments]    ${url}
    Run Keyword And Expect Error    Location should have been '${url}' but was *    Location Should Be    ${url}

Location With Param Should Be
    [Arguments]    ${url}    &{params}
    ${url_} =                  Replace Variables    ${url}
    ${url_} =                  Requote Uri          ${url_}
    Location Should Be    ${url_}

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

# Shortcut to common pages
Go To Move
    Go To Url                  ${PAGE_MOVES_URL}
    Page Should Contain                     Identify GeoKret
