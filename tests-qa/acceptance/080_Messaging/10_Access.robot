*** Settings ***
Resource        ../ressources/Authentication.robot
Resource        ../ressources/Users.robot
Variables       ../ressources/vars/users.yml
Test Setup      Test Setup

*** Variables ***

&{MESSAGE}    subject=Subject    message=The message

*** Test Cases ***

Anonymous Cannot Acces Form - via user
    Sign Out Fast
    Go To Url                                       ${PAGE_USER_1_CONTACT_URL}    redirect=${PAGE_SIGN_IN_URL}
    Page Should Contain                             ${UNAUTHORIZED}

Anonymous Cannot Acces Form - via geokret
    Sign Out Fast
    Go To Url                                       ${PAGE_GEOKRETY_DETAILS_1_CONTACT_OWNER_URL}    redirect=${PAGE_SIGN_IN_URL}
    Page Should Contain                             ${UNAUTHORIZED}

Authenticated Users Can Access Form
    [Template]    Is Authorized
    ${USER_1.name}
    ${USER_2.name}

Users Without Email Cannot Be Contacted
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_USER_3_CONTACT_URL}    redirect=${PAGE_USER_3_PROFILE_URL}
    Page Should Contain                             This user has no valid email

    Create Session    gk                            ${GK_URL}
    ${auth} =         GET On Session     gk         /devel/users/${USER_1.name}/login
    ${resp} =         POST On Session    gk         url=${PAGE_USER_3_CONTACT_URL}?skip_csrf=True
    ...                                             data=&{MESSAGE}
    ${body} =         Convert To String   ${resp.content}
    Should Contain                                  ${body}    This user has no valid email
    Delete All Sessions

Users Need A valid Email To Contact Others
    Sign In ${USER_3.name} Fast
    Go To Url                                       ${PAGE_USER_1_CONTACT_URL}    redirect=${PAGE_USER_1_PROFILE_URL}
    Page Should Contain                             Your email address must be validated before you can contact other players.

    Create Session    gk                            ${GK_URL}
    ${auth} =         GET On Session     gk         /devel/users/${USER_3.name}/login
    ${resp} =         POST On Session    gk         url=${PAGE_USER_1_CONTACT_URL}?skip_csrf=True
    ...                                             data=&{MESSAGE}
    ${body} =         Convert To String   ${resp.content}
    Should Contain                                  ${body}    Your email address must be validated before you can contact other players
    Delete All Sessions

Users Without A Validated Email Cannot Be Contacted
    Sign In ${USER_3.name} Fast
    Go To User ${USER_3.id}
    Click Button                                    ${USER_PROFILE_EMAIL_EDIT_BUTTON}
    Wait Until Modal                                Update your email address
    Fill Email Change Form                          foo+${USER_3.email}    ${FALSE}
    Click Button                                    ${MODAL_DIALOG_SUBMIT_BUTTON}
    Flash message shown                             A confirmation email was sent to your new address.
    Page Should Contain                             You have a pending email validation.

    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_USER_3_CONTACT_URL}    redirect=${PAGE_USER_3_PROFILE_URL}
    Page Should Contain                             This user has no valid email

Access By Not An Id
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_USER_CONTACT_URL}    userid=FOOBAR    redirect=${PAGE_HOME_URL_EN}
    Flash message shown                             This user does not exist.

Access By GeoKrety Not An Id
    Sign In ${USER_1.name} Fast
    Go To Url                                       ${PAGE_GEOKRETY_DETAILS_CONTACT_OWNER_URL}    gkid=FOOBAR    redirect=${PAGE_HOME_URL}
    Flash message shown                             This GeoKret does not exist.


*** Keywords ***

Test Setup
    Clear Database And Seed ${2} users
    Seed ${1} users with email status ${1}    start_at=3
    Seed ${1} geokrety owned by ${1}
    Sign Out Fast

Is Authorized
    [Arguments]    ${username}=${USER_1.name}
    Sign In ${username} Fast
    Go To Url                                       ${PAGE_USER_1_CONTACT_URL}
    Page Should Not Contain                         ${UNAUTHORIZED}
    Wait Until Panel                                Contact user
    Page Should Contain Element                     ${USER_CONTACT_SUBJECT_INPUT}
    Page Should Contain Element                     ${USER_CONTACT_MESSAGE_INPUT}
    Element Should Contain                          ${USER_CONTACT_USER_STATIC}             ${USER_1.name}
    Element Should Contain                          ${USER_CONTACT_USER_STATIC}             English
