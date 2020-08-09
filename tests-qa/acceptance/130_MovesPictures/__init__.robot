*** Settings ***
Library             SeleniumLibrary  timeout=10  implicit_wait=0
Resource            ../functions/FunctionsGlobal.robot
Test Timeout        2 minutes
Suite Setup         Global Setup
Suite Teardown      Global TearDown
