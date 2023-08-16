*** Settings ***
Resource   ComponentsLocator.robot
Resource   vars/Urls.robot
Resource   vars/Strings.robot
Resource   Browser.robot
Resource   Database.robot
Library    SeleniumLibrary  timeout=10  implicit_wait=0
# doc: http://robotframework.org/Selenium2Library/Selenium2Library.html
#      http://robotframework.org/robotframework/latest/RobotFrameworkUserGuide.html

*** Variables ***

${BROWSER}        Firefox

*** Keywords ***

Page WithoutWarningOrFailure
    Page Should Not Contain    Warning:
    Page Should Not Contain    Failed
    Page Should Not Contain    Internal Server Error

Variable Without Warning Or Failure
    [Arguments]    ${body}
    Should Not Contain         ${body}    Warning:
    Should Not Contain         ${body}    Failed
    Should Not Contain         ${body}    Internal Server Error
