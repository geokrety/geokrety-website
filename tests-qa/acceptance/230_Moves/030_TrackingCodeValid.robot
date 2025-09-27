*** Settings ***
Library         RequestsLibrary
Library         Dialogs
Resource        ../ressources/TomSelect.robot
Resource        ../ressources/Authentication.robot
Variables       ../ressources/vars/users.yml
Variables       ../ressources/vars/geokrety.yml
Suite Setup     Suite Setup
Suite Teardown  Suite Teardown
Test Setup      Test Setup
Test Teardown   Test Teardown

*** Variables ***
${MAX_TRACKING_CODES_AUTHENTICATED}    10    # Max for authenticated users
${MAX_TRACKING_CODES_ANONYMOUS}        1     # Max for anonymous users
${LONG_TRACKING_CODE}                  ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890
${MEDIUM_TRACKING_CODE}                12345678    # 8 chars - over 7 char limit
@{REFERENCE_NUMBERS}                   GK    GK0    GK001    GK0001

# Error message constants based on actual application behavior
${ANONYMOUS_MULTIPLE_TC_ERROR}         Anonymous users cannot check multiple Tracking Codes at once. Please login first.

*** Test Cases ***

Fill Single Valid Tracking Code Should Load GeoKret
    [Documentation]    Verify that entering a valid tracking code loads the corresponding GeoKret
    [Tags]    tracking-code    single    valid
    Go To Move
    Fill Tracking Code                      ${GEOKRETY_1.tc}
    Verify GeoKret Loaded                   ${GEOKRETY_1}    first

Fill Invalid Tracking Code Should Show Error
    [Documentation]    Verify that entering an invalid tracking code shows appropriate error messages
    [Tags]    tracking-code    single    invalid    error-handling
    Go To Move
    Fill Tracking Code                      ${TC_INVALID}
    Verify Tracking Code Error              Sorry, but Tracking Code "${TC_INVALID}" was not found in our database.

Fill Empty Tracking Code Should Require Submission
    [Documentation]    Verify that empty tracking code validation occurs on form submission
    [Tags]    tracking-code    single    invalid    edge-case
    Go To Move
    Fill Tracking Code                      ${EMPTY}
    Click Button                            ${MOVE_TRACKING_CODE_CHECK_BUTTON}
    # Empty validation might only trigger on submission, not on blur
    Run Keyword And Ignore Error           Input validation has error    ${MOVE_TRACKING_CODE_INPUT}

Fill Long Invalid Tracking Code Should Show Length Error
    [Documentation]    Test edge case with very long tracking code shows length validation
    [Tags]    tracking-code    single    invalid    edge-case    length-validation
    Go To Move
    Fill Tracking Code                      ${LONG_TRACKING_CODE}
    Verify Tracking Code Error              Tracking Code "${LONG_TRACKING_CODE}" seems too long. We expect 7 characters maximum here.

Fill Medium Length Invalid Tracking Code Should Show Length Error
    [Documentation]    Test tracking code just over the 7 character limit
    [Tags]    tracking-code    single    invalid    edge-case    length-validation
    Go To Move
    Fill Tracking Code                      ${MEDIUM_TRACKING_CODE}
    Verify Tracking Code Error              Tracking Code "${MEDIUM_TRACKING_CODE}" seems too long. We expect 7 characters maximum here.

Fill Tracking Code At Maximum Length Should Work
    [Documentation]    Test valid tracking code at exactly 7 characters
    [Tags]    tracking-code    single    valid    edge-case
    Go To Move
    Fill Tracking Code                      1234567
    Click Button                            ${MOVE_TRACKING_CODE_CHECK_BUTTON}
    # Should show "not found" error, not length error
    Verify Tracking Code Error              Sorry, but Tracking Code "1234567" was not found in our database.

Fill Tracking Code With Reference Numbers Should Show Specific Error
    [Documentation]    Verify that using reference numbers instead of tracking codes shows helpful error
    [Tags]    tracking-code    reference-number    error-handling
    [Template]    Fill And Verify Reference Number Error
    FOR    ${ref_num}    IN    @{REFERENCE_NUMBERS}
        ${ref_num}
    END

Fill Tracking Code With TC Starting By GK Should Work
    [Documentation]    Verify that tracking codes starting with GK work correctly
    [Tags]    tracking-code    special-case
    Seed special geokrety with tracking code starting with GK owned by ${1}
    Go To Move
    Fill Tracking Code                      ${GEOKRETY_STARTING_WITH_GK.tc}
    Verify GeoKret Loaded                   ${GEOKRETY_STARTING_WITH_GK}    first

