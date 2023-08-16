*** Settings ***
Library         RequestsLibrary
Resource        ../../ressources/Authentication.robot
Variables       ../../ressources/vars/users.yml
Suite Setup     Suite Setup

*** Test Cases ***

Refresh should display a message
    Sign In ${USER_1.name} Fast
    Go To User ${USER_1.id}
    Click Link                              ${USER_PROFILE_SECID_REFRESH_BUTTON}
    Wait Until Modal                        Refresh your secid?
    Click Button                            ${MODAL_DIALOG_SUBMIT_BUTTON}
    Flash message shown                     Your secid has been refreshed.

*** keywords ***

Suite Setup
    Clear Database And Seed ${1} users
