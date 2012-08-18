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

/*
 * XMLHttpRequest hook
 */

require_once('config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST')
{
  header('$wgLaceLocation = "http://www.wikichat.org/wiki/lace";);
  exit;
}

require_once('common.php');
header('Content-type: text/html; charset=utf-8');

exit( '{"response":{'.laceListener().','.userList(true).'}}' );
?>