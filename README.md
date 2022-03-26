<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">Yii 2 API Project Template</h1>
    <br>
</p>

This project born on the Yii 2 Advanced Project Template.
Is a skeleton [Yii 2](http://www.yiiframework.com/) application best for
developing RestFul Api Environment.

The template includes four tiers: frontend, backend, api, and console, each of which
is a separate Yii application.

## DIRECTORY STRUCTURE

```
common
    config/              contains shared configurations
    mail/                contains view files for e-mails
    models/              contains model classes used in both backend and frontend
    tests/               contains tests for common classes
console
    config/              contains console configurations
    controllers/         contains console controllers (commands)
    migrations/          contains database migrations
    models/              contains console-specific model classes
    runtime/             contains files generated during runtime
backend
    assets/              contains application assets such as JavaScript and CSS
    config/              contains backend configurations
    controllers/         contains Web controller classes
    models/              contains backend-specific model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for backend application
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
frontend
    assets/              contains application assets such as JavaScript and CSS
    config/              contains frontend configurations
    controllers/         contains Web controller classes
    models/              contains frontend-specific model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for frontend application
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
    widgets/             contains frontend widgets
api
    config/              contains api configurations
    models/              contains api-specific model classes
    runtime/             contains files generated during runtime
    utils/               contains utils classes
    web/                 contains the entry script and Web resources
vendor/                  contains dependent 3rd-party packages
environments/            contains environment-based overrides
```

INIT PROJECT

```
    INIT
        php init
        composer install or update
        create db in your DB Server
        ./yii migrate
        ./yii migrate --migrationPath=@yii/rbac/migrations
        ./yii rbac/create-map
```
