*** Settings ***
Library         DependencyLibrary
Resource        ../functions/PageRegistration.robot
Resource        ../vars/users.resource
Force Tags      CreateAccount

*** Test Cases ***
Create an account
    [Documentation]         Create an account
    Clear Database
    Create User             ${USER_1.name}    language=fr
    Go To Url               ${PAGE_DEV_MAILBOX_URL}
    Element Should Contain    ${DEV_MAILBOX_FIRST_MAIL_LINK}    Bienvenue sur GeoKrety.org
