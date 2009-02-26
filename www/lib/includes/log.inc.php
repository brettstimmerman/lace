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

require_once('../config.php');

$logfile = LACE_LOGDIR.postVar('log').'.dat';

if (!file_exists($logfile))
	$logfile = LACE_LOGFILE;
?>

<div id="subnav">
<?php printLogList($logfile); ?>
</div>

<h4 id="windowTitle"><?php echo LACE_SITE_NAME; ?> Logs</h4>
<div id="main">
	<div id="laceoutput">
	<?php echo printFileContentsHTML($logfile); ?>
	</div>
</div>

<div id="footer">
</div>