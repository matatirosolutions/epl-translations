# EPL Translation

This project has been created to facilitate the translation of the En Première Ligne ([https://enpremiereligne.fr](https://enpremiereligne.fr)) website for use in other countries and languages.

As well as providing a web interface for editors to use there are three commands which can be used by developers to import and export translated content.

## Import

This will update the database used by the site to include any new or modified translations. For translations which already exist only the French will be updated. For new translations all three languages will be set to the French version.
```
bin/console translation:import path
```

 - `path` should be the path to the project folder, e.g. `/var/www/enpremiereligne.fr`. The correct file will be retrieved from the translations folder.

It's unlikely that this command will be needed unless new functionality is added to the EPL site.

## Export strings

This command allows the strings to be exported into an .xlf file in the correct location in the path provided.

 ```
 bin/console translation:export:strings locale path
 ```
 
 - `locale` indicates which locale to export. Possible options are either `en_NZ` or `se`.
 - `path` should be the path to the project folder, e.g. `/var/www/enpremiereligne.fr`. The correctly-named file for the locale will be created in the translations folder at this path. 
 
 
 ## Export pages
 
 This command allows the pages to be exported into Twig templates in the correct folder for the locale in the path provided
 
  ```
  bin/console translation:export:pages locale path
  ```
  
  - `locale` indicates which locale to export. Possible options are either `en_NZ` or `se`.
  - `path` should be the path to the project folder, e.g. `/var/www/enpremiereligne.fr`. The various template files will be created in an appropriate folder at this path. 

  
## Contact
In order to make use of these commands you'll need the DB password for the hosted database. For that, or if you have any questions please contact:

Steve Winter  
[steve@msdev.co.uk](mailto:steve@msdev.co.uk)  
Matatiro Solutions