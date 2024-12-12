*** Settings ***
Resource        ../ressources/Authentication.robot
Resource        ../ressources/Users.robot
Variables       ../ressources/vars/users.yml
Suite Setup     Suite Setup

*** Variables ***

${SUBJECT}     HELLO!
${MESSAGE}     🎉 Welcome to GeoKrety.org!
&{MESSAGE_2}    subject=Subject    message=The message

*** Test Cases ***

Send Message Via User Contact Fresh Users
    Sign In ${USER_3.name} Fast
    Go To Url                                       ${PAGE_USER_CONTACT_URL}            userid=${USER_1.id}    redirect=${PAGE_HOME_URL_EN}


Send Message Via User Contact Fresh Users Post
    Sign In ${USER_3.name} Fast
    Go To Url                                       ${PAGE_USER_1_CONTACT_URL}    redirect=${PAGE_HOME_URL_EN}

    Create Session    gk                            ${GK_URL}
    ${auth} =         GET On Session     gk         /devel/users/${USER_3.name}/login
    ${resp} =         POST On Session    gk         url=${PAGE_USER_1_CONTACT_URL}?skip_csrf=True
    ...                                             data=&{MESSAGE_2}
    ${url} =         Convert To String   ${resp.url}
    Should Be Equal As Strings                      ${url}    ${PAGE_HOME_URL_EN}
    Delete All Sessions

Send Message Via User Contact
    Sign In ${USER_2.name} Fast
    Go To Url                                       ${PAGE_USER_CONTACT_URL}            userid=${USER_1.id}
    Input Text                                      ${USER_CONTACT_SUBJECT_INPUT}       ${SUBJECT}
    Input Text                                      ${USER_CONTACT_MESSAGE_INPUT}       ${MESSAGE}
    Click Button                                    ${MODAL_PANEL_SUBMIT_BUTTON}
    Page Should Contain                             Your message to ${USER_1.name} has been sent

    Go To Url                                       ${PAGE_DEV_MAILBOX_URL}
    Element Should Contain                          ${DEV_MAILBOX_FIRST_MAIL_LINK}      💌 Contact from user ${USER_2.name}

    Go To Url                                       ${PAGE_DEV_MAILBOX_FIRST_MAIL_URL}
    Page Should Contain                             Contact from a GeoKrety.org user
    Page Should Contain                             Hi ${USER_1.name}
    Page Should Contain                             This email was sent by user ${USER_2.name}
    Page Should Contain                             Subject: ${SUBJECT}
    Page Should Contain                             ${MESSAGE}
    Page Should Contain                             Reply to ${USER_2.name}
    Page Should Contain Link                        ${PAGE_USER_2_CONTACT_URL}

Sending From GeoKret Fill The Subject
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_GEOKRETY_DETAILS_1_CONTACT_OWNER_URL}    userid=${USER_2.id}
    Textfield Value Should Be                       ${USER_CONTACT_SUBJECT_INPUT}                   GeoKret: ${GEOKRETY_1.name} (${GEOKRETY_1.ref})


*** Keywords ***

Suite Setup
    Clear Database And Seed ${1} users
    Seed ${1} users with joined_days_ago ${10}    start_at=2
    Seed ${1} users with joined_days_ago ${1}     start_at=3

    Seed ${1} geokrety owned by ${1}
    Sign Out Fast
