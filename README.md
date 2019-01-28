# HydrogenFramework
PHP5 Framework with Design Patterns and Modules.

## Installation

```composer require ceus-media/hydrogen-framework```

## Sandbox Application

Clone an empty application skeleton:

```composer create-project ceus-media/hydrogen-app -n```

Afterwards change into project folder and run setup for development:

```cd hydrogen-app && make set-install-mode-dev```

Now you are ready to install application modules:

```make install```





## Event System

Besides the usual Request->Dispatch->Render->Response behavior, a event system exists to inject module code with the bootstrap process.
Therefore events can be attach on hooks, which will be called by the system during boot or later within modules.

### Default Hook Calls

These hooks will be called by the system during boot:

- Env::initModules
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

