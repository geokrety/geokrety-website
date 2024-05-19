*** Settings ***
Resource        Database.robot
Library         libraries/Browser.py  timeout=10  implicit_wait=0

*** Variables ***

${PAGE_SEED_USER}                       ${GK_URL}/devel/db/users/seed/\${params.count}
${PAGE_SEED_GEOKRETY}                   ${GK_URL}/devel/db/geokrety/seed
${PAGE_SEED_GEOKRETY_OWNED_BY_USER_1}   ${GK_URL}/devel/db/users/1/geokrety/seed
${PAGE_SEED_GEOKRETY_OWNED_BY_USER_2}   ${GK_URL}/devel/db/users/2/geokrety/seed
${PAGE_SEED_WAYPOINT_OC}                ${GK_URL}/devel/db/waypoint/oc
${PAGE_SEED_WAYPOINT_GC}                ${GK_URL}/devel/db/waypoint/gc
${PAGE_SEED_NEWS}                       ${GK_URL}/devel/db/news/seed
${PAGE_SEED_PICTURE_AVATAR}             ${GK_URL}/devel/db/users/1/avatar/1
${PAGE_SEED_OWNERCODE}                  ${GK_URL}/devel/db/ownercode/geokrety/\${params.geokretid}/ownercode/\${params.ownercode}/seed

${PAGE_CSS_ASSET_CLEAR_URL}             ${GK_URL}/devel/css/assets/clear

${PAGE_DEV_MAILBOX_URL}                 ${GK_URL}/devel/mail
${PAGE_DEV_MAILBOX_X_MAIL_URL}          ${PAGE_DEV_MAILBOX_URL}/\${params.number}
${PAGE_DEV_MAILBOX_FIRST_MAIL_URL}      ${PAGE_DEV_MAILBOX_URL}/0
${PAGE_DEV_MAILBOX_SECOND_MAIL_URL}     ${PAGE_DEV_MAILBOX_URL}/1
${PAGE_DEV_MAILBOX_THIRD_MAIL_URL}      ${PAGE_DEV_MAILBOX_URL}/2
${PAGE_DEV_MAILBOX_FOURTH_MAIL_URL}     ${PAGE_DEV_MAILBOX_URL}/3
${PAGE_DEV_MAILBOX_CLEAR_URL}           ${PAGE_DEV_MAILBOX_URL}/delete/all

${PAGE_DEV_RESET_DB_URL}                ${GK_URL}/devel/db/reset

################
# NAVBAR
################
${NAVBAR_DEV_MAILBOX_LINK}                  //*[@id="navbar-localmail"]
${NAVBAR_DEV_MAILBOX_COUNTER}               //*[@id="navbar-localmail"]/span[@class="badge"]

################
# DEV_MAILBOX
################
${DEV_MAILBOX_DELETE_ALL_MAILS_BUTTON}      //*[@id="deleteAllMailsButton"]
${DEV_MAILBOX_MAILS_TABLE_ROWS}             //*[@id="mailsTable"]/tbody/tr
${DEV_MAILBOX_FIRST_MAIL_LINK}              //*[@id="mailsTable"]/tbody/tr/td[@class="mail_id" and text()="0"]/parent::tr//a[contains(@class, "displayMailLink")]
${DEV_MAILBOX_SECOND_MAIL_LINK}             //*[@id="mailsTable"]/tbody/tr/td[@class="mail_id" and text()="1"]/parent::tr//a[contains(@class, "displayMailLink")]
${DEV_MAILBOX_FIRST_MAIL_DELETE_LINK}       //*[@id="mailsTable"]/tbody/tr/td[@class="mail_id" and text()="0"]/parent::tr//a[contains(@class, "deleteMailLink")]
${DEV_MAILBOX_SECOND_MAIL_DELETE_LINK}      //*[@id="mailsTable"]/tbody/tr/td[@class="mail_id" and text()="1"]/parent::tr//a[contains(@class, "deleteMailLink")]

*** Keywords ***


# Shortcut to common pages

Clear css assets
    Go To Url Fast                      ${PAGE_CSS_ASSET_CLEAR_URL}

Clear Database And Seed ${count} users
    Clear Database
    Seed ${count} users

Empty Dev Mailbox
    Go To Url                           ${PAGE_DEV_MAILBOX_URL}
    Click Element                       ${DEV_MAILBOX_DELETE_ALL_MAILS_BUTTON}
    Mailbox Should Contain 0 Messages

Empty Dev Mailbox Fast
    ${resp} =           Go To Url Fast With Current Session    ${PAGE_DEV_MAILBOX_CLEAR_URL}/fast
    ${body} =           Convert To String     ${resp.content}
    Should Contain      ${body}    OK

    # Go To Url                           ${PAGE_DEV_MAILBOX_CLEAR_URL}/fast
    # Page Should Contain                 OK

