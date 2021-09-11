# HydrogenFramework

*A PHP application framework using MVC, with design patterns and modules.*

[![Package version](http://img.shields.io/packagist/v/ceus-media/hydrogen-framework.svg?style=flat-square)](https://packagist.org/packages/ceus-media/hydrogen-framework)
[![Monthly downloads](http://img.shields.io/packagist/dt/ceus-media/hydrogen-framework.svg?style=flat-square)](https://packagist.org/packages/ceus-media/hydrogen-framework)
[![PHP version](http://img.shields.io/packagist/php-v/ceus-media/hydrogen-framework.svg?style=flat-square)](https://packagist.org/packages/ceus-media/hydrogen-framework)
[![PHPStan level](https://img.shields.io/badge/PHPStan-level%207-brightgreen.svg?style=flat-square)](https://packagist.org/packages/ceus-media/hydrogen-framework)
[![License](https://img.shields.io/packagist/l/ceus-media/hydrogen-framework.svg?style=flat-square)](https://packagist.org/packages/ceus-media/hydrogen-framework)
[![Release date](https://img.shields.io/github/release-date/CeusMedia/HydrogenFramework.svg?style=flat-square)](https://packagist.org/packages/ceus-media/hydrogen-framework)
[![Commit date](https://img.shields.io/github/last-commit/CeusMedia/HydrogenFramework.svg?style=flat-square)](https://packagist.org/packages/ceus-media/hydrogen-framework)

This application framework for PHP is a simple yet powerful engine to
develope custom web applications in MVC style.

A created frame (=empty application with framework and depencies) can be filled with life
by installing modules from existing module sources or create you own module.


## Installation

Create a project folder, step into it and create a composer project:
```
mkdir myProject
cd myProject
composer init
```
You need composer to be installed. If not, install composer globally or locally and run <code>composer init</code> or <code>./composer.phar init</code>

Answer all information questions and skip definition of requirements.

Once done, include the framework:

```composer require ceus-media/hydrogen-framework```

*There are three branches, for more information see Appendix.*

### Hymn
To be able to manage your project using the framework and its modules,
you will need the CLI tool "hymn".

You can install hymn:
- globally on your server
- locally as a standalone tool within your project root folder
- as project dependency in development mode

#### Globally
```
sudo curl -LsS https://github.com/CeusMedia/Hymn/raw/master/hymn.phar -o /usr/local/bin/hymn
sudo chmod a+x /usr/local/bin/hymn
hymn version
```

#### As local standalone
```
curl -LsS https://github.com/CeusMedia/Hymn/raw/master/hymn.phar -o hymn
chmod a+x hymn
./hymn version

```
#### As project dependency
```
composer require ceus-media/hymn
./vendor/bin/hymn version
```

So, the hymn CLI command will differ depending on your installation type:
- <code>hymn COMMAND [PARAMETERS]</code> (globally)
- <code>./hymn COMMAND [PARAMETERS]</code> (local standalone)
- <code>vendor/bin/hymn COMMAND [PARAMETERS]</code> (project dependency)

### Hydrogen project

Now, create a hydrogen project using hymn:
```
hymn create
```
Answer all information questions.
Provide database credentials to access or even create a database.
**Skip** configuration of **composer** since you already have this step.
**Skip** configuration of **PHPUnit** for now.

You now have the hydrogen project file <code>.hymn</code>.
This file will extend in the process and holds all information needed to (re-)install a constallation of defined and configured modules from defined sources.

#### Add local module source
```
mkdir modules
hymn source-add
```
Enter:
- Source ID: <kbd>ENTER</kbd>
- Source type: <kbd>ENTER</kbd>
- Source path: <code>modules</code>
- Source description: <code>Local project modules.</code>

#### Add public modules of Ceus Media
To make use of existing modules to play around or, later, create real applications,
you can use the public modules of Ceus Media.
This module source contains about 300 modules, structured by category.

Install this library and register it as module source:
```
composer require ceus-media/hydrogen-modules
hymn source-add
```
Enter:
- Source ID: <code>CeusMedia_Public</code>
- Source type: <kbd>ENTER</kbd>
- Source path: <code>./vendor/ceus-media/hydrogen-modules/</code>
- Source description: <code>Public module library maintained by Ceus Media.</code>

#### Source check
Now, if you are running:
```
hymn source-list
```
you can see the registered and indexed module sources.

There should be the two <code>Local_Modules</code> and <code>CeusMedia_Public</code>.
Both sources are marked as active, so they are taken into account by hymn.
The default source is the first one created. In this case <code>Local_Modules</code>.
Installing a module will prefer the default source, if not specified otherwise.


### Installing modules
#### Module categories
In the world of Hydrogen modules, there a several types of modules called category.
Some module are very small and just provide one JavaScript file.

Others are complex and deliver:
- views, maybe with forms and style definitions
- language file (aka localization)
- mails, if mails need to be send
- jobs, to be executed via CLI or cron tab
- HTML blocks to customer views and explain things
. JavaScript, for dynamics with web views
- much more

Such rich modules are there to:
- collect or manage module contents (mostly structured in a database)
- provide connections to resources (for storing and reading of contents)
- display collected contents in web views

App modules combine several modules together to an executable web application.
This may include:
- an e-mail queue and archive
- job execution via CLI
- job automation via cron
- request tracking
- user authentication

Having a functional application, you can extend functionality by:
- using further modules other
- creating own modules
- export your own modules to your own module source
- use modules from your own module source

You can list available modules:
```
hymn modules-available [SOURCE_ID]
```
Display (all) details of a module:
```
hymn module-info [MODULE_ID] -v(v)
```
Install a module and uninstallation:
```
hymn app-install [MODULE_ID]
hymn app-uninstall [MODULE_ID]
```

#### Application
For this example, we will use the app module <code>App_Site</code>:
```
hymn app-install App_Site
```
This will install a boilerblate web application with:
- Basic Layout: default master template of some HTML blocks for header and footer etc.
- Module <code>UI_Bootstrap</code>: CSS framework Bootstrap.
- Module <code>JS_jQuery</code>: JavaScript framework JQuery.
- Some exception handling system components.

The CSS framework Bootstrap comes with the icon set of Font Awesome and installs:
- Module <code>UI_Font</code>: a general font manager.
- Module <code>UI_Font_FontAwesome</code>: Font Awesome integration.

One of the system components (a logger) can be configured to send e-mails on errors.
Therefore a module capapble of sending mails will be installed automatically.

Since the sending of mails will by handled by a CLI script (to be decoupled from the web application), a mail queue will be introduced to a database and a job handling mechanism will be installed:
- Module <code>Resource_Mail</code>: a mail generator, queue and sender
- Module <code>Resource_Jobs</code>: a manager to execute module jobs via CLI or cron job

### Finally

Create a logs folder and allow the web server to write to it. Could look like:
```
mkdir logs
sudo chrp www-data
sudo chmod ug+w logs
```
Now, open the project URL in a browser.


## Sandbox application

If the installation is too much for you right now, you could checkout a simple sandbox installation.

Clone an empty application skeleton:

```composer create-project ceus-media/hydrogen-app -n```

Afterwards change into project folder and run setup for development:

```cd hydrogen-app && make set-install-mode-dev```

Create an empty database on your server, and maybe a database user beforehand.

Now you are ready to install application modules:

```make install```


## Appendix

### The three branches

- Branch dev-master should be stable
- Branch 0.8.x aka dev-master is recommended, since all modules are for this version.
- Branch 0.9.x is experimental stage (including namespaces).
- If all modules are compatible to 0.9.x, dev-master will step from 0.8.x to 0.9.x.

#### Installing branches

Latest stable development (dev-master), current on 0.8.x:
```composer require ceus-media/hydrogen-framework```
Latest 0.8.x current development branch:
```composer require ceus-media/hydrogen-framework:^dev-0.8-x```
Latest 0.9.x future development branch:
```composer require ceus-media/hydrogen-framework:^dev-0.9-x```

### Module sources

A module source is a library of one or more modules.
This library could be a GitHub repository, packaged by packagist and installable using composer
Or any other online Git repository, shared or private.

### Event System

Besides the usual Request->Dispatch->Render->Response behavior, a event system exists to inject module code with the bootstrap process.
Therefore events can be attach on hooks, which will be called by the system during boot or later within modules.

### Default Hook Calls

These hooks will be called by the system during boot:

- Env::initModules
- Env::initDatabase
- Database::init
- Env::initCache
- Session::init
- Page::init
- Page::applyModules
- Env::constructEnd
- Env::init
- App::onControl
- App::onDispatch
    - Controller::onDetectPath
    - Page::build
- App::respond