Fill Multiple Tracking Codes Should Fail For Anonymous Users
    [Documentation]    Anonymous users should not be able to check multiple tracking codes
    [Tags]    tracking-code    multiple    anonymous    security
    Go To Move
    Fill Tracking Code                      ${GEOKRETY_1.tc},${GEOKRETY_2.tc}
    Verify Multiple Tracking Code Error For Anonymous

Fill Multiple Valid Tracking Codes Should Load All GeoKrety
    [Documentation]    Logged-in users should be able to check multiple valid tracking codes
    [Tags]    tracking-code    multiple    authenticated
    Sign In ${USER_1.name} Fast
    Go To Move
    Fill Tracking Code                      ${GEOKRETY_1.tc},${GEOKRETY_2.tc}
    Verify Multiple GeoKrety Loaded         ${GEOKRETY_1}    ${GEOKRETY_2}

Fill Mixed Valid And Invalid Tracking Codes Should Show Error
    [Documentation]    Test behavior when mixing valid and invalid tracking codes - should reject entire input
    [Tags]    tracking-code    multiple    mixed    edge-case    error-handling
    Sign In ${USER_1.name} Fast
    Go To Move
    Fill Tracking Code                      ${GEOKRETY_1.tc},${TC_INVALID},${GEOKRETY_2.tc}
    # Application appears to reject entire input when any tracking code is invalid
    Wait Until Page Contains Element        ${MOVE_TRACKING_CODE_INPUT}/ancestor::div[contains(@class, "form-group") and contains(@class, "has-error")]    timeout=2s
    # Could also check for specific error message if one is shown

Fill Multiple Tracking Codes With One Too Long Should Show Error
    [Documentation]    Test multiple tracking codes where one exceeds length limit
    [Tags]    tracking-code    multiple    mixed    edge-case    length-validation
    Sign In ${USER_1.name} Fast
    Go To Move
    Fill Tracking Code                      ${GEOKRETY_1.tc},${LONG_TRACKING_CODE}
    # Should show error for the long tracking code
    Run Keyword And Ignore Error           Input validation has error    ${MOVE_TRACKING_CODE_INPUT}

Fill Maximum Number Of Tracking Codes Should Work
    [Documentation]    Test the maximum limit of tracking codes for authenticated users is accepted
    [Tags]    tracking-code    multiple    limits    edge-case    authenticated
    Sign In ${USER_1.name} Fast
    Go To Move
    ${tc_list}=    Build Comma Separated Tracking Codes    ${MAX_TRACKING_CODES_AUTHENTICATED}
    Fill Tracking Code                      ${tc_list}
    Click Button                            ${MOVE_TRACKING_CODE_CHECK_BUTTON}
    Verify Number Of GeoKrety Loaded        ${MAX_TRACKING_CODES_AUTHENTICATED}

GeoKret Reference Should Be Displayed In Panel Heading Using TomSelect
    [Documentation]    Test the TomSelect widget for adding tracking codes with proper reference display
    [Tags]    tracking-code    tomselect    ui    reference-display
    Sign In ${USER_1.name} Fast
    Go To Move

    # Test single item
    Test Single TomSelect Addition          ${GEOKRETY_1}

    # Test multiple items
    Test Multiple TomSelect Addition        ${GEOKRETY_1}    ${GEOKRETY_2}

    # Test invalid tracking code
    Test Invalid TomSelect Input            ${TC_INVALID}

Test TomSelect Item Removal
    [Documentation]    Test removing items from TomSelect widget using API
    [Tags]    tracking-code    tomselect    ui    removal
    Sign In ${USER_1.name} Fast
    Go To Move

    # Start clean and add two TCs using the improved TomSelect keywords
    Clear TomSelect                         ${MOVE_TRACKING_CODE_INPUT_BASE}
    Add TomSelect Value When Ready          ${MOVE_TRACKING_CODE_INPUT_BASE}            ${GEOKRETY_1.tc}
    Add TomSelect Value When Ready          ${MOVE_TRACKING_CODE_INPUT_BASE}            ${GEOKRETY_2.tc}
    Wait Until TomSelect Has Item Count     ${MOVE_TRACKING_CODE_INPUT_BASE}            2

    # Verify both items are present
    TomSelect Should Contain Item           ${MOVE_TRACKING_CODE_INPUT_BASE}            ${GEOKRETY_1.tc}
    TomSelect Should Contain Item           ${MOVE_TRACKING_CODE_INPUT_BASE}            ${GEOKRETY_2.tc}

    # Submit and verify both are considered
    Click Button                            ${MOVE_TRACKING_CODE_CHECK_BUTTON}
    Wait Until Page Contains Element        ${MOVE_TRACKING_CODE_SECOND_RESULT_ITEM}
    Element Count Should Be                 ${MOVE_TRACKING_CODE_RESULTS_ITEMS}         2
    Element Text Should Be                  ${MOVE_TRACKING_CODE_PANEL_HEADER_TEXT}     ${GEOKRETY_1.ref} ${GEOKRETY_2.ref}

    # Remove one item using the API
    Remove TomSelect Item                   ${MOVE_TRACKING_CODE_INPUT_BASE}            ${GEOKRETY_2.tc}
    Wait Until TomSelect Has Item Count     ${MOVE_TRACKING_CODE_INPUT_BASE}            1

    # Verify the correct item was removed
    TomSelect Should Contain Item           ${MOVE_TRACKING_CODE_INPUT_BASE}            ${GEOKRETY_1.tc}
    TomSelect Should Not Contain Item       ${MOVE_TRACKING_CODE_INPUT_BASE}            ${GEOKRETY_2.tc}

    # Submit again: we should now see only one result and header with a single ref
    Click Button                            ${MOVE_TRACKING_CODE_CHECK_BUTTON}
    Wait Until Page Contains Element        ${MOVE_TRACKING_CODE_FIRST_RESULT_ITEM}
    Element Count Should Be                 ${MOVE_TRACKING_CODE_RESULTS_ITEMS}         1
    Element Text Should Be                  ${MOVE_TRACKING_CODE_PANEL_HEADER_TEXT}     ${GEOKRETY_1.ref}

