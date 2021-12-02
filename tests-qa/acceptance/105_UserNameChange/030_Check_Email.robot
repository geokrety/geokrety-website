*** Settings ***
Library         SeleniumLibrary  timeout=10  implicit_wait=0
Library         Dialogs
Resource        ../functions/FunctionsGlobal.robot
Resource        ../vars/users.resource
Force Tags      Users Details    Username    Email

*** Test Cases ***

Users without mail address cannot change their username
    Clear Database
    Go To Url                               ${PAGE_SEED_USER}/1?noemail\=true
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_USER_CHANGE_USERNAME_URL}
    Page Should Contain                     Sorry, to use this feature, you must have a valid registered email address.

Users without valid mail address cannot change their username
    [Template]    Seed user with invalid email
    1
    2
    3

*** Keywords ***

Seed user with invalid email
    [Arguments]    ${email_invalid}
    Clear Database
    Go To Url                               ${PAGE_SEED_USER}/1?email_invalid\=${email_invalid}
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_USER_CHANGE_USERNAME_URL}
    Page Should Contain                     Sorry, to use this feature, you must have a valid registered email address.
