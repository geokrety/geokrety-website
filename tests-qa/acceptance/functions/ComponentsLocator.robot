*** Variables ***
# LBL = Labels ; BTN = Button ; TXT = Text Fields ; LST = List ; ELT = Element ;

################
#   COMMON V2
################

${GK_LOGO_LINK}     //*[@id="home-logo"]

${MODAL_DIALOG}                     //div[@class="modal-dialog"]
${MODAL_DIALOG_TITLE}               ${MODAL_DIALOG}//*[@id="modalLabel"]
${MODAL_DIALOG_SUBMIT_BUTTON}       ${MODAL_DIALOG}//div[contains(@class, "modal-footer")]/button[@type="submit"]
${MODAL_DIALOG_DISMISS_BUTTON}      ${MODAL_DIALOG}//div[contains(@class, "modal-footer")]/button[@data-dismiss="modal"]

${MODAL_PANEL}                      //div[contains(@class, "panel")]
${MODAL_PANEL_TITLE}                ${MODAL_PANEL}//*[@id="modalLabel"]
${MODAL_PANEL_SUBMIT_BUTTON}        ${MODAL_PANEL}//div[contains(@class, "modal-footer")]/button[@type="submit"]
${MODAL_PANEL_DISMISS_BUTTON}       ${MODAL_PANEL}//div[contains(@class, "modal-footer")]/button[@data-dismiss="modal"]

${DROPDOWN_LANG}     //*[@id="navbar-lang"]
${DROPDOWN_LANG_EN}  //*[@id="navbar-lang-en"]
${DROPDOWN_LANG_FR}  //*[@id="navbar-lang-fr"]

${FOOTER_HOME}       //*[@id="footer-home"]
${FOOTER_HELP}       //*[@id="footer-help"]
${FOOTER_NEWS}       //*[@id="footer-news"]
${FOOTER_CONTACT}    //*[@id="footer-contact"]
${FOOTER_LICENSE}    //*[@id="footer-license"]
${FOOTER_FACEBOOK}   //*[@id="footer-facebook"]
${FOOTER_TWITTER}    //*[@id="footer-twitter"]
${FOOTER_INSTAGRAM}  //*[@id="footer-instagram"]

${PICTURE_PULLER}    //div[contains(@class, "pictures-actions-pull")]
${PICTURE_PULLER_SET_AS_AVATAR_BUTTON}      //button[@data-type="define-as-main-avatar"]
${PICTURE_PULLER_EDIT_BUTTON}               //button[@data-type="picture-edit"]
${PICTURE_PULLER_DELETE_BUTTON}             //button[@data-type="picture-delete"]

${DROPZONE_PROCESSING_SUFFIX}               //span[@class="picture-message"]
${DROPZONE_PROCESSED_SUFFIX}                //a[@class="picture-link"]

${PAGINATOR}            //ul[@data-gk-type="paginator"]

################
# WELCOME PAGE
################
${HOME_FOUND_GK_TRACKING_CODE_INPUT}        //*[@id="tracking_code"]
${HOME_FOUND_GK_TRACKING_CODE_BUTTON}       //*[@id="found-geokret-submit"]
${HOME_PICTURE_LIST_PANEL}                  //*[@id="recentPicturesPanel"]
${HOME_PICTURE_LIST_GALERY}                 ${HOME_PICTURE_LIST_PANEL}//div[contains(@class, "gallery")]
${HOME_PICTURE_LIST_PICTURES}               ${HOME_PICTURE_LIST_GALERY}//div[contains(@class, "gallery")]
${HOME_MOVES_PANEL}                         //*[@id="recentMovesPanel"]
${HOME_LATEST_MOVES_TABLE}                  ${HOME_MOVES_PANEL}/table
${HOME_NEWS_PANELS}                         //*[@data-gk-type="news"]

################
# NAVBAR
################
${NAVBAR}                                   //body/nav
${NAVBAR_SIGN_IN_LINK}                      //*[@id="navbar-profile-login"]
${NAVBAR_REGISTER_LINK}                     //*[@id="navbar-profile-register"]
${NAVBAR_MOVE_LINK}                         //*[@id="navbar-move"]
${NAVBAR_DEV_MAILBOX_LINK}                  //*[@id="navbar-localmail"]
${NAVBAR_DEV_MAILBOX_COUNTER}               //*[@id="navbar-localmail"]/span[@class="badge"]
${NAVBAR_PROFILE_LINK}                      //*[@id="navbar-profile-user"]
${NAVBAR_SIGN_OUT_LINK}                     //*[@id="navbar-profile-user-logout"]
${NAVBAR_ACTIONS_LINK}                      //*[@id="navbar-actions"]
${NAVBAR_ACTIONS_MOVE_LINK}                 //*[@id="navbar-actions-move"]
${NAVBAR_ACTIONS_CREATE_GEOKRET_LINK}       //*[@id="navbar-actions-create"]
${NAVBAR_ACTIONS_CLAIM_GEOKRET_LINK}        //*[@id="navbar-actions-claim"]
${NAVBAR_ACTIONS_SEARCH_LINK}               //*[@id="navbar-actions-search"]
${NAVBAR_ACTIONS_PHOTO_GALLERY_LINK}        //*[@id="navbar-actions-gallery"]

