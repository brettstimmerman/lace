<?php

# 'Branding' Options

/** The name you'd like to appear in the main header, and anywhere
  the name of the chatroom is displayed. */
define('LACE_SITE_NAME', 'Lace');

/** A description of your chatroom; appears only as part of the
  <title> tag. */
define('LACE_SITE_DESCRIPTION', '');


# Lace Client Engine Settings

/** Default polling interval used when IntervalManager is disabled
  (in seconds). */
define('LACE_INTERVAL', 3);

/** Timeout threshold after which Lace will stop its XMLHttpRequest
  cycle (in minutes) */
define('LACE_TIMEOUT', 10);


# URL Settings

/** Absolute URL to Lace including trailing slash
  (e.g. 'http://www.myserver.com/lace/' */
define('LACE_URL_ABS', 'http://www.myserver.com/lace/');

/** Relative URL to Lace including trailing slash
  (e.g. '/lace/' or simply '/') */
define('LACE_URL_REL', '/lace/');

/** Use dirified (pretty) URLs for the logs?
  (Requires mod_rewrite)

  NOTE: If LACE_LOGS_DIRIFIED is set to TRUE,
  you should configure mod_rewrite's RewriteBase
  (in .htaccess) to match LACE_URL_REL */
define('LACE_LOGS_DIRIFIED', false);


# Cookie Settings

/** Name Lace's session cookie */
define('LACE_SESSION_COOKIE', 'Lace');

/** Name Lace's nickname cookie */
define('LACE_NAME_COOKIE', LACE_SESSION_COOKIE.'_name');

/** Name Lace's flood data cookie */
define('LACE_FLOOD_COOKIE', LACE_SESSION_COOKIE.'_flood');

/** Secret word that's hashed as part of a unique cookie value */
define('LACE_SECRET_WORD', 'LaceSecretWord');


# Flood Settings

/** Maximum number of posts allowed in 10 seconds
  (higher settings are more leniant) */
define('LACE_FLOOD_POST_COUNT', 8);


# Lace Components

/** Turn the Javascript client engine on of off.

  When turned on, Lace employs XMLHttpRequest where
  available. For browsers lacking XMLHttpRequest support,
  Lace will function as a 'traditional' web application
  where form submissions cause the entire page to refresh.

  When turned off, Lace will function as a 'traditional'
  web application in every browser.

  Disabling the client engine disables all client-side
  functions of Lace. */
define('LACE_ENABLE_CLIENT_ENGINE', true);

/** Enable IntervalManager for throttling XMLHttpRequests.

  If IntervalManager is disabled, Lace will poll at the
  interval specified in LACE_INTERVAL, and cannot be
  throttled or stopped. */
define('LACE_ENABLE_INTERVAL_MANAGER', true);


# Data file handling

/** Maximum number of posts (lines) to display on the main page */
define('LACE_FILE_MAX_SIZE', 35);

/** Maximum age (in days) of logged conversations */
define('LACE_ARCHIVE_DAYS', 8);

/** Use MD5 change detection?

  If set enabled, Lace uses a (much slower) MD5 hashing
  instead of file modification time and file size to detect
  changes in the main data file.

  Use MD5 hashing only if file mod time and size cannot be
  trusted */
define('LACE_HASH_MD5', false);


# Message Settings

/** Whether to log and display system generated join/part
  messages.  (Can be a nuisance on a public server with
  not much chatter going on.) */
define('LACE_SHOW_JOIN_PART', false);

/** Whether to log and display nickname change messages.
  (Can be a nuisance like LACE_SHOW_JOIN_PART.) */
define('LACE_SHOW_NAME_CHANGE', false);

/** Whether to show hourly timestamps on the main page */
define('LACE_SHOW_HOUR', true);

/** Limit messages to this many characters
  NOTE: Keep in mind HTML markup counts towards message length */
define('LACE_MAX_TEXT_LENGTH', 750);


# Data File Locations

/** Filesystem location of the datafile directory including trailing
  slash. Default is the /data directory beneath the directory
  this configuration file is in.

  Note: this is the filesystem path, not the URL. */
define('LACE_DATADIR', dirname(__FILE__).'/data/');

/** Location of the archived data files (logs) including
    trailing slash. */
define('LACE_LOGDIR', LACE_DATADIR.'logs/');

/** Location and filename of the main data file. */
define('LACE_FILE', LACE_DATADIR.'lace.dat');

/** Location and filename of the current log data file */
define('LACE_LOGFILE', LACE_DATADIR.'log.dat');

/** Location and filename of the activity (user list) file */
define('LACE_ACTIVITY_FILE', LACE_DATADIR.'activity.dat');

?>