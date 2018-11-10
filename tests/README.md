This page describes how to test Geokrety using PHPUnit

# Context

PHPUnit is a PHP Unit Tests framework

Main config file is at root location and called `phpunit.xml`

## Minimal requirements

You will need some php extensions:

Uncomment in `php.ini` your extension location:

    extension_dir = "C:/tools/php7/ext/" or "ext"

and enable the following extensions:

    extension=curl
    extension=gettext
    extension=mysqli

## Test customization

Some tests requires database, but without test database defined, this tests are simply skipped.
To setup test database, fill the following environment: `test_database_host`, `test_database_pwd`

Main tests config file is: `tests\config\konfig-mysql.php`.
Notice that database name is `geokrety-test-db` and user is `root`. 

You could also show test methods by setting: `test_show_method_name=true`


## How to run tests

Example of a Bash configuration:

    Name: PHPUnit (geokrety)
    Script: scripts/runTests.sh
    Interpreter path: C:\Programmes\Git\bin\bash.exe
    Working directory: C:\MyWORK\geokrety-website

In option, you could add environment variables to customize your tests:


    test_database_host=gk-db
    test_database_pwd=password
    test_show_method_name=true
