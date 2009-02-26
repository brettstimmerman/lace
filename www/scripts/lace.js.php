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
 
if (LACE_ENABLE_CLIENT_ENGINE === true)
{
?>
<script type="text/javascript" defer="defer">
var LaceConfig = {
  interval: '<?php echo LACE_INTERVAL; ?>',
  timeout: '<?php echo LACE_TIMEOUT; ?>',
  floodCookie: '<?php echo LACE_FLOOD_COOKIE; ?>',
  floodCount: '<?php echo LACE_FLOOD_POST_COUNT; ?>',
  url: '<?php echo LACE_URL_REL; ?>'
};
</script>
<?php
  echo '<script type="text/javascript" src="'.LACE_URL_REL.'scripts/clientengine.js" defer="defer"> </script>'."\n";
  
  if (LACE_ENABLE_INTERVAL_MANAGER === true)
    echo '<script type="text/javascript" src="'.LACE_URL_REL.'scripts/intervalmanager.js" defer="defer"> </script>'."\n";
  
  echo '<script type="text/javascript" src="'.LACE_URL_REL.'scripts/startup.js"> </script>'."\n";
}
?>