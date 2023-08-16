*** Settings ***
Resource        ../ressources/Authentication.robot
Resource        ../ressources/Users.robot
Variables       ../ressources/vars/users.yml
Suite Setup     Suite Setup

*** Variables ***

${SUBJECT}     HELLO!
${MESSAGE}     🎉 Welcome to GeoKrety.org!

*** Test Cases ***

Send Message Via User Contact
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_USER_CONTACT_URL}            userid=${USER_2.id}
    Input Text                                      ${USER_CONTACT_SUBJECT_INPUT}       ${SUBJECT}
    Input Text                                      ${USER_CONTACT_MESSAGE_INPUT}       ${MESSAGE}
    Click Button                                    ${MODAL_PANEL_SUBMIT_BUTTON}

    Go To Url                                       ${PAGE_DEV_MAILBOX_URL}
    Element Should Contain                          ${DEV_MAILBOX_FIRST_MAIL_LINK}      💌 Contact from user ${USER_1.name}

    Go To Url                                       ${PAGE_DEV_MAILBOX_FIRST_MAIL_URL}
    Page Should Contain                             Contact from a GeoKrety.org user
    Page Should Contain                             Hi ${USER_2.name}
    Page Should Contain                             This email was sent by user ${USER_1.name}
    Page Should Contain                             Subject: ${SUBJECT}
    Page Should Contain                             ${MESSAGE}
    Page Should Contain                             Reply to ${USER_1.name}
    Page Should Contain Link                        ${PAGE_USER_1_CONTACT_URL}

Sending From GeoKret Fill The Subject
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_GEOKRETY_DETAILS_1_CONTACT_OWNER_URL}    userid=${USER_2.id}
    Textfield Value Should Be                       ${USER_CONTACT_SUBJECT_INPUT}                   GeoKret: ${GEOKRETY_1.name} (${GEOKRETY_1.ref})


*** Keywords ***

Suite Setup
    Clear Database And Seed ${2} users
    Seed ${1} geokrety owned by ${2}
    Sign Out Fast

Is Authorized
    [Arguments]    ${username}=${USER_1.name}
    Sign In ${username} Fast
    Go To Url                                       ${PAGE_GEOKRETY_DETAILS_1_CONTACT_OWNER_URL}
    Page Should Not Contain                         ${UNAUTHORIZED}
    Wait Until Panel                                Contact user
    Page Should Contain Element                     ${USER_CONTACT_SUBJECT_INPUT}
    Page Should Contain Element                     ${USER_CONTACT_MESSAGE_INPUT}