################
# DEV_MAILBOX
################
${DEV_MAILBOX_DELETE_ALL_MAILS_BUTTON}      //*[@id="deleteAllMailsButton"]
${DEV_MAILBOX_MAILS_TABLE_ROWS}             //*[@id="mailsTable"]/tbody/tr
${DEV_MAILBOX_FIRST_MAIL_LINK}              //*[@id="mailsTable"]/tbody/tr/td[@class="mail_id" and text()="0"]/parent::tr//a[contains(@class, "displayMailLink")]
${DEV_MAILBOX_SECOND_MAIL_LINK}             //*[@id="mailsTable"]/tbody/tr/td[@class="mail_id" and text()="1"]/parent::tr//a[contains(@class, "displayMailLink")]
${DEV_MAILBOX_FIRST_MAIL_DELETE_LINK}       //*[@id="mailsTable"]/tbody/tr/td[@class="mail_id" and text()="0"]/parent::tr//a[contains(@class, "deleteMailLink")]
${DEV_MAILBOX_SECOND_MAIL_DELETE_LINK}      //*[@id="mailsTable"]/tbody/tr/td[@class="mail_id" and text()="1"]/parent::tr//a[contains(@class, "deleteMailLink")]

# Is it possible to have something like that, when mail_id is not yet defined
# ${DEV_MAILBOX_MAIL_DELETE_BY_ID_LINK}       //*[@id="mailsTable"]/tbody/tr/td[@class="mail_id" and text()="${mail_id}"]/parent::tr//a[contains(@class, "deleteMailLink")]

################
# SIGN IN FORM
################
${SIGN_IN_FORM_USERNAME_INPUT}              //div[@class="modal-dialog"]//form//input[@name="login"]
${SIGN_IN_FORM_PASSWORD_INPUT}              //div[@class="modal-dialog"]//form//input[@name="password"]
${SIGN_IN_FORM_REMEMBER_ME_CHECKBOX}        //div[@class="modal-dialog"]//form//input[@name="remember"]
${SIGN_IN_FORM_SIGN_IN_BUTTON}              //div[@class="modal-dialog"]//form//button[@type="submit"]

################
# USER REGISTRATION FORM
################

${REGISTRATION_REGISTER_BUTTON}             //*[@id="registerButton"]
${REGISTRATION_USERNAME_INPUT}              //*[@id="usernameInput"]
${REGISTRATION_EMAIL_INPUT}                 //*[@id="emailInput"]
${REGISTRATION_PASSWORD_INPUT}              //*[@id="passwordInput"]
${REGISTRATION_PASSWORD_CONFIRM_INPUT}      //*[@id="passwordConfirmInput"]
${REGISTRATION_PREFERRED_LANGUAGE_SELECT}   //*[@id="preferredLanguageInput"]
${REGISTRATION_DAILY_MAIL_CHECKBOX}         //*[@id="dailyMailsInput"]
${REGISTRATION_TERMS_OF_USE_CHECKBOX}       //*[@id="termsOfUseInput"]


################
# GEOKRETY CREATE FORM
################

${GEOKRET_CREATE_CREATE_BUTTON}             //*[@id="createOrUpdateSubmitButton"]
${GEOKRET_CREATE_NAME_INPUT}                //*[@id="inputName"]
${GEOKRET_CREATE_TYPE_SELECT}               //*[@id="inputGeokretType"]
${GEOKRET_CREATE_MISSION_INPUT}             //*[@id="inputMission"]
${GEOKRET_CREATE_LABEL_TEMPLATE_SELECT}     //*[@id="inputLabelTemplate"]


################
# GEOKRETY DETAILS
################

${GEOKRET_DETAILS_DETAILS_PANEL}                //*[@id="geokretyDetailsPanel"]
${GEOKRET_DETAILS_DETAILS_PANEL_HEADING}        ${GEOKRET_DETAILS_DETAILS_PANEL}/div[contains(@class, "panel-heading")]
${GEOKRET_DETAILS_DETAILS_PANEL_BODY}           ${GEOKRET_DETAILS_DETAILS_PANEL}/div[contains(@class, "panel-body")]

${GEOKRET_DETAILS_NAME}                         ${GEOKRET_DETAILS_DETAILS_PANEL}/div[@class="panel-heading"]/a[@data-gk-link="geokret"]
${GEOKRET_DETAILS_TYPE}                         ${GEOKRET_DETAILS_DETAILS_PANEL}/div[@class="panel-heading"]/small
${GEOKRET_DETAILS_TYPE_IMG}                     ${GEOKRET_DETAILS_DETAILS_PANEL}/div[@class="panel-heading"]/img
${GEOKRET_DETAILS_OWNER}                        ${GEOKRET_DETAILS_DETAILS_PANEL}/div[@class="panel-heading"]/a[@data-gk-link="user"]

