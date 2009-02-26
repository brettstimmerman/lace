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

error_reporting(E_ALL);

define('LACE_VERSION', '0.1.5');

require_once('config.php');

// Load core libraries
require_once('lib/classes/LaceData.php');
require_once('lib/classes/LaceActivity.php');
require_once('lib/lib_utils.php');
require_once('lib/lib_filter.php');
require_once('lib/lib_lace.php');
require_once('lib/mb_compat.php');

mb_internal_encoding('UTF-8');

// Create the activity object
$A = &new LaceActivity();

fixMagicQuotes();
validateSession();

// Feeble attempt at preventing caching
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').'GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
?>