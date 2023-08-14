*** Settings ***
Library         RequestsLibrary
Resource        ../ressources/Authentication.robot
Variables       ../ressources/vars/users.yml
Test Setup      Test Setup

*** Test Cases ***
Terms of use unchecked
    [Documentation]               When unckecked form cannot be submitted

    Go To Url                     ${PAGE_REGISTER_URL}
    Fill Registration Form        ${USER_1.name}
    ...                           email=${USER_1.email}
    ...                           terms_of_use=${FALSE}
    Click Button                  ${REGISTRATION_REGISTER_BUTTON}

    Location Should Be            ${PAGE_REGISTER_URL}
    Input validation has error    ${REGISTRATION_TERMS_OF_USE_CHECKBOX}

    # When ckecked form can be submitted
    Select Checkbox               ${REGISTRATION_TERMS_OF_USE_CHECKBOX}
    Input validation has success  ${REGISTRATION_TERMS_OF_USE_CHECKBOX}

    Click Button                  ${REGISTRATION_REGISTER_BUTTON}
    Location Should Contain       ${GK_URL}/en/users/
    Page Should Not Contain       This user does not exist

*** Keywords ***

Test Setup
    Clear Database
    Sign Out Fast
