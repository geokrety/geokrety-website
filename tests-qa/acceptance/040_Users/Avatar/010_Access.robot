*** Settings ***
Library         RequestsLibrary
Resource        ../../ressources/Authentication.robot
Resource        ../../ressources/Pictures.robot
Variables       ../../ressources/vars/users.yml
Suite Setup     Suite Setup

*** Test Cases ***

Anonymous should not see draggable
    Sign Out Fast
    Go To User ${USER_1.id}
    Page Should Not Contain Element                 ${USER_PROFILE_DROPZONE}

User himself should see draggable
    Sign In ${USER_1.name} Fast
    Go To User ${USER_1.id}
    Page Should Contain Element                     ${USER_PROFILE_DROPZONE}

Authenticated should not see draggable for other users
    Sign In ${USER_1.name} Fast
    Go To User ${USER_2.id}
    Page Should Not Contain Element                 ${USER_PROFILE_DROPZONE}

*** Keywords ***

Suite Setup
    Clear Database And Seed ${2} users