Mailbox Should Contain ${count} Messages
    Go To Url In New Tab                    ${PAGE_DEV_MAILBOX_URL}
    Element Text Should Be                  ${NAVBAR_DEV_MAILBOX_COUNTER}     ${count}
    ${rowCount} =     Get Element Count     ${DEV_MAILBOX_MAILS_TABLE_ROWS}
    Should Be Equal As Integers             ${count}    ${rowCount}
    Close Tab

Mailbox Message ${number} Subject Should Contain ${message}
    Go To Url                           ${PAGE_DEV_MAILBOX_URL}
    Element Should Contain              //*[@id="mailsTable"]/tbody/tr/td[@class="mail_id" and text()="${number - 1}"]/parent::tr//a[contains(@class, "displayMailLink")]    ${message}

Mailbox Open Message ${number}
    Go To Url                           ${PAGE_DEV_MAILBOX_X_MAIL_URL}    number=${number - 1}

# Delete First Mail in Mailbox
#     Go To Url                         ${PAGE_DEV_MAILBOX_URL}
#     Click Element                     ${DEV_MAILBOX_FIRST_MAIL_DELETE_LINK}

# Delete Second Mail in Mailbox
#     Go To Url                         ${PAGE_DEV_MAILBOX_URL}
#     Click Element                     ${DEV_MAILBOX_SECOND_MAIL_DELETE_LINK}

Delete Mail ${mail_id} in Mailbox
    Go To Url                           ${PAGE_DEV_MAILBOX_URL}
    Click Element                       //*[@id="mailsTable"]/tbody/tr/td[@class="mail_id" and text()="${mail_id}"]/parent::tr//a[contains(@class, "deleteMailLink")]

Seed ${count} users
    Go To Url Fast                      ${PAGE_SEED_USER}?    count=${count}

    # ${resp} =    GET                    ${GK_URL}/devel/db/users/seed/${count}
    # ${body} =    Convert To String      ${resp.content}
    # Should Contain                      ${body}    done!

    # Go To Url                           ${PAGE_SEED_USER}    count=${count}
    # Page Should Contain                 done!
    Empty Dev Mailbox Fast

Seed ${count} users without password
    Go To Url Fast                      ${PAGE_SEED_USER}?without_password\=true    count=${count}
    Empty Dev Mailbox Fast

Seed ${count} users without email
    [Arguments]    ${start_at}=1
    Go To Url Fast                      ${PAGE_SEED_USER}?noemail\=true&i\=${start_at}    count=${count}
    Empty Dev Mailbox Fast

Seed ${count} users without terms of use
    Go To Url Fast                      ${PAGE_SEED_USER}/no-terms-of-use    count=${count}
    Empty Dev Mailbox Fast

Seed ${count} users with status ${status}
    [Arguments]    ${start_at}=1
    Go To Url Fast                      ${PAGE_SEED_USER}/status/${status}?i\=${start_at}    count=${count}
    Empty Dev Mailbox Fast

Seed ${count} users with email status ${email_status}
    [Arguments]    ${start_at}=1
    Go To Url Fast                      ${PAGE_SEED_USER}?email_invalid\=${email_status}&i\=${start_at}    count=${count}
    Empty Dev Mailbox Fast

Seed ${count} users without terms of use with status ${status}
    Go To Url Fast                      ${PAGE_SEED_USER}/status/${status}/no-terms-of-use    count=${count}
    Empty Dev Mailbox Fast

Seed ${count} users with social_auth_provider_id ${social_auth_provider_id}
    Go To Url Fast                      ${PAGE_SEED_USER}?social_auth_provider_id\=${social_auth_provider_id}    count=${count}
    Empty Dev Mailbox Fast

Seed ${count} users without password with social_auth_provider_id ${social_auth_provider_id}
    Go To Url Fast                      ${PAGE_SEED_USER}?without_password\=true&social_auth_provider_id\=${social_auth_provider_id}    count=${count}
    Empty Dev Mailbox Fast

Seed ${count} geokrety
    Go To Url Fast                      ${PAGE_SEED_GEOKRETY}/${count}

Seed ${count} geokrety owned by ${userid}
    Go To Url Fast                      ${GK_URL}/devel/db/users/${userid}/geokrety/seed/${count}

Seed ${count} geokrety owned by ${userid} with mission ${mission}
    Go To Url Fast                      url=${GK_URL}/devel/db/users/${userid}/geokrety/seed/${count}?mission=${mission}

Seed special geokrety with tracking code starting with GK owned by ${userid}
    Go To Url Fast                      ${GK_URL}/devel/db/users/${userid}/geokrety/tc-starting-with-gk

Seed owner code ${ownercode} for geokret ${geokretid}
    Go To Url Fast                      ${PAGE_SEED_OWNERCODE}    geokretid=${geokretid}    ownercode=${ownercode}

Seed ${count} waypoints OC
    Go To Url Fast                      ${PAGE_SEED_WAYPOINT_OC}/${count}

Seed ${count} waypoints GC
    Go To Url Fast                      ${PAGE_SEED_WAYPOINT_GC}/${count}
