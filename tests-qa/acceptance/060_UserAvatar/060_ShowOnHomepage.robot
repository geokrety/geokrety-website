*** Settings ***
Library         SeleniumLibrary  timeout=10  implicit_wait=0
Library         RobotEyes
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/Images.robot
Resource        ../vars/users.resource
Force Tags      Users Details    Pictures    RobotEyes
Test Setup      Clear DB And Seed 1 users

*** Test Cases ***

Should be shown on Homepage
    Sign In ${USER_1.name} Fast
    Upload user avatar via button           ${PAGE_USER_1_PROFILE_URL}              ${CURDIR}/sample-picture.png    count=1
    Upload user avatar via button           ${PAGE_USER_1_PROFILE_URL}              ${CURDIR}/sample-picture2.png   count=2
    Go To Url                               ${PAGE_HOME_URL}
    Page Should Contain Element             ${HOME_PICTURE_LIST_PANEL}
    Element Count Should Be                 ${HOME_PICTURE_LIST_PICTURES}     2
    Check Image                             ${HOME_PICTURE_LIST_GALERY}
