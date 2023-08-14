*** Settings ***
Library         RequestsLibrary
Resource        ../ressources/Authentication.robot
Variables       ../ressources/vars/users.yml
Test Setup      Test Setup

*** Test Cases ***

Seeded are fully valid
    [Documentation]                     No TermsOfUse question for seeded users
    Seed 1 users
    Sign In User                        ${USER_1.name}
    Location Should Not Be              ${PAGE_TERMS_OF_USE_URL}

Freshly created accounts are fully valid
    [Documentation]                     No TermsOfUse question for fresh users
    Register User                       &{USER_1}
    Activate user account
    Sign Out Fast
    Sign In User                        ${USER_2.name}
    Location Should Not Be              ${PAGE_TERMS_OF_USE_URL}

Legacy accounts are asked for terms of use
    [Documentation]                     Already present accounts are asked for terms of use
    Seed 1 users without terms of use
    Sign In ${USER_1.name} Fast
    Go To User ${USER_1.id}             redirect=${PAGE_TERMS_OF_USE_URL}

*** Keywords ***

Test Setup
    Clear Database
    Sign Out Fast
    Empty Dev Mailbox Fast
