# EPL Translator

This project has been created to facilitate the translation of the En Première Ligne ([https://enpremiereligne.fr](https://enpremiereligne.fr)).

As well as providing a web interface for editors to use there were three commands which can be used by developers to import and export content.

## Import

This will update the database used by the site to include any new or modified translations. For translations which already exist only the French will be updated. For new translations all three languages will be set to the French version.
```
bin/console translations:import path
```

 - `path` should be the path to the translation folder, but shouldn't contain the file name, e.g. `var/www/enpremiereligne.fr`


## Export strings

This command allows the strings to be exported into an .xlf file according to the path provided

 ```
 bin/console translations:import locale path
 ```
 
 - `locale` indicates which locale to export. Possible options are either `en_NZ` or `se`.
 - `path` should be the path to the translation folder, but shouldn't contain the file name, e.g. `var/www/enpremiereligne.fr`. The correctly-named file for the locale will be created at this path. 
 
 
 ## Export pages
 
 This command allows the pages to be exported into Twig templates in the correct folder for the locale in the path provided
 
  ```
  bin/console translations:import locale path
  ```
  
  - `locale` indicates which locale to export. Possible options are either `en_NZ` or `se`.
  - `path` should be the path to the project folder, e.g. `var/www/enpremiereligne.fr`. The various template files will be created in an appropriate folder at this path. 

  
## Contact
In order to make use of these commands you'll need the DB password for the hosted database. For that, or if you have any questions please contact:

Steve Winter  
[steve@msdev.co.uk](mailto:steve@msdev.co.uk)  
Matatiro Solutions