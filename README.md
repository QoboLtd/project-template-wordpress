project-template-wordpress
==========================
 
[![Build Status](https://travis-ci.org/QoboLtd/project-template-wordpress.svg?branch=master)](https://travis-ci.org/QoboLtd/project-template-wordpress)
[![Latest Stable Version](https://poser.pugx.org/qobo/project-template-wordpress/v/stable)](https://packagist.org/packages/qobo/project-template-wordpress)
[![Total Downloads](https://poser.pugx.org/qobo/project-template-wordpress/downloads)](https://packagist.org/packages/qobo/project-template-wordpress)
[![Latest Unstable Version](https://poser.pugx.org/qobo/project-template-wordpress/v/unstable)](https://packagist.org/packages/qobo/project-template-wordpress)
[![License](https://poser.pugx.org/qobo/project-template-wordpress/license)](https://packagist.org/packages/qobo/project-template-wordpress)

About
-----

This is a template for the new WordPress projects.

Developed by [Qobo](https://www.qobo.biz), used in [Qobrix](https://qobrix.com).

Install
-------

There are two ways to install and start using this project template.

### Composer

You can create a new project from this template using composer.

```bash
composer create-project qobo/project-template-wordpress example.com
cd example.com
./bin/build app:install DB_NAME="app",PROJECT_NAME="My Project",PROJECT_VERSION="v1.0.0"
```

### Git

Alternatively, if you want to be able to update your project to the latest version
of the template, you can install the template with git.

```bash
mkdir example.com
cd example.com
git init
# Pull the latest version from https://github.com/QoboLtd/project-template-wordpress/releases
git pull git@github.com:QoboLtd/project-template-wordpress.git vX.Y.Z
composer update
./bin/build app:install DB_NAME="app",PROJECT_NAME="My Project",PROJECT_VERSION="v1.0.0"
# Add your own remote repository
git remote add origin git@github.com/USER/REPO
# Push
git push origin master
```

With the above approach, you have the full history of the template development.  You can
do your own development now, and upgrade to the latest template at any point in the future.

Update
------

If you installed the project template using git, you can easily
upgrade your application to the latest template with the following:

```bash
cd exmample.com
# Make sure you are on the master branch and have a clean and up-to-date workspace
git checkout master
git pull origin master
# Create a new branch
git checkout -b project-template-update
# Pull the latest version from https://github.com/QoboLtd/project-template-wordpress/releases
git pull git@github.com:QoboLtd/project-template-wordpress.git vX.Y.Z
composer update
./bin/build app:update
# Check for conflicts, resolve if any, commit, and then push
git push origin project-template-update
# Create a pull request, review, and merge
```

Usage
-----

Now that you have the project template installed, check that it works
before you start working on your changes.  Fire up the PHP web server:

```bash
./bin/phpserv
```

Or run it on the alternative port:

```bash
./bin/phpserv -S localhost:9000
```

In your browser navigate to [http://localhost:8000](http://localhost:8000).
You should see the standard `phpinfo()` page.  If you do, all parts
are in place.

![Screenshot](screenshot.png)

Now you can develop your PHP project as per usual, but with the following
advantages:

* Support for [PHP built-in web server](http://php.net/manual/en/features.commandline.webserver.php)
* Per-environment configuration using `.env` file, which is ignored by git
* Powerful build system ([Robo](http://robo.li/)) integrated
* Composer integrated with `vendor/` folder added to `.gitignore` .
* PHPUnit integrated with `tests/` folder and example unit tests.
* Sensible defaults for best practices - favicon.ico, robots.txt, MySQL dump, Nginx configuration, GPL, etc.

For example, you can easily automate the build process of your application
by modifying the included Robo files in `build/` folder.  Run the following
command to examine available targets:

```bash
./bin/build
```

As you can see, there are already some placeholders for your application's build
process.  By default, it is suggested that you have these:

* `app:install` - for installation process of your application,
* `app:update` - for the update process of the already installed application, and
* `app:remove` - for the application removal process and cleanup.

You can, of course, add your own, remove these, or change them any way you want.  Have a look at
[Robo](http://robo.li) documentation for more information on how
to use these targets and pass runtime configuration parameters.

Test
----

### PHPUnit and PHP CodeSniffer

The fastest and simplest way to run PHPUnit and PHP CodeSniffer is via a
composer script:

```bash
./bin/composer test
```

Alternatively, you can run the test with code coverage reports:

Code coverage reports in HTML format will be placed in `./build/test-coverage/` folder.

### Travis CI

Continious Integration is a tool that helps to run your tests whenever you do any
changes on your code base (commit, merge, etc).  There are many tools that you can
use, but project-template-wordpress provides an example integration with [Travis CI](https://travis-ci.org/).

Have a look at `.travis.yml` file, which describes the environment matrix, project installation
steps and ways to run the test suite.  For your real project, based on project-template-wordpress, you'd probably
want to remove the example tests from the file.

### Examples

project-template-wordpress provides a few examples of how to write and organize unit tests.  Have a look
in the `tests/` folder.  Now you have **NO EXCUSE** for not testing your applications!


Configurations
--------------

Plugin - Compress PNG for WP (Using TinyPNG API)

This plugin requires an API key from TinyPNG. You can set your key in .env.example file using parameter TINYPNG_API_KEY. A default valid API key has already been added to the template but due to a limited number of requests allowed per key, each project should be using its own key. You can get an API key at https://tinypng.com/developers (one key per email address).
