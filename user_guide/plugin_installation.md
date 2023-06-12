# Installation

## Pre-Installation

Before installing the plugin, be sure that you have completed the following tasks.

* Your light show has access to the internet.


## Website Setup

* Copy the files from the ```website_setup``` directory to your light show website. 
* Rename the config.example.php file to config.php
* Update the values in the config.php file to match your website
* Upload the ```jukebox.api``` file in your website root directory. 
* Upload the ```config.php``` file in the parent folder above your website root directory.
* Login to the database for your website. Execute the query in songrequest.sql and in songsettings.sql. Ensure that the sql commands complete successfully.


## FPP Plugin Setup

To install this plugin

* Copy and paste the following URL on the Plugin Manger page (Content Setup > Plugin Manager). 
[https://raw.githubusercontent.com/almostengr/fpp-website-jukebox/main/pluginInfo.json](https://raw.githubusercontent.com/almostengr/fpp-website-jukebox/main/pluginInfo.json)
* Click Get Plugin Info button
* The plugin will show in the Available Plugins list.
* Click the Install button next to the plugin name.
