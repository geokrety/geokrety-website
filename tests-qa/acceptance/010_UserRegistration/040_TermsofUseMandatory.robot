*** Settings ***
Library         DependencyLibrary
Resource        ../functions/PageRegistration.robot
Resource        ../vars/users.resource
Force Tags      CreateAccount

*** Test Cases ***
Terms of use unchecked
    [Documentation]               When unckecked form cannot be submitted
    Clear Database
    Go To Url                     ${PAGE_REGISTER_URL}
    Fill Registration Form        ${USER_1.name}    terms_of_use=${FALSE}
    Click Button                  ${REGISTRATION_REGISTER_BUTTON}
    Location Should Be            ${PAGE_REGISTER_URL}
    Input validation has error    ${REGISTRATION_TERMS_OF_USE_CHECKBOX}

Terms of use checked
    Depends on test               Terms of use unchecked
    [Documentation]               When ckecked form can be submitted
    Select Checkbox               ${REGISTRATION_TERMS_OF_USE_CHECKBOX}
    Input validation has success  ${REGISTRATION_TERMS_OF_USE_CHECKBOX}
    Click Button                  ${REGISTRATION_REGISTER_BUTTON}
    Location Should Contain       ${GK_URL}/en/users/
    Page Should Not Contain       This user does not exist
