import os
import sys

# Define Default Timezone for all tests
# No Daylight saving - UTC+03:00
os.environ["TZ"] = "Africa/Nairobi"

GK_TIMEOUT_MINUTES = 2
BROWSER = "Firefox"
PROJECT_NAME = "GeoKrety"

GK_URL = os.getenv('GEOKRETY_URL', 'http://localhost:3001/')
BS_ENABLED = os.getenv('BS_ENABLED', False)
BS_USERNAME = os.getenv('BS_USERNAME')
BS_TOKEN = os.getenv('BS_TOKEN')
BS_LOCAL = os.getenv('BS_LOCAL', 'false')
BS_LOCAL_ID = os.getenv('BS_LOCAL_ID')
BS_BUILD = os.getenv('BS_BUILD', 'local')
HEADLESS = os.getenv('HEADLESS', False)

if GK_URL is None:
    print('E: Please define variable GEOKRETY_URL')
    sys.exit()

if GK_URL.endswith('/'):
    GK_URL = GK_URL.rstrip('/')

BS_HUB = None
if not BS_ENABLED or BS_ENABLED == '0' or BS_ENABLED.lower() == 'false' :
    BS_ENABLED = 'false'
else:
    if BS_USERNAME is None or BS_TOKEN is None:
        print('E: BrowserStack enabled, but it need BS_USERNAME and BS_TOKEN')
        sys.exit()
    else:
        BS_HUB = "https://" + BS_USERNAME + ":" + BS_TOKEN + "@hub.browserstack.com/wd/hub"
        print("I: * BrowserStack enabled - bs_username:{}".format(BS_USERNAME))

    if BS_LOCAL != 'false':
        BS_LOCAL = 'true'
        print("I: * BrowserStack Local enabled - BrowserStack local agent needed")

    if BS_LOCAL_ID is None:
        print('E: BrowserStack enabled, but it need BS_LOCAL_ID to be defined and match `BrowserStack local agent` configuration')
        sys.exit()
    print("I: * BrowserStack BS_LOCAL_ID:{}".format(BS_LOCAL_ID))

print("I: * Starting robot test - target {}".format(GK_URL))
