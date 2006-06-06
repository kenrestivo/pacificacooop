What is going on here?

It's simple.

I have to keep track of a million different settings for a million different configurations. They are all in here, named 'type-location-variation.settings.php'.

I am using UNIX symlinks to link the appropriate version in the appropriate location. The symlinks are emphatically *not* under version control. Everything else is. So I can sync back and forth with impunity, just as long as the rsync excludes are set up right to ignore these symlinks.

Specifically:
settings.php will symlink to the appropriate drupal-*-*.settings.php file
civicrm.settings.php will symlink to the appropriate civicrm-*-*.settings.php file.

It'd be cleaner if I could move them to a subdirectory, but that'd mess up all the paths. instead you have this mess in this folder. Sorry.