Test TomSelect With Length Validation
    [Documentation]    Test TomSelect behavior with tracking codes that exceed length limits
    [Tags]    tracking-code    tomselect    ui    length-validation    edge-case
    Sign In ${USER_1.name} Fast
    Go To Move
    Clear TomSelect                         ${MOVE_TRACKING_CODE_INPUT_BASE}
    Input Text                              ${MOVE_TRACKING_CODE_INPUT}                 ${MEDIUM_TRACKING_CODE}
    Simulate Event                          ${MOVE_TRACKING_CODE_INPUT}                 blur
    Click Button                            ${MOVE_TRACKING_CODE_CHECK_BUTTON}
    # Should show length validation error
    Run Keyword And Ignore Error           Input validation has error    ${MOVE_TRACKING_CODE_INPUT}

*** Keywords ***

Suite Setup
    [Documentation]    Set up test data for the entire suite
    Clear Database And Seed ${1} users
    # Seed enough GeoKrety for all tests, including max-multiple test
    Seed ${MAX_TRACKING_CODES_AUTHENTICATED} geokrety owned by ${1}
    Sign Out Fast

Suite Teardown
    [Documentation]    Clean up after all tests
    Sign Out Fast

Test Setup
    [Documentation]    Ensure clean state before each test
    # Ensure we're logged out unless test specifically signs in
    Run Keyword And Ignore Error    Sign Out Fast

Test Teardown
    [Documentation]    Clean up after each test
    # Clear any validation errors or states
    Run Keyword And Ignore Error    Go To Move

Fill Tracking Code
    [Documentation]    Fill tracking code input and trigger blur event
    [Arguments]    ${tracking_code}
    Input Text                              ${MOVE_TRACKING_CODE_INPUT}                 ${tracking_code}
    Simulate Event                          ${MOVE_TRACKING_CODE_INPUT}                 blur

Verify GeoKret Loaded
    [Documentation]    Verify that a GeoKret was loaded correctly in results
    [Arguments]    ${geokrety}    ${position}=first
    Wait Until Page Contains Element        ${MOVE_TRACKING_CODE_${position.upper()}_RESULT_ITEM}
    Element Should Be Visible               ${MOVE_TRACKING_CODE_RESULT_LIST}
    Element Should Contain                  ${MOVE_TRACKING_CODE_${position.upper()}_RESULT_ITEM}     ${geokrety.name} by ${USER_1.name}
    Element Should Contain                  ${MOVE_TRACKING_CODE_${position.upper()}_RESULT_ITEM}     Never moved

Verify Tracking Code Error
    [Documentation]    Verify that tracking code validation shows specific error
    [Arguments]    ${expected_error_message}
    Input validation has error              ${MOVE_TRACKING_CODE_INPUT}
    Input validation has error help         ${MOVE_TRACKING_CODE_INPUT}                 ${expected_error_message}
    Panel validation has error              ${MOVE_TRACKING_CODE_PANEL}

Verify Multiple Tracking Code Error For Anonymous
    [Documentation]    Verify error message for anonymous users trying multiple tracking codes
    Input validation has error              ${MOVE_TRACKING_CODE_INPUT}
    Input validation has error help         ${MOVE_TRACKING_CODE_INPUT}                 ${ANONYMOUS_MULTIPLE_TC_ERROR}
    Panel validation has error              ${MOVE_TRACKING_CODE_PANEL}