${GEOKRET_DETAILS_TRACKING_CODE}                ${GEOKRET_DETAILS_DETAILS_PANEL_BODY}//dd[@class="geokret-tracking-code"]
${GEOKRET_DETAILS_TRACKING_CODE_LABEL}          ${GEOKRET_DETAILS_DETAILS_PANEL_BODY}//dt[text()="Tracking Code"]

${GEOKRET_DETAILS_REF_NUMBER}                   ${GEOKRET_DETAILS_DETAILS_PANEL_BODY}//dd[@class="geokret-id"]
${GEOKRET_DETAILS_REF_NUMBER_LABEL}             ${GEOKRET_DETAILS_DETAILS_PANEL_BODY}//dt[text()="Reference number"]

${GEOKRET_DETAILS_DISTANCE}                     ${GEOKRET_DETAILS_DETAILS_PANEL_BODY}//dd[@class="geokret-distance"]
${GEOKRET_DETAILS_DISTANCE_LABEL}               ${GEOKRET_DETAILS_DETAILS_PANEL_BODY}//dt[text()="Total distance"]

${GEOKRET_DETAILS_CACHES_COUNT}                 ${GEOKRET_DETAILS_DETAILS_PANEL_BODY}//dd[@class="geokret-caches-count"]
${GEOKRET_DETAILS_CACHES_COUNT_LABEL}           ${GEOKRET_DETAILS_DETAILS_PANEL_BODY}//dt[text()="Places visited"]

${GEOKRET_DETAILS_CREATED_ON_DATETIME}          ${GEOKRET_DETAILS_DETAILS_PANEL_BODY}//dd[@class="geokret-created-on-datetime"]
${GEOKRET_DETAILS_CREATED_ON_DATETIME_LABEL}    ${GEOKRET_DETAILS_DETAILS_PANEL_BODY}//dt[text()="Born"]

${GEOKRET_DETAILS_MISSION}                      //*[@id="geokretyMissionPanel"]/div[contains(@class, "panel-body")]

${GEOKRET_DETAILS_FOUND_IT_TRACKING_CODE}       //*[@id="tracking_code"]
${GEOKRET_DETAILS_FOUND_IT_BUTTON}              //*[@id="foundItLogItButton"]

${GEOKRET_DETAILS_WATCH_LINK}                   //*[@id="geokretDetailsWatchLink"]
${GEOKRET_DETAILS_UNWATCH_LINK}                 //*[@id="geokretDetailsUnwatchLink"]
${GEOKRET_DETAILS_WATCHERS_LINK}                //*[@id="geokretDetailsWatchersLink"]
${GEOKRET_DETAILS_WATCHERS_COUNT_BADGE}         ${GEOKRET_DETAILS_WATCHERS_LINK}//span[contains(@class, "badge")]
${GEOKRET_DETAILS_LOG_THIS_GEOKRET_LINK}        //*[@id="geokretDetailsLogThisGeokretLink"]
${GEOKRET_DETAILS_TRANSFER_OWNERSHIP_LINK}      //*[@id="geokretDetailsOfferAdoptionLink"]
${GEOKRET_DETAILS_PRINT_LABEL_LINK}             //*[@id="geokretDetailsPrintLabelLink"]
${GEOKRET_DETAILS_ARCHIVE_LINK}                 //*[@id="geokretDetailsArchiveLink"]

${GEOKRET_DETAILS_MAP_PANEL}                    //*[@id="geokretyDetailsMapPanel"]
${GEOKRET_DETAILS_MAP}                          //*[@id="mapid"]
${GEOKRET_DETAILS_MOVES}                        //div[@data-gk-type="move"]
${GEOKRET_DETAILS_MOVE_1}                       //div[@data-gk-type="move" and @data-id="1"]
${GEOKRET_DETAILS_MOVE_2}                       //div[@data-gk-type="move" and @data-id="2"]
${GEOKRET_DETAILS_MOVE_3}                       //div[@data-gk-type="move" and @data-id="3"]

${GEOKRET_DETAILS_MOVES_EDIT_BUTTONS}               //a[@data-type="move-edit"]
${GEOKRET_DETAILS_MOVES_DELETE_BUTTONS}             //button[@data-type="move-delete"]
${GEOKRET_DETAILS_MOVES_PICTURE_UPLOAD_BUTTONS}     //button[@data-type="move-picture-upload"]
${GEOKRET_DETAILS_MOVES_COMMENT_BUTTONS}            //button[@data-type="move-comment" and @data-move-comment-type="comment"]
${GEOKRET_DETAILS_MOVES_MISSING_BUTTON}             //button[@data-type="move-comment" and @data-move-comment-type="missing"]

