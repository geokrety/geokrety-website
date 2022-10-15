*** Settings ***
Library         RequestsLibrary
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/PagePasswordChange.robot
Resource        ../vars/users.resource
Force Tags      Users Details    Security
Test Setup      Clean

*** Variables ***

&{password_equal}    password_old=password    password_new=mypassword    password_new_confirm=mypassword

&{password_not_equal}    password_old=password    password_new=mypassword    password_new_confirm=bad

&{password_no_old_equal}    password_new=mypassword    password_new_confirm=mypassword

&{password_no_old_not_equal}    password_new=mypassword    password_new_confirm=badpassword

*** Test Cases ***

Field old password visibility - user having a password
    Seed ${1} users
    Sign In ${USER_1.name} Fast
    Go To User 1 url
    Click Link                              ${USER_PROFILE_PASSWORD_EDIT_BUTTON}
    Wait Until Modal                        Change your password
    Page Should Contain Element             ${USER_PASSWORD_OLD_INPUT}

Field old password visibility - user not having a password
    Seed ${1} users without password
    Sign In ${USER_1.name} Fast
    Go To User 1 url
    Click Link                              ${USER_PROFILE_PASSWORD_EDIT_BUTTON}
    Wait Until Modal                        Change your password
    Page Should Not Contain Element         ${USER_PASSWORD_OLD_INPUT}

Old password check - user not having a password
    Seed ${1} users without password
    Create Session    gk                            ${GK_URL}
    ${auth} =         GET On Session      gk        /devel/users/${USER_1.name}/login

    ${resp} =         POST On Session     gk        url=${PAGE_USER_CHANGE_PASSWORD_URL}?skip_csrf=True     data=&{password_no_old_not_equal}    expected_status=200
    ${body} =         Convert To String   ${resp.content}
    Should Contain                        ${body}    New passwords doesn't match.

    ${resp} =         POST On Session     gk        url=${PAGE_USER_CHANGE_PASSWORD_URL}?skip_csrf=True     data=&{password_no_old_equal}    expected_status=200
    ${body} =         Convert To String   ${resp.content}
    Should Contain                        ${body}    Your password has been changed.

    Delete All Sessions

Old password check - user having a password
    Seed ${1} users
    Create Session    gk                            ${GK_URL}
    ${auth} =         GET On Session      gk        /devel/users/${USER_1.name}/login

    ${resp} =         POST On Session     gk        url=${PAGE_USER_CHANGE_PASSWORD_URL}?skip_csrf=True     data=&{password_not_equal}    expected_status=200
    ${body} =         Convert To String   ${resp.content}
    Should Contain                        ${body}    New passwords doesn't match.

    ${resp} =         POST On Session     gk        url=${PAGE_USER_CHANGE_PASSWORD_URL}?skip_csrf=True     data=&{password_equal}    expected_status=200
    ${body} =         Convert To String   ${resp.content}
    Should Contain                        ${body}    Your password has been changed.

    Delete All Sessions

Old password check - user having a password - no old password given
    Seed ${1} users
    Create Session    gk                            ${GK_URL}
    ${auth} =         GET On Session      gk        /devel/users/${USER_1.name}/login

    ${resp} =         POST On Session     gk        url=${PAGE_USER_CHANGE_PASSWORD_URL}?skip_csrf=True     data=&{password_no_old_not_equal}    expected_status=200
    ${body} =         Convert To String   ${resp.content}
    Should Contain                        ${body}    Please enter your old password.

    ${resp} =         POST On Session     gk        url=${PAGE_USER_CHANGE_PASSWORD_URL}?skip_csrf=True     data=&{password_no_old_equal}    expected_status=200
    ${body} =         Convert To String   ${resp.content}
    Should Contain                        ${body}    Please enter your old password.

    Delete All Sessions

*** Keywords ***

Clean
    Clear Database
    Sign Out Fast
