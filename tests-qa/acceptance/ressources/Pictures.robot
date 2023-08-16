*** Settings ***
Resource          CustomActions.robot
Resource          ComponentsLocator.robot
Library           SeleniumLibrary  timeout=10  implicit_wait=0
Library           RobotEyes
Library 	      OperatingSystem

*** Variables ***

${PICTURES_DIR}      ${CURDIR}/pictures
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

Post GeoKret Avatar
    [Arguments]    ${file}=pictures/sample-picture.jpg    ${position}=1
    Location Should Contain                 ${PAGE_HOME_URL_EN}/geokrety/
    Upload picture via button               ${GEOKRET_DETAILS_AVATAR_DROPZONE}
    ...                                     ${GEOKRET_DETAILS_AVATAR_IMAGES}
    ...                                     ${file}
    ...                                     ${position}

Post GeoKret Avatar Via Drag/Drop
    [Arguments]    ${source}    ${position}=1
    Location Should Contain                 ${PAGE_HOME_URL_EN}/geokrety/
    Upload picture via Drag/Drop            ${GEOKRET_DETAILS_AVATAR_DROPZONE}
    ...                                     ${GEOKRET_DETAILS_AVATAR_IMAGES}
    ...                                     ${source}
    ...                                     ${position}

Post User Avatar
    [Arguments]    ${file}=pictures/sample-picture.jpg    ${position}=1
    Location Should Contain                 ${PAGE_HOME_URL_EN}/users/
    Upload picture via button               ${USER_PROFILE_DROPZONE}
    ...                                     ${USER_PROFILE_DROPZONE_IMAGE}
    ...                                     ${file}
    ...                                     ${position}

Post User Avatar Via Drag/Drop
    [Arguments]    ${source}    ${position}=1
    Location Should Contain                 ${PAGE_HOME_URL_EN}/users/
    Upload picture via Drag/Drop            ${USER_PROFILE_DROPZONE}
    ...                                     ${USER_PROFILE_DROPZONE_IMAGE}
    ...                                     ${source}
    ...                                     ${position}


Post Move Picture
    [Arguments]    ${file}=pictures/sample-picture.jpg    ${move_number}=1    ${position}=1
    Location Should Contain                 ${PAGE_HOME_URL_EN}/geokrety/
    ${move} =    Get Move ${move_number} XPath
    Upload picture via button               ${move}${GEOKRET_MOVE_DROPZONE}
    ...                                     ${move}${GEOKRET_MOVE_IMAGES}
    ...                                     ${file}
    ...                                     ${position}


# Post Move Picture
#     Post Move                                       ${MOVE_1}
#     Sign In ${USER_1.name} Fast
#     Go To GeoKrety ${1} url
#     Upload picture via button                       ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_DROPZONE}    ${GEOKRET_DETAILS_MOVE_1}${GEOKRET_MOVE_IMAGES}    pictures/sample-picture4.jpg

Upload Picture Via Button Base
    [Arguments]    ${dropzone}    ${results}    ${file}
    Wait Until Page Contains Element        ${dropzone}
    Scroll Into View                        ${dropzone}
    Choose File    	                        ${dropzone}//*[@type="file"]       ${file}

Upload Picture Via Button
    [Arguments]    ${dropzone}    ${results}    ${file}    ${position}=1
    Upload Picture via Button Base    ${dropzone}    ${results}    ${file}
    TRY
        Wait Until Page Contains Element        ${results}\[${position}]//span[@class="picture-message"]     timeout=1
    EXCEPT
        No Operation
    END
    Wait Until Page Contains Element        ${results}\[${position}]${DROPZONE_PROCESSED_SUFFIX}     timeout=10

Upload Picture Via Drag/Drop
    [Arguments]    ${dropzone}    ${results}    ${source}    ${position}=1
    Wait Until Page Contains Element        ${dropzone}
    Scroll Into View                        ${dropzone}
    Drag And Drop                           ${source}    ${dropzone}
    TRY
        Wait Until Page Contains Element        ${results}\[${position}]//span[@class="picture-message"]     timeout=1
    EXCEPT
        No Operation
    END
    Wait Until Page Contains Element        ${results}\[${position}]${DROPZONE_PROCESSED_SUFFIX}     timeout=10

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
