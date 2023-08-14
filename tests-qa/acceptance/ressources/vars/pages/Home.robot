*** Settings ***

*** Variables ***
${HOME_FOUND_GK_TRACKING_CODE_INPUT}        //*[@id="tracking_code"]
${HOME_FOUND_GK_TRACKING_CODE_BUTTON}       //*[@id="found-geokret-submit"]
${HOME_PICTURE_LIST_PANEL}                  //*[@id="recentPicturesPanel"]
${HOME_PICTURE_LIST_GALERY}                 ${HOME_PICTURE_LIST_PANEL}//div[contains(@class, "gallery")]
${HOME_PICTURE_LIST_PICTURES}               ${HOME_PICTURE_LIST_GALERY}//div[contains(@class, "gallery")]
${HOME_MOVES_PANEL}                         //*[@id="recentMovesPanel"]
${HOME_LATEST_MOVES_TABLE}                  ${HOME_MOVES_PANEL}/table
${HOME_NEWS_PANELS}                         //*[@data-gk-type="news"]

${FOOTER_HOME}       //*[@id="footer-home"]
${FOOTER_HELP}       //*[@id="footer-help"]
${FOOTER_NEWS}       //*[@id="footer-news"]
${FOOTER_CONTACT}    //*[@id="footer-contact"]
${FOOTER_LICENSE}    //*[@id="footer-license"]
${FOOTER_FACEBOOK}   //*[@id="footer-facebook"]
${FOOTER_TWITTER}    //*[@id="footer-twitter"]
${FOOTER_INSTAGRAM}  //*[@id="footer-instagram"]

*** Keywords ***
