#Keepass database viewer for Owncloud

Specify the path to your kdbx file in your user profile on owncloud and enter the database password.

This is a viewer only, the database cannot be modified in any way.

Keepass kdbx file must be password protected only, with the default encryption settings.

##Acknowledgements:

- The GUI is from Passwords for ownCloud Server 8
2015, Fallon Turner <fcturner@users.noreply.github.com>
https://github.com/fcturner/passwords

- The PHP Keepass viewer backend is from KeePassPHP
2013, shkdee 
https://github.com/shkdee/KeePassPHP

- notepad icon
http://www.flaticon.com/free-icon/class-notes_78686

##GUI Changelog:
- Changed password table to have fixed column width percents to prevent the table resizing when hovering over entries
- Changed the notes popup to be bigger and the size based of the screen percent
- Reduced the text scaling when displayed on a smaller screen
- Changed the notes icon to be different from the edit icon
- Added an option to hide the password strength field
- Removed text below the passwords, its annoying
- Try to copy username or password to clipboard when clicked on
- Changed the "Search for" box to only search password titles
- Creation time sorted to the second
- Disabled all options for editing or creating entries
- Changed to load passwords from a kdbx file in the users cloud instead of a database

##KeePassPHP Changelog:
- Added support for reading notes
- Added support for reading creation date/time
- Added support to be used in ownCloud
