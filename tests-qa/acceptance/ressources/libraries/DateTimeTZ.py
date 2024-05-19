__version__ = '0.1'

import pytz
from robot.libraries.DateTime import Date


def datetime_to_utc(date, result_format='timestamp',
                    date_format=None, exclude_millis=False):
    dt = Date(date, date_format)
    dt.datetime = dt.datetime.astimezone(pytz.timezone('utc'))
    return dt.convert(result_format, millis=not exclude_millis)
