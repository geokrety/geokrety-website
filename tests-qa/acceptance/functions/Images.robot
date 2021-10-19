*** Settings ***
Library 	   OperatingSystem
Resource       FunctionsGlobal.robot

*** Keywords ***

Upload user avatar via button
    [Arguments]    ${user_profile_url}    ${file}    ${count}=1
    Go To                                   ${user_profile_url}
    Wait Until Page Contains Element        ${USER_PROFILE_DROPZONE_PICTURE_UPLOAD_BUTTON}    timeout=30
    Choose File    	                        //*[@type="file"]    ${file}
    # Run Keyword And Continue On Failure     Wait Until Page Contains Element        ${USER_PROFILE_DROPZONE_IMAGE}\[${count}]${DROPZONE_PROCESSING_SUFFIX}    timeout=30  ## Errors caused by invalid syntax, timeouts, or fatal exceptions are not caught by this keyword.
    Wait Until Page Contains Element        ${USER_PROFILE_DROPZONE_IMAGE}\[${count}]${DROPZONE_PROCESSED_SUFFIX}     timeout=30

Upload user avatar via Drag/Drop - same page
    [Arguments]    ${user_profile_url}    ${source}    ${count}=1
    Go To                                   ${user_profile_url}
    Wait Until Page Contains Element        ${USER_PROFILE_DROPZONE_PICTURE_UPLOAD_BUTTON}    timeout=30
    Drag And Drop                           ${source}    ${USER_PROFILE_DROPZONE}
    # Run Keyword And Continue On Failure     Wait Until Page Contains Element        ${USER_PROFILE_DROPZONE_IMAGE}\[${count}]${DROPZONE_PROCESSING_SUFFIX}    timeout=30  ## Errors caused by invalid syntax, timeouts, or fatal exceptions are not caught by this keyword.
    Wait Until Page Contains Element        ${USER_PROFILE_DROPZONE_IMAGE}\[${count}]${DROPZONE_PROCESSED_SUFFIX}     timeout=30

Click Picture Action
    [Arguments]    ${image}    ${button}
    Wait Until Page Contains Element        ${image}${PICTURE_PULLER}
    Scroll Into View                        ${image}${PICTURE_PULLER}
    Mouse Over                              ${image}${PICTURE_PULLER}
    Mouse Over                              ${image}${button}
    Click Button                            ${image}${button}

Upload picture via button
    [Arguments]    ${dropzone}    ${results}    ${file}    ${position}=1
    Wait Until Page Contains Element        ${dropzone}
    Scroll Into View                        ${dropzone}
    Choose File    	                        ${dropzone}//*[@type="file"]       ${file}
    # Run Keyword And Continue On Failure     Wait Until Page Contains Element        ${results}\[${position}]${DROPZONE_PROCESSING_SUFFIX}    timeout=5  ## Errors caused by invalid syntax, timeouts, or fatal exceptions are not caught by this keyword.
    Wait Until Page Contains Element        ${results}\[${position}]${DROPZONE_PROCESSED_SUFFIX}     timeout=5

Upload picture via via Drag/Drop - same page
    [Arguments]    ${dropzone}    ${results}    ${source}    ${position}=1
    Wait Until Page Contains Element        ${dropzone}
    Scroll Into View                        ${dropzone}
    Drag And Drop                           ${source}    ${dropzone}
    # Run Keyword And Continue On Failure     Wait Until Page Contains Element        ${results}\[${position}]${DROPZONE_PROCESSING_SUFFIX}    timeout=5  ## Errors caused by invalid syntax, timeouts, or fatal exceptions are not caught by this keyword.
    Wait Until Page Contains Element        ${results}\[${position}]${DROPZONE_PROCESSED_SUFFIX}     timeout=5


*** Keywords ***

Drag And Drop
    [Arguments]     ${src}    ${tgt}
    ${js}        Get File              acceptance/functions/drag-n-drop.js
    ${result}    Execute Javascript    ${js}; return DragNDrop('${src}', '${tgt}');
