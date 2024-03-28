*** Settings ***
Metadata  Log of First Run   [log.html|log.html]
Metadata  Log of Second Run  [log-rerun.html|log-rerun.html]

Resource            ressources/FunctionsGlobal.robot
Test Timeout        1 minutes
Suite Setup         Global Setup
Suite Teardown      Global TearDown

*** Variables ***

*** Keywords ***

Global Setup
    Clear css assets
    Clear Database
    !Open GeoKrety Browser

Global TearDown
    Close Browser