${GEOKRET_DETAILS_MOVES_COMMENTS_ALL_ITEMS}         //li[@data-type="move-comment"]
${GEOKRET_DETAILS_MOVES_COMMENTS_ITEMS}             //li[@data-type="move-comment" and @data-move-comment-id="\${commentid}"]
${GEOKRET_DETAILS_MOVES_COMMENTS_FIRST_ITEM}        //li[@data-type="move-comment" and @data-move-comment-id="1"]
${GEOKRET_DETAILS_MOVES_COMMENTS_SECOND_ITEM}       //li[@data-type="move-comment" and @data-move-comment-id="2"]
${GEOKRET_DETAILS_MOVES_COMMENTS_DELETE_BUTTON}     //li[@data-type="move-comment" and @data-move-comment-id="\${commentid}"]//button[@data-type="move-comment-delete"]
${GEOKRET_DETAILS_MOVES_COMMENTS_FIRST_AUTHOR}      //li[@data-type="move-comment" and @data-move-comment-id="1"]//*[contains(@class, "author")]
${GEOKRET_DETAILS_MOVES_COMMENTS_FIRST_COMMENT}     //li[@data-type="move-comment" and @data-move-comment-id="1"]/div/span[@class="move-comment"]

${GEOKRET_MOVE_COMMENT_PANEL}                       //div[@data-gk-type="move"]
${GEOKRET_MOVE_COMMENT_COMMENT_INPUT}               //*[@id="comment"]

${GEOKRET_DETAILS_AVATAR_DROPZONE}                            //div[contains(@class, "dropzone")]
${GEOKRET_DETAILS_AVATAR_DROPZONE_PICTURE_UPLOAD_BUTTON}      //button[contains(@class, "geokretAvatarUploadButton") and contains(@class, "dz-clickable")]
${GEOKRET_DETAILS_AVATAR_IMAGES}                              //*[@id="geokretPicturesList"]//div[contains(@class, "gallery")]/div[contains(@class, "gallery")]
${GEOKRET_DETAILS_AVATAR_FIRST_IMAGE}                         ${GEOKRET_DETAILS_AVATAR_IMAGES}\[1]
${GEOKRET_DETAILS_AVATAR_SECOND_IMAGE}                        ${GEOKRET_DETAILS_AVATAR_IMAGES}\[2]

${GEOKRET_MOVE_DROPZONE}                            //div[contains(@class, "dropzone")]
${GEOKRET_MOVE_DROPZONE_PICTURE_UPLOAD_BUTTON}      //button[contains(@class, "movePictureUploadButton") and contains(@class, "dz-clickable")]
${GEOKRET_MOVE_IMAGES}                              //div[contains(@class, "gallery")]/div[contains(@class, "gallery")]
${GEOKRET_MOVE_FIRST_IMAGE}                         ${GEOKRET_MOVE_IMAGES}\[1]
${GEOKRET_MOVE_SECOND_IMAGE}                        ${GEOKRET_MOVE_IMAGES}\[2]

################
# USER PROFILE PAGE
################

${CLAIM_TRACKING_CODE_INPUT}                        //*[@id="inputTrackingCode"]
${CLAIM_OWNER_CODE_INPUT}                           //*[@id="inputOwnerCode"]
${CLAIM_OWNER_CODE}                                 //*[@id="geokretDetailsAdoptionMessage"]/strong[1]

################
# USER PROFILE PAGE
################

${USER_PROFILE_DETAILS_PANEL}                   //*[@id="userDetailsPanel"]
${USER_PROFILE_PICTURES_PANEL}                  //*[@id="userPicturesList"]
${USER_PROFILE_AWARDS_PANEL}                    //*[@id="userAwardsPanel"]
${USER_PROFILE_BADGES_PANEL}                    //*[@id="userBadgesPanel"]
${USER_PROFILE_ACTIONS_PANEL}                   //*[@id="userActionsPanel"]
${USER_PROFILE_MINI_MAP_PANEL}                  //*[@id="userMiniMapPanel"]
${USER_PROFILE_STATPIC_PANEL}                   //*[@id="userStatpicPanel"]
${USER_PROFILE_DANGER_ZONE_PANEL}               //*[@id="userDangerZonePanel"]

${USER_PROFILE_DROPZONE}                        //*[@id="userAvatar" and contains(@class, "dropzone")]
${USER_PROFILE_DROPZONE_IMAGE}                  //*[@id="userPicturesList"]//div[@class="gallery"]/div[@class="gallery"]
${USER_PROFILE_DROPZONE_PICTURE_UPLOAD_BUTTON}  //*[@id="userAvatarUploadButton" and contains(@class, "dz-clickable")]
${USER_PROFILE_IMAGES}                          ${USER_PROFILE_PICTURES_PANEL}//div[contains(@class, "gallery")]//div[contains(@class, "gallery")]
${USER_PROFILE_FIRST_IMAGE}                     //*[@id="userPicturesList"]//div[@class="gallery"]/div[@class="gallery"][1]
${USER_PROFILE_SECOND_IMAGE}                    //*[@id="userPicturesList"]//div[@class="gallery"]/div[@class="gallery"][2]

${USER_PROFILE_CREATE_GEOKRET_BUTTON}           //*[@id="userProfileCreateGeokretButton"]

${USER_PROFILE_USERNAME_HEADER_BUTTONS}         ${USER_PROFILE_DETAILS_PANEL}/div[@class="panel-heading"]//div[@class="btn-group]/div[@class="btn-group]/*[@id="btn"]

${USER_PROFILE_ICON_IMAGE}                      ${USER_PROFILE_DETAILS_PANEL}/div[@class="panel-heading"]/img[@data-gk-type]

