*** Settings ***
Resource          CustomActions.robot
Resource          ComponentsLocator.robot
Library           SeleniumLibrary  timeout=10  implicit_wait=0
Library           RobotEyes
Library 	      OperatingSystem

*** Variables ***

${CAPTION_INPUT}     //*[@id="caption"]

${PICTURE_PULLER}    //div[contains(@class, "pictures-actions-pull")]
${PICTURE_PULLER_SET_AS_AVATAR_BUTTON}      //button[@data-type="define-as-main-avatar"]
${PICTURE_PULLER_EDIT_BUTTON}               //button[@data-type="picture-edit"]
${PICTURE_PULLER_DELETE_BUTTON}             //button[@data-type="picture-delete"]

${DROPZONE_PROCESSING_SUFFIX}               //span[@class="picture-message"]
${DROPZONE_PROCESSED_SUFFIX}                //a[@class="picture-link"]

${GEOKRET_DETAILS_AVATAR_DROPZONE}                            //div[contains(@class, "dropzone")]
${GEOKRET_DETAILS_AVATAR_DROPZONE_PICTURE_UPLOAD_BUTTON}      //button[@id="geokretAvatarUploadButton" and contains(@class, "dz-clickable")]
${GEOKRET_DETAILS_AVATAR_IMAGES}                              //*[@id="geokretPicturesList"]//div[contains(@class, "gallery")]
${GEOKRET_DETAILS_AVATAR_IMAGES_ALL}                          ${GEOKRET_DETAILS_AVATAR_IMAGES}//div[contains(@class, "gallery")]
${GEOKRET_DETAILS_AVATAR_FIRST_IMAGE}                         ${GEOKRET_DETAILS_AVATAR_IMAGES}\[1]
${GEOKRET_DETAILS_AVATAR_SECOND_IMAGE}                        ${GEOKRET_DETAILS_AVATAR_IMAGES}\[2]

${GEOKRET_MOVE_DROPZONE}                            //div[contains(@class, "dropzone")]
${GEOKRET_MOVE_DROPZONE_PICTURE_UPLOAD_BUTTON}      //button[contains(@class, "movePictureUploadButton") and contains(@class, "dz-clickable")]
${GEOKRET_MOVE_IMAGES}                              //div[contains(@class, "gallery")]/div[contains(@class, "gallery")]
${GEOKRET_MOVE_FIRST_IMAGE}                         ${GEOKRET_MOVE_IMAGES}\[1]
${GEOKRET_MOVE_SECOND_IMAGE}                        ${GEOKRET_MOVE_IMAGES}\[2]

${USER_PROFILE_DROPZONE}                        //*[@id="userAvatar" and contains(@class, "dropzone")]
${USER_PROFILE_DROPZONE_IMAGE}                  //*[@id="userPicturesList"]//div[@class="gallery"]/div[@class="gallery"]
${USER_PROFILE_DROPZONE_PICTURE_UPLOAD_BUTTON}  //*[@id="userAvatarUploadButton" and contains(@class, "dz-clickable")]
${USER_PROFILE_IMAGES}                          ${USER_PROFILE_PICTURES_PANEL}//div[contains(@class, "gallery")]//div[contains(@class, "gallery")]
${USER_PROFILE_FIRST_IMAGE}                     //*[@id="userPicturesList"]//div[@class="gallery"]/div[@class="gallery"][1]
${USER_PROFILE_SECOND_IMAGE}                    //*[@id="userPicturesList"]//div[@class="gallery"]/div[@class="gallery"][2]
${USER_PROFILE_AVATAR_GALLERY}                  ${USER_PROFILE_DETAILS_PANEL}//div[contains(@class, "gallery")]

*** Keywords ***

Check Image
    [Arguments]    ${element}    ${name}=img1
    Open Eyes                               SeleniumLibrary  5
    Scroll Into View                        ${GK_LOGO_LINK}
    Scroll Into View                        ${element}
    Wait Until Element Is Visible           ${element}
    Capture Element                         ${element}    name=${name}
    Compare Images

# Post User Avatar
#     Sign In ${USER_1.name} Fast
#     Go To User ${1} url
#     Upload picture via button                       ${USER_PROFILE_DROPZONE}                ${USER_PROFILE_DROPZONE_IMAGE}      ${CURDIR}/sample-picture1.jpg

