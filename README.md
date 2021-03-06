![Logo Combawa](logo_combawa.png)
# Combawa

Used in combination of happyculture/happy_rocket or any other drupal-project based composer template, combawa will help you to setup your project and be ready to start working. 
Then, it will help you build your projects in a consistent and adaptative way.

## Project generator

Used at the beginning of a project, this command creates an installation profile, a default theme and an admin theme. If you want it to, it can also change the default configuration to use these generated elements. Finally, it can also generate build setting files used by the project builder.

To use it in interactive mode, just run `drupal combawa:generate-project`.

The files generated by this command should be added to your git repository.

## Environment generator

Used once per environment, this command creates a .env file and a settings.local.php file that allow to override project default settings and to define the database access credentials used by Drupal.

To use it in interactive mode, just run `drupal combawa:generate-environment`.\
All interactive options are also available in non-interactive mode if you need this to be run by your CI server for example. See the integrated help using `drupal help combawa:generate-environment`.

Eg:
```
drupal combawa:generate-environment \
    --environment=preprod \
    --environment-url=https://mysite.url \
    --dump-file-name=reference_dump.sql \
    --db-host=localhost \
    --db-port=3306 \
    --db-name=db_name \
    --db-user=db_user \
    --db-password=db_password \
    --no-interaction
```

## Project builder

The project builder is a script that... builds the project each time it's needed based on the current files (ie. it does not retrieve new code from the git repository). 

According to the settings, either it is going to drop everything and start a fresh install using the given configuration, either it is going to load a reference database dump extracted from production then run all updates on it.

You can build your project by running `composer build`.

## Wrapper

If you are using this script accross different projects, you should use the Combawa wrapper (https://github.com/Happyculture/combawa-wrapper) that works as the Drush wrapper. One global command to run the Combawa build without targetting the `vendor/bin/combawa` binary.
