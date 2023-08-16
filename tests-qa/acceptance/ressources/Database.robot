*** Settings ***
Documentation     Keywork to manage the database
Resource          Devel.robot
Resource          vars/Urls.robot
Library           RequestsLibrary

*** Variables ***

*** Keywords ***

Clear Database
    Go To Url Fast    ${PAGE_DEV_RESET_DB_URL}
    # ${resp} =         GET    ${PAGE_DEV_RESET_DB_URL}
    # ${body} =         Convert To String     ${resp.content}
    # Should Contain                          ${body}    OK
