*** Settings ***
Metadata  Log of First Run   [log.html|log.html]
Metadata  Log of Second Run  [log-rerun.html|log-rerun.html]

Resource            ressources/FunctionsGlobal.robot
Test Timeout        1 minutes
Suite Setup         Global Setup
Suite Teardown      Global TearDown

*** Variables ***

${browser}       Firefox

*** Keywords ***

Global Setup
    Clear Database
    !Open GeoKrety Browser  ${browser}    # which browser? the one that's the value of the variable

Global TearDown
    Close Browser