${USER_PROFILE_USERNAME}                        ${USER_PROFILE_DETAILS_PANEL}/div[@class="panel-heading"]/a[@data-gk-link="user"]

${USER_PROFILE_JOIN_TIME}                       ${USER_PROFILE_DETAILS_PANEL}/div[contains(@class, "panel-body")]//dd[@class="user-join-on-datetime"]
${USER_PROFILE_JOIN_TIME_LABEL}                 ${USER_PROFILE_DETAILS_PANEL}/div[contains(@class, "panel-body")]//dt[text()="Joined us"]

${USER_PROFILE_LANGUAGE}                        ${USER_PROFILE_DETAILS_PANEL}/div[contains(@class, "panel-body")]//dd[@class="user-language"]
${USER_PROFILE_LANGUAGE_LABEL}                  ${USER_PROFILE_DETAILS_PANEL}/div[contains(@class, "panel-body")]//dt[text()="Language"]

${USER_PROFILE_EMAIL}                           ${USER_PROFILE_DETAILS_PANEL}/div[contains(@class, "panel-body")]//dd[@class="user-email"]
${USER_PROFILE_EMAIL_LABEL}                     ${USER_PROFILE_DETAILS_PANEL}/div[contains(@class, "panel-body")]//dt[text()="Email"]

${USER_PROFILE_SECID}                           //*[@id="secid"]
${USER_PROFILE_SECID_LABEL}                     ${USER_PROFILE_DETAILS_PANEL}/div[contains(@class, "panel-body")]//dt[text()="Secid"]

${USER_PROFILE_GAINED_CREATED_AWARDS}           ${USER_PROFILE_AWARDS_PANEL}//span[@class="created-awards"]
${USER_PROFILE_GAINED_MOVED_AWARDS}             ${USER_PROFILE_AWARDS_PANEL}//span[@class="moves-awards"]

${USER_PROFILE_AVATAR_GALLERY}                  ${USER_PROFILE_DETAILS_PANEL}//div[contains(@class, "gallery")]
${USER_PROFILE_STATPIC_IMAGE}                   ${USER_PROFILE_STATPIC_PANEL}//img

${USER_PROFILE_DELETE_ACCOUNT_BUTTON}                      ${USER_PROFILE_DANGER_ZONE_PANEL}//*[@id="userAccountDeleteButton"]
${USER_PROFILE_DELETE_ACCOUNT_OPERATION_RESULT_INPUT}      //*[@id="operationInputResult"]
${USER_PROFILE_DELETE_ACCOUNT_REMOVE_CONTENT_CHECKBOX}     //*[@id="removeCommentContentCheckbox"]

${USER_PROFILE_LANGUAGE_EDIT_BUTTON}                    //*[@id="userLanguageUpdateButton"]
${USER_PROFILE_EMAIL_EDIT_BUTTON}                       //*[@id="userEmailUpdateButton"]
${USER_PROFILE_SECID_REFRESH_BUTTON}                    //*[@id="userSecidUpdateButton"]
${USER_PROFILE_RSS_FEED_BUTTON}                         //*[@id="userRssFeedButton"]
${USER_PROFILE_AUTHENTICATION_HISTORY_BUTTON}           //*[@id="userAuthenticationHistoryButton"]
${USER_PROFILE_CONTACT_BUTTON}                          //*[@id="userContactButton"]
${USER_PROFILE_PASSWORD_EDIT_BUTTON}                    //*[@id="userPasswordChangeButton"]
${USER_PROFILE_PICTURE_UPLOAD_BUTTON}                   //*[@id="userAvatarUploadButton"]
${USER_PROFILE_BANNER_EDIT_BUTTON}                      //*[@id="userBannerChangeButton"]
${USER_PROFILE_HOME_POSITION_EDIT_BUTTON}               //*[@id="userHomePositionEditButton"]
${USER_PROFILE_HOME_POSITION_EDIT_BUTTON_MINIMAP}       //*[@id="userHomePositionEditButtonMinimap"]

${USER_PROFILE_AVATAR_CAPTION_INPUT}            //*[@id="caption"]


################
# USER BANER TEMPLATE CHOOSER PAGE
################

${USER_BANER_TEMPLATE_CHOOSER_RADIO_GROUP}      statpic
${USER_BANER_TEMPLATE_CHOOSER_SUBMIT_BUTTON}    //*[@id="bannerTemplateSubmitButton"]

################
# USER OBSERVATION AREA
################

${USER_OBSERVATION_AREA_MAP}                    //*[@id="mapid"]
${USER_OBSERVATION_AREA_MAP_ID}                 #mapid
${USER_OBSERVATION_AREA_COORDINATES_INPUT}      //*[@id="inputCoordinates"]
${USER_OBSERVATION_AREA_RADIUS_INPUT}           //*[@id="inputRadius"]
${USER_OBSERVATION_AREA_SUBMIT}                 //*[@id="userObservationAreaSubmitButton"]

################
# USER PASSWORD
################

