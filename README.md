webapps-framework-lib
=====================

CuteWebApps framework for rapid application development of custom web applications.

* PHP 5.2+ compatible
* Ready-to-use in applications
* MVC based architecture
* Component-based structure
* Includes MySQL database driver, taken from Zend Framework
* Component for application localization
* Tools for checking environment and update
* Tools for unit-test coverage and UI crawler

Variuos components for the framework are available on our GitHub profile:
http://github.com/cutewebapps/

Namespaces of classes:
=====================
* Sys  - Basic library with autoloader
* App  - Web-application layer
* DBx  - Database layer, refactored Zend_Db_*
* Lang - Component for application localization

Binaries
=====================
* cwa-install - installation of components
* cwa-package - command-line tool for generating new components
* cwa-model   - command-line tool for generating drafts of models and controllers
* cwa-db      - command-line tool for fetching and installing mysql databases

Installation
=====================
This package needs to be unpacked into users home directory.
After than yu can run cwa-install to install other CWA packages.