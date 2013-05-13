***********************************
      CiviCRM release scripts
***********************************

Use these scripts to create the CiviCRM release tarballs (posted to SourceForge.net)


1- Create your release environment

It is much better to start from scratch in a new directory rather than from your development environment.

  git clone https://github.com/CiviCRM42/civicrm42-core.git civicrm42
  cd civicrm42
  git clone https://github.com/CiviCRM42/civicrm42-packages.git packages
  git clone https://github.com/CiviCRM42/civicrm42-drupal.git drupal
  git clone https://github.com/CiviCRM42/civicrm42-joomla.git joomla
  git clone https://github.com/CiviCRM42/civicrm42-wordpress.git WordPress
  svn checkout http://svn.civicrm.org/l10n/trunk l10n

2- Customize your configuration file

  cd distmaker
  cp distmaker.conf.dist distmaker.conf
  which php rsync zip
Note the result of these commands, you will need this on the next line ...

  vi distmaker.conf
DM_SOURCEDIR should point to the ‘civicrm42’ directory you created in step 1
DM_GENFILESDIR and DM_TARGETDIR must be existing directories – create them if needed
ATTENTION: please make sure DM_REF_CORE=4.2 (and not 'master')

3/ Create the distribution

  ./distmaker.sh all

The resulting files will be in the DM_TARGETDIR directory