${USER_PASSWORD_OLD_INPUT}                      //*[@id="inputPasswordOld"]
${USER_PASSWORD_NEW_INPUT}                      //*[@id="inputPasswordNew"]
${USER_PASSWORD_CONFIRM_INPUT}                  //*[@id="inputPasswordConfirm"]

################
# USER PASSWORD RECOVERY
################

${USER_PASSWORD_RECOVERY_EMAIL_INPUT}           //*[@id="email"]
${USER_PASSWORD_RECOVERY_END_LINK_BUTTON}       //*[@id="sendRecoveryLinkButton"]
${USER_PASSWORD_RECOVERY_NEW_INPUT}             //*[@id="inputPasswordNew"]
${USER_PASSWORD_RECOVERY_CONFIRM_INPUT}         //*[@id="inputPasswordConfirm"]
${USER_PASSWORD_RECOVERY_CHANGE_BUTTON}         //*[@id="changePasswordButton"]

################
# USER LANGUAGE PREFERENCES
################

${USER_LANGUAGE_LANGUAGE_SELECT}                //*[@id="inputLanguage"]

################
# USER EMAIL PREFERENCES
################

${USER_EMAIL_EMAIL_INPUT}                       //*[@id="inputEmail"]
${USER_EMAIL_DAILY_MAIL_CHECKBOX}               //*[@id="dailyMailsCheckbox"]
${USER_EMAIL_VALIDATION_REFUSE_BUTTON}          //*[@id="emailChangeRefuseButton"]
${USER_EMAIL_VALIDATION_ACCEPT_BUTTON}          //*[@id="emailChangeAcceptButton"]
${USER_EMAIL_VALIDATION_DISMISS_BUTTON}         //*[@id="emailChangeDismissButton"]

################
# USER USERNAME CHANGE
################

${USER_CHANGE_USERNAME_INPUT}                   //*[@id="inputNewUsername"]

################
# USER RECENT MOVES PAGE
################

${USER_RECENT_MOVES_TABLE}                      //*[@id="userRecentMovesTable"]


################
# USER INVENTORY PAGE
################

${USER_INVENTORY_TABLE}                         //*[@id="userInventoryTable"]


################
# WATCH
################

${USER_WATCHERS_TABLE}                          //table[@id="geokretWatchersTable"]
${USER_WATCHED_TABLE}                           //table[@id="userWatchedTable"]
${USER_WATCHED_ROW_1_MOVE_LINK}                 ${USER_WATCHED_TABLE}//tbody//tr[1]//a[contains(@class, "move-link")]
${USER_WATCHED_ROW_1_UNWATCH_LINK}              ${USER_WATCHED_TABLE}//tbody//tr[1]//a[contains(@class, "unwatch-link")]


################
# USER OWNED GEOKRETY PAGE
################

${USER_OWNED_GEOKRETY_TABLE}                    //*[@id="userOwnedGeoKretyTable"]


################
# USER OWNED GEOKRETY RECENT MOVES PAGE
################

${USER_OWNED_GEOKRETY_RECENT_MOVES_TABLE}       //*[@id="userOwnedGeoKretyRecentMovesTable"]


################
# USER AUTHENTICATION HISTORY PAGE
################

${USER_AUTHENTICATION_HISTORY_TABLE}            //*[@id="userAuthenticationHistory"]

################
# USER CONTACT
################

${USER_CONTACT_USER_STATIC}                     //*[@id="contactedUser"]
${USER_CONTACT_SUBJECT_INPUT}                   //*[@id="inputSubject"]
${USER_CONTACT_MESSAGE_INPUT}                   //*[@id="message"]

################
# MOVES
################

${MOVE_TRACKING_CODE_PANEL}                     //*[@id="panelMoveGeoKret"]
${MOVE_TRACKING_CODE_PANEL_HEADER}              //*[@id="headingGeokret"]
${MOVE_TRACKING_CODE_PANEL_HEADER_TEXT}         //*[@id="geokretHeader"]
${MOVE_TRACKING_CODE_INPUT}                     //*[@id="nr"]
${MOVE_TRACKING_CODE_CHECK_BUTTON}              //*[@id="nrSearchButton"]
${MOVE_TRACKING_CODE_INVENTORY_BUTTON}          //*[@id="nrInventorySelectButton"]
${MOVE_TRACKING_CODE_RESULT_LIST}               //*[@id="nrResult"]
${MOVE_TRACKING_CODE_RESULTS_ITEMS}             //*[@id="nrResult"]/li
${MOVE_TRACKING_CODE_FIRST_RESULT_ITEM}         //*[@id="nrResult"]/li[1]
${MOVE_TRACKING_CODE_SECOND_RESULT_ITEM}        //*[@id="nrResult"]/li[2]
${MOVE_TRACKING_CODE_NEXT_BUTTON}               //*[@id="nrNextButton"]

