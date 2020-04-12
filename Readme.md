# Module for importing news to a site from the RSS feed
Should be developed, then convert it into news entities on website.
News entity consist of:
* Title
* Body
* Image (the main image of the news, should be stored on local server)
* Source (link to the news object’s source - details news page)

Also follow further conditions:
Keep in mind that there should be a possibility to import new news from time to time when RSS feed updates.
For every item take the first image from images listed under content attribute and store image on a local server.
This module should be installed to website as composer dependency.

Getting started
------------
In order to deploy this module, you need to install CMS Drupal 8, Docker4Docker. For more information, see the Deployment section.

Installation
------------
The setting can be made in several ways, using composer or through Drupal 8. Consider using composer.
In the composer.json file, you need to add a section with the repository
```
    "repositories": [
        {
            "type": "package",
            "package": {
                "name": "whdigger/import",
                "version": "master",
                "type":"drupal-custom-module",
                "source": {
                    "url": "https://github.com/whdigger/import.git",
                    "type": "git",
                    "reference": "master"
                }
            }
        }
    ],
    "extra": {
        "installer-paths": {
            "web/modules/custom/{$name}": ["type:drupal-custom-module"]
        }
    }
```
Run the installation command via composer
```
composer require whdigger/import
```
Go to the extensions section of Drupal, select the Import module from the list, and click install.

For cron to work you need to add the following string to crontab
```
0 * * * * cd /var/www/html && /usr/bin/drush cron 
```

Example of use
------------
To start using data import, you need to create a news entity that will import data from the RSS feed. To do this, go to the section Structure -> Material Types -> Add material type (/admin/structure/types/add)
Next, in the manage fields section, add fields: title, content, links, and file

You can then configure the import type.
In the structure section, you will see a new data type importer types (/admin/structure/importer_type/add) you need to create a data type, match the data import fields to the news entity fields, and select a unique key.

After the data type is created, you can proceed to creating the upload itself, a new item will appear in the content section (admin/content/importer) here you need to install a link to the RSS feed. After saving the data, go to the section (admin/content/importer) and select Import in the table in the operations column.
The entire installation and configuration process can be viewed in more detail on the video https://youtu.be/buLbm7ztIv4
