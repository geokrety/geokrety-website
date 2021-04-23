
*** Variables ***
${PAGE_SEED_USER}                       ${GK_URL}/devel/db/users/seed
${PAGE_SEED_GEOKRETY}                   ${GK_URL}/devel/db/geokrety/seed
${PAGE_SEED_GEOKRETY_OWNED_BY_USER_1}   ${GK_URL}/devel/db/users/1/geokrety/seed
${PAGE_SEED_GEOKRETY_OWNED_BY_USER_2}   ${GK_URL}/devel/db/users/2/geokrety/seed
${PAGE_SEED_WAYPOINT_OC}                ${GK_URL}/devel/db/waypoint/oc
${PAGE_SEED_WAYPOINT_GC}                ${GK_URL}/devel/db/waypoint/gc
${PAGE_SEED_NEWS}                       ${GK_URL}/devel/db/news/seed
${PAGE_SEED_PICTURE_AVATAR}             ${GK_URL}/devel/db/users/1/avatar/1

${PAGE_LOGIN_USER}                      ${GK_URL}/devel/users/\${params.username}/login

${PAGE_DEV_MAILBOX_URL}                 ${GK_URL}/devel/mail
${PAGE_DEV_MAILBOX_FIRST_MAIL_URL}      ${GK_URL}/devel/mail/0
${PAGE_DEV_MAILBOX_SECOND_MAIL_URL}     ${GK_URL}/devel/mail/1
${PAGE_DEV_MAILBOX_THIRD_MAIL_URL}      ${GK_URL}/devel/mail/2
${PAGE_DEV_MAILBOX_FOURTH_MAIL_URL}     ${GK_URL}/devel/mail/3
${PAGE_DEV_MAILBOX_CLEAR_URL}           ${GK_URL}/devel/mail/delete/all
${PAGE_DEV_RESET_DB_URL}                ${GK_URL}/devel/db/reset

${PAGE_HOME_URL}                        ${GK_URL}/en
${PAGE_HOME_URL_FR}                     ${GK_URL}/fr
${PAGE_TERMS_OF_USE_URL}                ${GK_URL}/en/terms-of-use
${PAGE_REGISTER_URL}                    ${GK_URL}/en/registration
${PAGE_SIGN_IN_URL}                     ${GK_URL}/en/login
${PAGE_SIGN_OUT_URL}                    ${GK_URL}/en/logout
${PAGE_NEWS_LIST_URL}                   ${GK_URL}/en/news
${PAGE_NEWS_URL}                        ${GK_URL}/en/news/\${params.newsid}
${PAGE_MOVES_URL}                       ${GK_URL}/en/moves
${PAGE_MOVES_FROM_INVENTORY_URL}        ${GK_URL}/en/moves/select-from-inventory
${PAGE_PICTURES_GALLERY_URL}            ${GK_URL}/en/picture/gallery

${PAGE_MOVES_EDIT_URL}                  ${GK_URL}/en/moves/\${params.moveid}/edit
${PAGE_MOVES_COMMENT_URL}               ${GK_URL}/en/moves/\${params.moveid}/comment
${PAGE_MOVES_COMMENT_MISSING_URL}       ${GK_URL}/en/moves/\${params.moveid}/missing
${PAGE_MOVES_DELETE_URL}                ${GK_URL}/en/moves/\${params.moveid}/delete

${PAGE_USER_1_PROFILE_URL}              ${GK_URL}/en/users/1
${PAGE_USER_2_PROFILE_URL}              ${GK_URL}/en/users/2
${PAGE_USER_1_PROFILE_URL_FR}           ${GK_URL}/fr/users/1
${PAGE_USER_X_PROFILE_URL}              ${GK_URL}/en/users/\${params.userid}

${PAGE_USER_RECENT_MOVES_URL}           ${GK_URL}/en/users/\${params.userid}/recent-moves
${PAGE_USER_INVENTORY_URL}              ${GK_URL}/en/users/\${params.userid}/inventory
${PAGE_USER_WATCHED_GEOKRETY_URL}       ${GK_URL}/en/users/\${params.userid}/watched-geokrety
${PAGE_USER_OWNED_GEOKRETY_URL}         ${GK_URL}/en/users/\${params.userid}/owned-geokrety
${PAGE_USER_OWNED_GEOKRETY_RECENT_MOVES_URL}    ${GK_URL}/en/users/\${params.userid}/owned/recent-moves
${PAGE_USER_POSTED_PICTURES_URL}                ${GK_URL}/en/users/\${params.userid}/pictures
${PAGE_USER_OWNED_GEOKRETY_PICTURES_URL}        ${GK_URL}/en/users/\${params.userid}/owned/pictures

