# Plexidex
Export Plex Media Server Movie library to HTML/PHP

![v0.0.2-a-Main Page](http://i.imgur.com/PHOTpqG.jpg)

## Change History

### v0.0.2-a
#### Updates
* New card design
* Details button moved to bottom of card
* Details appears as modal instead of within card
* More metadata
* New export and direct stream options
* Added favicon

#### v0.0.2 Screenshots

![Main Page](http://i.imgur.com/PHOTpqG.jpg)


![Details Card](http://i.imgur.com/NYvSEVA.png)




### v0.0.1-a

![v0.0.1 Screenshot](http://i.imgur.com/6dmsB5K.jpg)

This is the initial upload to gage the interest of other Plex users.

This package will require a webserver running php, and a network connection to your Plex Media Server

Upload contents to a PHP webserver and set the permissions of the *temp* directory to 0744. Set the permissions of *settings.inc* to 0600. All other files should be 0644.

Edit the settings.inc file to add your Plex IP address or hostname, port number, and X-Plex-Token.

Then open your browser and go to *webserverpath/index.php?refresh*

This should prompt you to choose your desired movie index sections. Check the desired sections and click save.

With your page still set to *?refresh*, click the sort links on the left to refresh their sort orders.

Once that is done, you can access the page without *?refresh*. To refresh your HTML in the future, just visit with *?refresh* again.

Feedback is welcome!
## Road Map
### Next Release
Landing Page
  Informs PMS connection status
  Media request contact form
Options page for config of
  IP:port
  X-token
  Library expiration
  Hide sections
  Disable Stream or Export
Lazy load images for reduced bandwidth
Manual refresh directly on page
### Distant Future Release
Search
Category listings (Genre, TV Show seasons)

## Troubleshooting
### Initial pages are not saving
This may be because the application cannot modify the */temp/sections.txt* file

Manually edit the file and add a new row for each *title;key*. You can get the key by browsing to *http://plexip:port/library/sections/*

example: Movies;2

### Page is outdated
Append *?refresh* to the end of your URL *webserverpath/index.php?refresh*, then click a listing on the left to refresh it.
This will need to be done for each listing.
