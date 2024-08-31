import os
import sys


def env2bool(env_name, default=False) -> bool:
    return f"{os.getenv(env_name, default)}".lower() in ["true"]


BROWSER = os.getenv("BROWSER", "Firefox").lower()
HEADLESS = env2bool("HEADLESS", False)
REMOTE_URL = os.getenv("REMOTE_URL")
AUTO_REMOTE_URL = env2bool("AUTO_REMOTE_URL", False)
RECORDING_ENABLED = env2bool("RECORDING_ENABLED", False)

# ###################
# ## REMOTE_URL    ##
# ###################

if AUTO_REMOTE_URL:
    REMOTE_URL = "http://127.0.0.1:4444/wd/hub"


# ###################
# ## BROWSER STACK ##
# ###################

BS_ENABLED = env2bool("BS_ENABLED", False)
BS_USERNAME = os.getenv('BS_USERNAME')
BS_TOKEN = os.getenv('BS_TOKEN')
BS_LOCAL = env2bool('BS_LOCAL', False)
BS_LOCAL_ID = os.getenv('BS_LOCAL_ID')
BS_BUILD = os.getenv('BS_BUILD', 'local')

if BS_ENABLED:
    if BS_USERNAME is None or BS_TOKEN is None:
        print('E: BrowserStack enabled, but it need BS_USERNAME and BS_TOKEN')
        sys.exit()

    REMOTE_URL = f"https://{BS_USERNAME}:{BS_TOKEN}@hub.browserstack.com/wd/hub"
    print(f"I: * BrowserStack enabled - bs_username:{BS_USERNAME}")

    if BS_LOCAL:
        print("I: * BrowserStack Local enabled - BrowserStack local agent needed")
    elif BS_LOCAL_ID is None:
        print('E: BrowserStack enabled, but it needs BS_LOCAL_ID to be defined and match `BrowserStack local agent` configuration')
        sys.exit()
    print(f"I: * BrowserStack BS_LOCAL_ID:{BS_LOCAL_ID}")
