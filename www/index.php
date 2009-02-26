<?php

/*
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation; either version 2 of the License, or (at your
 * option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General
 * Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

// Initialize the form's name value to
// be overridden during session init.
global $name;
$name = '';

require_once('common.php');

if ($name == '')
  $name = getName();

// This is only ever used if XMLHttpRequest is
// not accessible (JavaScript disabled, etc.)
// Otherwise, Lace just passes through it unaffected.
laceListener(false);

// Front Controller dirty work
$op = postVar('op', false);
switch ($op)
{
  case 'log':
    $id      = 'log';
    $include = 'log.inc.php';
    $title   = LACE_SITE_NAME.' Logs';
    break;
  case 'help':
    $id      = 'help';
    $include = 'help.inc.php';
    $title   = LACE_SITE_NAME.' Tips';
    break;
  default:
    if ($_SERVER['REQUEST_URI'] != LACE_URL_REL
      && $_SERVER['REQUEST_URI'] != LACE_URL_REL.basename(__FILE__))
    {
      // Redirect invalid URLs to the main page
      header('Location: ' . LACE_URL_ABS);
      exit;
    }
    $id    = 'home';
    $include = 'lace.inc.php';
    $title   = LACE_SITE_NAME;
    $title  .= (LACE_SITE_DESCRIPTION) ? ' - '.LACE_SITE_DESCRIPTION : '';
    break;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <title><?php echo $title; ?></title>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
  <style type="text/css" media="all"> @import "<?php echo LACE_URL_REL; ?>styles/style.css"; </style>
  <?php require_once('scripts/lace.js.php'); ?>
</head>

<body id="<?php echo $id; ?>">
<div id="wrapper">
  <div id="nav">
    <ul>
      <li id="nav-home"><a href="<?php echo LACE_URL_REL; ?>" title="<?php echo LACE_SITE_NAME; ?> Lobby">Lobby</a></li>
      <li id="nav-log"><a href="<?php echo LACE_URL_REL; ?>logs/" title="Previously on <?php echo LACE_SITE_NAME; ?>">Logs</a></li>
      <li id="nav-help"><a href="<?php echo LACE_URL_REL; ?>help/" title="<?php echo LACE_SITE_NAME; ?> Help">Help</a></li>
    </ul>
  </div>

  <div id="content">
    <?php
      // Include the content file.
      require_once('lib/includes/'.$include);
    ?>
  </div>

  <div id="poweredBy">
    Powered by <a href="http://socket7.net/lace/">Lace</a>
  </div>
</div>
</body>
</html>