${MOVE_INVENTORY_TABLE}                         //*[@id="geokretyListTable"]
${MOVE_INVENTORY_SELECT_BUTTON}                 //*[@id="modalInventorySelectButton"]
${MOVE_INVENTORY_SELECT_BUTTON_BADGE}           ${MOVE_INVENTORY_SELECT_BUTTON}/span[@class="badge"]
${MOVE_INVENTORY_SELECT_ALL_CHECKBOX}           //*[@id="geokretySelectAll"]
${MOVE_INVENTORY_ALL_ITEMS_CHECKBOX}            ${MOVE_INVENTORY_TABLE}//tr//input[@type="checkbox"]
${MOVE_INVENTORY_FILTER_INPUT}                  //*[@id="gk-filter"]


${MOVE_LOG_TYPE_PANEL}                          //*[@id="panelMoveLogType"]
${MOVE_LOG_TYPE_PANEL_HEADER}                   //*[@id="headingLogtype"]
${MOVE_LOG_TYPE_PANEL_HEADER_TEXT}              //*[@id="logTypeHeader"]
${MOVE_LOG_TYPE_RADIO_GROUP}                    logtype
${MOVE_LOG_TYPE_DROPPED_RADIO}                 //*[@id="logType0"]
${MOVE_LOG_TYPE_GRABBED_RADIO}                 //*[@id="logType1"]
${MOVE_LOG_TYPE_MEET_RADIO}                    //*[@id="logType3"]
${MOVE_LOG_TYPE_DIPPED_RADIO}                  //*[@id="logType5"]
${MOVE_LOG_TYPE_COMMENT_RADIO}                 //*[@id="logType2"]
${MOVE_LOG_TYPE_ARCHIVE_RADIO}                 //*[@id="logType4"]
${MOVE_LOG_TYPE_NEXT_BUTTON}                    //*[@id="logtypeNextButton"]


${MOVE_NEW_LOCATION_PANEL}                      //*[@id="panelLocation"]
${MOVE_NEW_LOCATION_PANEL_HEADER}               //*[@id="headingLocation"]
${MOVE_NEW_LOCATION_PANEL_HEADER_TEXT}          //*[@id="locationHeader"]
${MOVE_NEW_LOCATION_WAYPOINT_INPUT}             //*[@id="wpt"]
${MOVE_NEW_LOCATION_OC_BUTTON}                  //*[@id="wptSearchByNameButton"]
${MOVE_NEW_LOCATION_SEARCH_BUTTON}              //*[@id="wptSearchButton"]
${MOVE_NEW_LOCATION_NEXT_BUTTON}                //*[@id="locationNextButton"]

${MOVE_NEW_LOCATION_OC_INPUT}                   //*[@id="findbyCacheNameInput"]
${MOVE_NEW_LOCATION_OC_INPUT_TYPEAHEAD}         ${MOVE_NEW_LOCATION_OC_INPUT}/following-sibling::ul[contains(@class, "typeahead")]
${MOVE_NEW_LOCATION_OC_INPUT_TYPEAHEAD_ITEMS}   ${MOVE_NEW_LOCATION_OC_INPUT_TYPEAHEAD}/li

${MOVE_NEW_LOCATION_MAP_PANEL}                          //*[@id="mapField"]
${MOVE_NEW_LOCATION_MAP_PANEL_HEADER}                   ${MOVE_NEW_LOCATION_MAP_PANEL}/div[contains(@class, "panel-heading")]
${MOVE_NEW_LOCATION_MAP_COORDINATES_INPUT}              //*[@id="latlon"]
${MOVE_NEW_LOCATION_MAP_COORDINATES_SEARCH_BUTTON}      //*[@id="coordinatesSearchButton"]
${MOVE_NEW_LOCATION_MAP_COORDINATES_HELP_BUTTON}        //*[@id="geolocationButton"]
${MOVE_NEW_LOCATION_MAP_MAP}                            //*[@id="mapid"]

${MOVE_ADDITIONAL_DATA_PANEL}                       //*[@id="additionalDataPanel"]
${MOVE_ADDITIONAL_DATA_PANEL_HEADER}                //*[@id="headingMessage"]
${MOVE_ADDITIONAL_DATA_PANEL_HEADER_TEXT}           //*[@id="additionalDataHeader"]
${MOVE_ADDITIONAL_DATA_SUBMIT_BUTTON}               //*[@id="submitButton"]
${MOVE_ADDITIONAL_DATA_DATE_TIME_INPUT}             //*[@id="inputDate"]
${MOVE_ADDITIONAL_DATA_DATE_TIME_PICKER_BUTTON}     ${MOVE_ADDITIONAL_DATA_DATE_TIME_INPUT}/following-sibling::span[contains(@class, "input-group-addon")]
${MOVE_ADDITIONAL_DATA_DATE_TIME_PICKER_WIDGET}     ${MOVE_ADDITIONAL_DATA_DATE_TIME_INPUT}/following-sibling::div[contains(@class, "bootstrap-datetimepicker-widget")]
${MOVE_ADDITIONAL_DATA_DATE_TIME_PICKER_WIDGET_HOUR_PLUS}     ${MOVE_ADDITIONAL_DATA_DATE_TIME_PICKER_WIDGET}//a[@data-action="incrementHours"]
${MOVE_ADDITIONAL_DATA_DATE_HIDDEN_INPUT}           //*[@id="inputHiddenDate"]
${MOVE_ADDITIONAL_DATA_HOUR_HIDDEN_INPUT}           //*[@id="inputHiddenHour"]
${MOVE_ADDITIONAL_DATA_MINUTE_HIDDEN_INPUT}         //*[@id="inputHiddenMinute"]
${MOVE_ADDITIONAL_DATA_TIMEZONE_HIDDEN_INPUT}       //*[@id="inputHiddenTimezone"]
${MOVE_ADDITIONAL_DATA_USERNAME_INPUT}              //*[@id="username"]
${MOVE_ADDITIONAL_DATA_COMMENT_INPUT}               //*[@id="comment"]
${MOVE_ADDITIONAL_DATA_COMMENT_CODEMIRROR}          ${MOVE_ADDITIONAL_DATA_PANEL}//div[contains(@class, "CodeMirror")]