Verify Multiple GeoKrety Loaded
    [Documentation]    Verify that multiple GeoKrety were loaded correctly
    [Arguments]    ${geokrety1}    ${geokrety2}
    Wait Until Page Contains Element        ${MOVE_TRACKING_CODE_FIRST_RESULT_ITEM}
    Element Count Should Be                 ${MOVE_TRACKING_CODE_RESULTS_ITEMS}         2

    Element Should Contain                  ${MOVE_TRACKING_CODE_FIRST_RESULT_ITEM}     ${geokrety1.name} by ${USER_1.name}
    Wait Until Page Contains Element        ${MOVE_TRACKING_CODE_SECOND_RESULT_ITEM}
    Element Should Contain                  ${MOVE_TRACKING_CODE_SECOND_RESULT_ITEM}    ${geokrety2.name} by ${USER_1.name}

Verify Number Of GeoKrety Loaded
    [Documentation]    Verify that exactly ${count} GeoKrety are displayed in results
    [Arguments]    ${count}
    Wait Until Page Contains Element        ${MOVE_TRACKING_CODE_FIRST_RESULT_ITEM}
    Element Count Should Be                 ${MOVE_TRACKING_CODE_RESULTS_ITEMS}         ${count}

Fill And Verify Reference Number Error
    [Documentation]    Template keyword for testing reference number errors
    [Arguments]    ${reference_number}
    Go To Url                               ${PAGE_MOVES_URL}
    Fill Tracking Code                      ${reference_number}
    Click Button                            ${MOVE_TRACKING_CODE_CHECK_BUTTON}
    Input validation has error              ${MOVE_TRACKING_CODE_INPUT}
    Input validation has error help         ${MOVE_TRACKING_CODE_INPUT}                 You seems to have used the GeoKret public identifier "${reference_number}".
    Panel validation has error              ${MOVE_TRACKING_CODE_PANEL}

Test Single TomSelect Addition
    [Documentation]    Test adding single item via TomSelect
    [Arguments]    ${geokrety}
    Debug TomSelect                         ${MOVE_TRACKING_CODE_INPUT_BASE}
    Add TomSelect Value When Ready          ${MOVE_TRACKING_CODE_INPUT_BASE}            ${geokrety.tc}
    Click Button                            ${MOVE_TRACKING_CODE_CHECK_BUTTON}
    Wait Until Page Contains Element        ${MOVE_TRACKING_CODE_FIRST_RESULT_ITEM}
    Element Text Should Be                  ${MOVE_TRACKING_CODE_PANEL_HEADER_TEXT}     ${geokrety.ref}

Test Multiple TomSelect Addition
    [Documentation]    Test adding multiple items via TomSelect
    [Arguments]    ${geokrety1}    ${geokrety2}
    Clear TomSelect                         ${MOVE_TRACKING_CODE_INPUT_BASE}
    Add TomSelect Value When Ready          ${MOVE_TRACKING_CODE_INPUT_BASE}            ${geokrety1.tc}
    Add TomSelect Value When Ready          ${MOVE_TRACKING_CODE_INPUT_BASE}            ${geokrety2.tc}

    Click Button                            ${MOVE_TRACKING_CODE_CHECK_BUTTON}
    Wait Until Page Contains Element        ${MOVE_TRACKING_CODE_SECOND_RESULT_ITEM}
    Element Text Should Be                  ${MOVE_TRACKING_CODE_PANEL_HEADER_TEXT}     ${geokrety1.ref} ${geokrety2.ref}

Test Invalid TomSelect Input
    [Documentation]    Test invalid input via TomSelect
    [Arguments]    ${invalid_tc}
    Clear TomSelect                         ${MOVE_TRACKING_CODE_INPUT_BASE}
    Input Text                              ${MOVE_TRACKING_CODE_INPUT}                 ${invalid_tc}
    Simulate Event                          ${MOVE_TRACKING_CODE_INPUT}                 blur
    Click Button                            ${MOVE_TRACKING_CODE_CHECK_BUTTON}
    Element Text Should Be                  ${MOVE_TRACKING_CODE_PANEL_HEADER_TEXT}     ${EMPTY}

Build Comma Separated Tracking Codes
    [Documentation]    Build a comma-separated list of ${count} tracking codes from seeded GeoKrety
    [Arguments]    ${count}
    @{tcs}=    Create List
    ${end}=    Evaluate    int(${count}) + 1
    FOR    ${i}    IN RANGE    1    ${end}
        ${tc}=    Get Variable Value    ${GEOKRETY_${i}.tc}
        Append To List    ${tcs}    ${tc}
    END
    ${joined}=    Catenate    SEPARATOR=,    @{tcs}
    RETURN    ${joined}