${PAGE_USER_1_BANER_TEMPLATE_URL}       ${GK_URL}/en/users/1/choose-statpic-template
${PAGE_USER_1_OBSERVATION_AREA_URL}     ${GK_URL}/en/users/1/observation-area
${PAGE_USER_CHANGE_PASSWORD_URL}        ${GK_URL}/en/user/update-password
${PAGE_USER_CHANGE_LANGUAGE_URL}        ${GK_URL}/en/user/preferred-language
${PAGE_USER_REFRESH_SECID_URL}          ${GK_URL}/en/user/refresh-secid
${PAGE_USER_CHANGE_EMAIL_URL}           ${GK_URL}/en/user/email/update

${PAGE_USER_EMAIL_CHANGE_VALIDATE_URL}  ${GK_URL}/en/user/email/change/validate

${PAGE_USER_CONTACT_URL}                ${GK_URL}/en/users/\${params.userid}/contact
${PAGE_USER_1_CONTACT_URL}              ${GK_URL}/en/users/1/contact
${PAGE_USER_2_CONTACT_URL}              ${GK_URL}/en/users/2/contact
${PAGE_USER_3_CONTACT_URL}              ${GK_URL}/en/users/3/contact

${PAGE_PASSWORD_RECOVERY_URL}                     ${GK_URL}/en/password-recovery
${PAGE_PASSWORD_RECOVERY_RESET_PASSWORD_URL}      ${GK_URL}/en/recover-password/

${PAGE_GEOKRETY_CREATE_URL}             ${GK_URL}/en/geokrety/create
${PAGE_GEOKRETY_CLAIM_URL}              ${GK_URL}/en/geokrety/claim

${PAGE_GEOKRETY_DETAILS_URL}            ${GK_URL}/en/geokrety/\${params.gkid}
${PAGE_GEOKRETY_1_DETAILS_URL}          ${GK_URL}/en/geokrety/GK0001
${PAGE_GEOKRETY_2_DETAILS_URL}          ${GK_URL}/en/geokrety/GK0002
${PAGE_GEOKRETY_3_DETAILS_URL}          ${GK_URL}/en/geokrety/GK0003

${PAGE_GEOKRETY_DETAILS_CONTACT_OWNER_URL}          ${GK_URL}/en/geokrety/\${params.gkid}/contact-owner
${PAGE_GEOKRETY_DETAILS_1_CONTACT_OWNER_URL}          ${GK_URL}/en/geokrety/GK0001/contact-owner
${PAGE_GEOKRETY_DETAILS_2_CONTACT_OWNER_URL}          ${GK_URL}/en/geokrety/GK0002/contact-owner
${PAGE_GEOKRETY_DETAILS_3_CONTACT_OWNER_URL}          ${GK_URL}/en/geokrety/GK0003/contact-owner

${PAGE_SEARCH_BY_WAYPOINT_URL}          ${GK_URL}/en/search/waypoint/\${params.waypoint}

${PAGE_LEGACY_INDEX_URL}                ${GK_URL}/index.php
${PAGE_LEGACY_LONGIN_URL}               ${GK_URL}/longin.php
${PAGE_LEGACY_SEARCH_BY_WAYPOINT_URL}   ${GK_URL}/szukaj.php
${PAGE_LEGACY_GKT_INVENTORY_URL}        ${GK_URL}/gkt/v3/inventory
${PAGE_LEGACY_GKT_SEARCH_URL}           ${GK_URL}/gkt/v3/search
${PAGE_LEGACY_API_EXPORT_URL}           ${GK_URL}/api/v1/export
${PAGE_LEGACY_API_EXPORT2_URL}          ${GK_URL}/api/v1/export2
${PAGE_LEGACY_API_EXPORT_OC_URL}        ${GK_URL}/api/v1/export_oc
