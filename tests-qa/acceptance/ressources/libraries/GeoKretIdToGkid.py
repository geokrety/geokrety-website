__version__ = '0.1'


def geokret_id_to_gkid(id):
    return "GK{0:04x}".format(id).upper()
