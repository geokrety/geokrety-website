*** Settings ***
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/PageEmailChange.robot
Resource        ../vars/users.resource
Resource        ../vars/geokrety.resource
Force Tags      Messaging
Suite Setup     Seed

*** Test Cases ***

Anonymous Cannot Acces Form - via user
    Sign Out Fast
    Go To Url                                       ${PAGE_USER_1_CONTACT_URL}
    Page Should Contain                             Unauthorized

Anonymous Cannot Acces Form - via geokret
    Sign Out Fast
    Go To Url                                       ${PAGE_GEOKRETY_DETAILS_1_CONTACT_OWNER_URL}
    Page Should Contain                             Unauthorized

Authenticated Users Can Access Form
    [Template]    Is Authorized
    ${USER_1.name}
    ${USER_2.name}

Users Without Email Cannot Be Contacted
    [Tags]    TODO
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_USER_3_CONTACT_URL}
    Page Should Contain                             This user has no registered email

Users Without A Validated Email Cannot Be Contacted
    [Tags]    TODO
    Sign In ${USER_3.name} Fast
    Go To User 3 url
    Click Button                                    ${USER_PROFILE_EMAIL_EDIT_BUTTON}
    Wait Until Modal                                Update your email address
    Fill Email Change Form                          ${USER_3.email}    ${FALSE}
    Click Button                                    ${MODAL_DIALOG_SUBMIT_BUTTON}
    Flash message shown                             A confirmation email was sent to your new address.
    Page Should Contain                             You have a pending email validation.

    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_USER_3_CONTACT_URL}
    Page Should Contain                             This user has no registered email



*** Keywords ***

Seed
    Clear DB And Seed 2 users
    Go To Url                                       ${PAGE_SEED_USER}/1?noemail\=true&i\=3
    Seed 1 geokrety owned by ${USER_1.id}
    Sign Out Fast

Is Authorized
    [Arguments]    ${username}=${USER_1.name}
    Sign In ${username} Fast
    Go To Url                                       ${PAGE_USER_1_CONTACT_URL}
    Page Should Not Contain                         Unauthorized
    Wait Until Panel                                Contact user
    Page Should Contain Element                     ${USER_CONTACT_SUBJECT_INPUT}
    Page Should Contain Element                     ${USER_CONTACT_MESSAGE_INPUT}
    Element Should Contain                          ${USER_CONTACT_USER_STATIC}             ${USER_1.name}
    Element Should Contain                          ${USER_CONTACT_USER_STATIC}             English