################
# GALLERY
################

${GALLERY_IMAGES}                                   //div[@class="gallery"]/div[@class="gallery"]
${GALLERY_FIRST_IMAGE}                              ${GALLERY_IMAGES}\[1]
${GALLERY_SECOND_IMAGE}                             ${GALLERY_IMAGES}\[2]
${GALLERY_THIRD_IMAGE}                              ${GALLERY_IMAGES}\[3]
${GALLERY_FOURTH_IMAGE}                             ${GALLERY_IMAGES}\[4]

################
# NEWS
################

${NEWS_PANEL}                                   //div[contains(@class, "panel") and @data-gk-type="news"]
${NEWS_PANEL_HEADING}                           ${NEWS_PANEL}//div[@class="panel-heading"]
${NEWS_PANEL_BODY}                              ${NEWS_PANEL}//div[contains(@class, "panel-body")]

${NEWS_TITLE}                                   ${NEWS_PANEL_HEADING}//h3//span[@class="title"]
${NEWS_AUTHOR}                                  ${NEWS_PANEL_HEADING}//i[@class="author"]
${NEWS_LINK}                                    ${NEWS_PANEL_HEADING}//a[@data-gk-type="news-link"]
${NEWS_COMMENTS_COUNT}                          ${NEWS_PANEL_HEADING}//span[@class="badge"]
${NEWS_CONTENT}                                 ${NEWS_PANEL_BODY}
${NEWS_SUBSCRIPTION_BUTTON}                     ${NEWS_PANEL_HEADING}//button[@data-type="news-subscription"]

${NEWS_COMMENT_FORM_PANEL}                       //*[@id="newsCommentPanel"]
${NEWS_COMMENT_FORM_PANEL_TITLE}                 ${NEWS_COMMENT_FORM_PANEL}/div[contains(@class, "panel-heading")]
${NEWS_COMMENT_FORM_PANEL_CONTENT}               ${NEWS_COMMENT_FORM_PANEL}/div[contains(@class, "panel-body")]
${NEWS_COMMENT_FORM_MESSAGE_INPUT}               //*[@id="content"]
${NEWS_COMMENT_FORM_SUBSCRIBE_CHECKBOX}          ${NEWS_COMMENT_FORM_PANEL}//*[@id="subscribe"]

${NEWS_COMMENT_PANEL}                           //*[@id="newsCommentPanel"]

${NEWS_COMMENTS}                                //div[@data-gk-type="news-comment"]
${NEWS_COMMENT_PANEL_HEADER}                    ${NEWS_COMMENTS}/div[contains(@class, "panel-heading")]
${NEWS_COMMENT_PANEL_CONTENT}                   ${NEWS_COMMENTS}/div[contains(@class, "panel-body")]
${NEWS_COMMENT_AUTHOR}                          ${NEWS_COMMENT_PANEL_HEADER}//a[@data-gk-link="user"]
${NEWS_COMMENT_DELETE_BUTTON}                   ${NEWS_COMMENT_PANEL_HEADER}//button[@data-type="news-comment-delete"]
${NEWS_COMMENT_FIRST_COMMENT}                   ${NEWS_COMMENTS}\[1]
${NEWS_COMMENT_SECOND_COMMENT}                  ${NEWS_COMMENTS}\[2]
${NEWS_COMMENT_THIRD_COMMENT}                   ${NEWS_COMMENTS}\[3]

################
# SEARCH
################

${SEARCH_NAVBAR_FORM}                          //nav//form[@id="formSearchAdvanced"]
${SEARCH_NAVBAR_INPUT}                         //nav//input[@id="inputSearchAdvanced"]
${SEARCH_NAVBAR_SUBMIT}                        //nav//button[@id="buttonSearchAdvancedType"]

${SEARCH_BY_WAYPOINT_TABLE}                    //*[@id="searchByWaypointTable"]
${SEARCH_BY_USER_TABLE}                        //*[@id="searchByUserTable"]
${SEARCH_BY_GEOKRETY_TABLE}                    //*[@id="searchByGeokretyTable"]

${SEARCH_NAVBAR_INPUT_TYPEAHEAD}               ${SEARCH_NAVBAR_FORM}//ul[contains(@class, "typeahead")]
