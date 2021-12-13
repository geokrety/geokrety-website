*** Settings ***
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/PageEmailChange.robot
Resource        ../functions/PageRegistration.robot
Resource        ../vars/users.resource
Force Tags      Users Details    Email
Test Setup      Clear Database

*** Test Cases ***

Disable daily mails
    Create User                             ${USER_1.name}    daily_mail=${TRUE}
    Change daily mail preferences           ${FALSE}
    Flash message shown                     Your email preferences were saved.

Enable daily mails
    Create User                             ${USER_1.name}    daily_mail=${FALSE}
    Change daily mail preferences           ${TRUE}
    Flash message shown                     Your email preferences were saved.
