import os

# Set a default value in case pabot is not used
PABOTEXECUTIONPOOLID = 0

# Define Default Timezone for all tests
# No Daylight saving - UTC+03:00
os.environ["TZ"] = "Africa/Nairobi"

# This is the host that will be used as base during tests
# Port will be detemined by concurrency and dynamically set PABOTEXECUTIONPOOLID
GK_FQDN = os.getenv('GK_FQDN', 'localhost')

# Path to the reference image files used by RobotEyes
images_dir = 'visual_images'
