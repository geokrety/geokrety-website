*** Settings ***
Resource        ../functions/PageRegistration.robot
Resource        ../vars/users.resource
Force Tags      Sign In
Test Setup      Clear Database
Test Teardown   Sign Out User

*** Test Cases ***

Seeded are fully valid
    [Documentation]                     No TermsOfUse question for seeded users
    Seed 1 users
    Go To Url                           ${PAGE_HOME_URL}
    Sign In User                        ${USER_1.name}
    Location Should Not Be              ${PAGE_TERMS_OF_USE_URL}

Freshly created accounts are fully valid
    [Documentation]                     No TermsOfUse question for fresh users
    Create User                         ${USER_2.name}
    Activate user account
    Sign Out Fast
    Go To Url                           ${PAGE_HOME_URL}
    Sign In User                        ${USER_2.name}
    Location Should Not Be              ${PAGE_TERMS_OF_USE_URL}

Legacy accounts are asked for terms of use
    [Documentation]                     Already present accounts are asked for terms of use
    Seed 1 users without terms of use
    Go To Url                           ${PAGE_HOME_URL}
    Sign In User                        ${USER_1.name}
    Location Should Be                  ${PAGE_TERMS_OF_USE_URL}
