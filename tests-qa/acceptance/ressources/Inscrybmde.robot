*** Settings ***
Library           SeleniumLibrary  timeout=10  implicit_wait=0

*** Variables ***

*** Keywords ***

Input Inscrybmde
    [Arguments]    ${element_id}    ${value}
    Execute Javascript              $("${element_id}").data('editor').value('${value}')

Inscrybmde To Textarea
    [Arguments]    ${element_id}
    Execute Javascript              $("${element_id}").data('editor').toTextArea()

Textarea To Inscrybmde
    [Arguments]    ${element_id}
    Execute Javascript              $("${element_id}").data('editor').toEditor()
