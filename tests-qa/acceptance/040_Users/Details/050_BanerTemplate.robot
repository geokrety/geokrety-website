*** Settings ***
Library         RequestsLibrary
Library         RobotEyes
Resource        ../../ressources/Authentication.robot
Variables       ../../ressources/vars/users.yml
Suite Setup     Suite Setup
Force Tags      Statpic

*** Test Cases ***

Anonymous users are refused
    Sign Out Fast
    Go To Url                                ${PAGE_USER_1_BANER_TEMPLATE_URL}    redirect=${PAGE_SIGN_IN_URL}
    Flash message shown                     ${UNAUTHORIZED}

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

Post an invalid baner id
    [Timeout]     5 minutes
    [Template]    Select invalid banner
    456           The Statpic_template field needs to be a numeric value, equal to, or lower than '9'
    -1            The Statpic_template field needs to be a numeric value, equal to, or higher than '1'
    ABC           The Statpic_template field needs to be a numeric value, equal to, or higher than '1'


*** Keywords ***

Suite Setup
    Clear Database And Seed ${2} users

Select banner
    [Tags]    OpenEyes
    [Arguments]    ${templateId}
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_USER_1_BANER_TEMPLATE_URL}
    Select Radio Button                     ${USER_BANER_TEMPLATE_CHOOSER_RADIO_GROUP}    ${templateId}
    Click Button                            ${USER_BANER_TEMPLATE_CHOOSER_SUBMIT_BUTTON}
    Flash message shown                     Your user banner template preference has been successfully saved.

    Open Eyes                               Browser  5       template_id=${templateId}
    Scroll To Element                       ${USER_PROFILE_STATPIC_IMAGE}
    Wait Until Element Is Visible           ${USER_PROFILE_STATPIC_IMAGE}
    Capture Element                         ${USER_PROFILE_STATPIC_IMAGE}
    Compare Images

Select invalid banner
    [Arguments]    ${templateId}    ${message}
    ${data} =         Evaluate              {'statpic': '${templateId}'}
    Create Session                          gk         ${GK_URL}
    ${auth} =         GET On Session        gk         /devel/users/${USER_1.name}/login
    ${resp} =         POST On Session       gk         url=${PAGE_USER_1_BANER_TEMPLATE_URL}?skip_csrf=True     data=${data}    expected_status=200
    ${body} =         Convert To String     ${resp.content}
    Should Contain                          ${body}    ${message}
    Delete All Sessions
