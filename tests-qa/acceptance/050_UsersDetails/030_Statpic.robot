*** Settings ***
Library         SeleniumLibrary  timeout=10  implicit_wait=0
Library         RobotEyes
Resource        ../functions/PageGeoKretyCreate.robot
Resource        ../vars/users.resource
Resource        ../vars/geokrety.resource
Force Tags      Users Details    RobotEyes
Test Setup      Clear Database

*** Test Cases ***
##########################
###     WARNING !!!    ###
###  This require a patch see: https://github.com/jz-jess/RobotEyes/pull/65
##########################

Statpic generated on user create
    Seed 1 users
    Go To User 1 url
    Check Image             ${USER_PROFILE_STATPIC_IMAGE}

Statpic updated on geokrety create
    Seed 1 users
    Seed 1 geokrety owned by 1
    Go To User 1 url
    Check Image             ${USER_PROFILE_STATPIC_IMAGE}

# Todo check moves
