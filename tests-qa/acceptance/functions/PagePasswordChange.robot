*** Settings ***
Resource        FunctionsGlobal.robot

*** Keywords ***

Fill Password Change Form
    [Arguments]    ${old}=password    ${new}=newpass    ${confirm}=${new}
    Input Text                              ${USER_PASSWORD_OLD_INPUT}          ${old}
    Input Text                              ${USER_PASSWORD_NEW_INPUT}          ${new}
    Input Text                              ${USER_PASSWORD_CONFIRM_INPUT}      ${confirm}
