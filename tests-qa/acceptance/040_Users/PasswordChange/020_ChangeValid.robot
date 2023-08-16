*** Settings ***
Library         RequestsLibrary
Resource        ../../ressources/Authentication.robot
Resource        ../../ressources/Users.robot
Variables       ../../ressources/vars/users.yml
Test Setup      Test Setup


*** Test Cases ***

Valid password change - modal
    Go To User ${USER_1.id}
    Change User Password Via Modal          password    newpass    newpass
    Flash message shown                     Your password has been changed.

Valid password change - page form
    Go To Url                               ${PAGE_USER_CHANGE_PASSWORD_URL}
    Change User Password                    password    newpass    newpass
    Flash message shown                     Your password has been changed.

Confirmation mail should be sent
    Go To Url                               ${PAGE_USER_CHANGE_PASSWORD_URL}
    Change User Password                    password    newpass    newpass

    Mailbox Should Contain ${1} Messages
    Mailbox Message ${1} Subject Should Contain Your password has been changed

    Mailbox Open Message ${1}
    Page Should Contain                     Congratulation
    Page Should Contain                     Your password has been successfully changed.

*** Keywords ***

Test Setup
    Clear Database And Seed ${1} users
    Sign In ${USER_1.name} Fast
