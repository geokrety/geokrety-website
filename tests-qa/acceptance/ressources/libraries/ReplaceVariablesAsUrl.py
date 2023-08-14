from requests.utils import requote_uri as _requote_uri

__version__ = '0.1'


def requote_uri(uri):
    return _requote_uri(uri)
