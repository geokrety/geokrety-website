*** Settings ***
Library         SeleniumLibrary  timeout=10  implicit_wait=0
Library         RobotEyes
Resource        ../functions/FunctionsGlobal.robot
Resource        ../vars/users.resource
Force Tags      Users Details    RobotEyes
Suite Setup     Seed

*** Test Cases ***

Anonymous users are refused
    Sign Out Fast
    Go To                                   ${PAGE_USER_1_BANER_TEMPLATE_URL}
    Page Should Contain                     Unauthorized

Users cannot edit other user banner
    [Tags]    TODO
    Sign In ${USER_2.name} Fast
    Go To                                   ${PAGE_USER_1_BANER_TEMPLATE_URL}
    Page Should Contain                     Unauthorized

## template expansion don't work here see:
## https://github.com/jz-jess/RobotEyes/issues/67
Select banner
    [Timeout]     5 minutes
    [Template]    Select banner
    1
    2
    3
    4
    5
    6
    7
    8
    9

*** Keywords ***

Seed
    Clear Database
    Seed 2 users

Select banner
    [Arguments]    ${templateId}
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_USER_1_BANER_TEMPLATE_URL}
    Select Radio Button                     ${USER_BANER_TEMPLATE_CHOOSER_RADIO_GROUP}    ${templateId}
    Click Button                            ${USER_BANER_TEMPLATE_CHOOSER_SUBMIT_BUTTON}
    Flash message shown                     Your user banner template preference has been successfully saved.

    Open Eyes                               SeleniumLibrary  5       template_id=${templateId}
    Scroll To Element                       ${USER_PROFILE_STATPIC_IMAGE}
    Wait Until Element Is Visible           ${USER_PROFILE_STATPIC_IMAGE}
    Capture Element                         ${USER_PROFILE_STATPIC_IMAGE}
    Compare Images
