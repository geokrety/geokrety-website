# Notes for programmers

Feel free to add your notes here.


1) use only "free licensed" components (classes, graphics etc)

2) as the system may not use in the future "PHP persistent connections", we have to connect to the database each time in the script.


## GitHub commit message format

Github accept some keyword in commit message. When applicable, please reference related issue using "#" character; example: "Fixes #45" to close issue #45.
cf. [autolinked-references-and-urls/](https://help.github.com/articles/autolinked-references-and-urls/), and [closing-issues-using-keywords](https://help.github.com/articles/closing-issues-using-keywords/).

## Travis_ci

Each time you are pushing fresh commits on https://github.com/geokrety/geokrety-website, a new travis job is started onto [travis-ci.org/geokrety](https://travis-ci.org/geokrety/geokrety-website/).
Travis checks are defined into [.travis.yml](website/.travis.yml)

If you fork the project, then you will have to activate travis-ci builds for your own clone.


## Install

please cf. [INSTALL.md](INSTALL.md)