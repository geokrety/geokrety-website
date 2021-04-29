
# GeoKrety.org QA

## Dashboard


<p align="center">
<a href="https://geokrety.github.io/geokrety-website-qa/"><img src="https://image.flaticon.com/icons/svg/203/203165.svg" width="50" alt="QA Tests reports"/> <small>QA Tests reports</small></a>
</p>

## Context

This project includes automated tests -Quality Assurance (QA)- for  GeoKrety.org website ([source code](https://github.com/geokrety/geokrety-website), [service](https://geokrety.org)):
- Tests rely on Python, RobotFramework, Selenium Frameworks

# HowTo run QA

## Prerequisite
Install `Robot Framework`, `geckodriver` and a recent Firefox version (Tested with 79.0).
```bash
make install_robot-framework
make download_geckodriver
# Command `magick` need to be installed, use your package manager to install it
apt-get install imagemagick
```

If you need while development to take manual step, the `Dialogs` module will need `thinker` library.

```bash
sudo apt-get install python3-tk
```

### From command line

Before starting, you have to define testing URL Address.

```bash
# Default value is
$ export GEOKRETY_URL=http://localhost:3001/
```

Note: qa-tests depends on `GK_DEVEL` environment variable which activates some
special quick access functions to be used only in tests.

Warning: Running qa-tests will empty your database!

The simplest way is to use the `Makefile` to launch all tests
```bash
make tests
```

But sometime you'll may have to launch them individually. Using the `robot`
command directly will be necessary. Typical examples are:
```bash
$ robot --variable browser:Firefox -v images_dir:visual_images --output output.xml --debugfile debugfile.log --log log.html --report report.html --xunit xUnit.xml -d docs/local -V acceptance/vars/robot-vars.py acceptance/

$ robot --variable browser:Firefox -v images_dir:visual_images --output output.xml --debugfile debugfile.log --log log.html --report report.html --xunit xUnit.xml -d docs/local -V acceptance/vars/robot-vars.py acceptance/180_News

$ robot --variable browser:Firefox -v images_dir:visual_images --output output.xml --debugfile debugfile.log --log log.html --report report.html --xunit xUnit.xml -d docs/local -V acceptance/vars/robot-vars.py -s 50_Comments acceptance/180_News

```

It is possible to not display the browser window using the `HEADLESS=True` environment variable.

### From Travis

Tests are included in the automated travis execution. It is only activated on `branches` not `PR`.

### HowTo run QA BrowserStack stage (Experimental)

To run BrowserStack tests locally, you will need :
- The `BrowserStackLocal` binary (`make download_bslocal`)
- a BrowserStack username,
- a BrowserStack token.

They could be retrieved from  https://automate.browserstack.com/

````
export BS_USERNAME=jojo
export BS_TOKEN=ThisIsVerySecretToken
export TARGET_BS=True
````

# Contribute
- [Wiki page](https://github.com/geokrety/geokrety-website-qa/wiki) includes good practices and tips,
- Pull request need to embed use case definitions, and pass Travis checks.


# Credit
- Those tests are based on @boly38 work in [geokrety-website-qa](https://github.com/geokrety/geokrety-website-qa)