Post GeoKret Avatar
    [Arguments]    ${file}=${CURDIR}/sample-picture.jpg    ${position}=1
    Location Should Contain                 ${PAGE_HOME_URL_EN}/geokrety/
    Upload picture via button               ${GEOKRET_DETAILS_AVATAR_DROPZONE}
    ...                                     ${GEOKRET_DETAILS_AVATAR_IMAGES}
    ...                                     ${file}
    ...                                     ${position}

Post GeoKret Avatar Via Drag/Drop
    [Arguments]    ${source}    ${position}=1
    Location Should Contain                 ${PAGE_HOME_URL_EN}/geokrety/
    Upload picture via via Drag/Drop        ${GEOKRET_DETAILS_AVATAR_DROPZONE}
    ...                                     ${GEOKRET_DETAILS_AVATAR_IMAGES}
    ...                                     ${source}
    ...                                     ${position}

# Post Move Picture
#     Post Move                                       ${MOVE_1}
#     Sign In ${USER_1.name} Fast
#     Go To GeoKrety ${1} url
#     Upload picture via button                       ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_DROPZONE}    ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}    ${CURDIR}/sample-picture4.jpg

# Upload user avatar via button
#     [Arguments]    ${user_profile_url}    ${file}    ${count}=1
#     Go To                                   ${user_profile_url}
#     Wait Until Page Contains Element        ${USER_PROFILE_DROPZONE_PICTURE_UPLOAD_BUTTON}    timeout=30
#     Choose File    	                        //*[@type="file"]    ${file}
#     # Run Keyword And Continue On Failure     Wait Until Page Contains Element        ${USER_PROFILE_DROPZONE_IMAGE}\[${count}]${DROPZONE_PROCESSING_SUFFIX}    timeout=30  ## Errors caused by invalid syntax, timeouts, or fatal exceptions are not caught by this keyword.
#     Wait Until Page Contains Element        ${USER_PROFILE_DROPZONE_IMAGE}\[${count}]${DROPZONE_PROCESSED_SUFFIX}     timeout=30

# Upload user avatar via Drag/Drop - same page
#     [Arguments]    ${user_profile_url}    ${source}    ${count}=1
#     Go To                                   ${user_profile_url}
#     Wait Until Page Contains Element        ${USER_PROFILE_DROPZONE_PICTURE_UPLOAD_BUTTON}    timeout=30
#     Drag And Drop                           ${source}    ${USER_PROFILE_DROPZONE}
#     # Run Keyword And Continue On Failure     Wait Until Page Contains Element        ${USER_PROFILE_DROPZONE_IMAGE}\[${count}]${DROPZONE_PROCESSING_SUFFIX}    timeout=30  ## Errors caused by invalid syntax, timeouts, or fatal exceptions are not caught by this keyword.
#     Wait Until Page Contains Element        ${USER_PROFILE_DROPZONE_IMAGE}\[${count}]${DROPZONE_PROCESSED_SUFFIX}     timeout=30

Upload picture via button
    [Arguments]    ${dropzone}    ${results}    ${file}    ${position}=1
    Wait Until Page Contains Element        ${dropzone}
    Scroll Into View                        ${dropzone}
    Choose File    	                        ${dropzone}//*[@type="file"]       ${file}
    Wait Until Page Contains Element        ${results}\[${position}]${DROPZONE_PROCESSED_SUFFIX}     timeout=5

Upload picture via via Drag/Drop
    [Arguments]    ${dropzone}    ${results}    ${source}    ${position}=1
    Wait Until Page Contains Element        ${dropzone}
    Scroll Into View                        ${dropzone}
    Drag And Drop                           ${source}    ${dropzone}
    Wait Until Page Contains Element        ${results}\[${position}]${DROPZONE_PROCESSED_SUFFIX}     timeout=5

Drag And Drop
    [Arguments]     ${src}    ${tgt}
    ${js}        Get File              acceptance/ressources/drag-n-drop.js
    ${result}    Execute Javascript    ${js}; return DragNDrop('${src}', '${tgt}');

Click Picture Action
    [Arguments]    ${image}    ${button}
    Wait Until Page Contains Element        ${image}${PICTURE_PULLER}
    Scroll Into View                        ${image}${PICTURE_PULLER}
    Mouse Over                              ${image}${PICTURE_PULLER}
    Mouse Over                              ${image}${button}
    Click Button                            ${image}${button}
