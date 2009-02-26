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

/**
 * userList()
 *
 * Returns a JSON or HTML representation of
 * the current User List
 */
function userList($json = false)
{
  global $A;

  $users = $A->getUsers();

  $output = '';

  if ($json === false)
  {
    foreach ($users as $name)
    {
      $output .= '<li>'.$name.'</li>';
    }

    return $output;
  }

  $userHash = postVar('userHash', false);
  $hash = $A->getHash();
  if ($userHash === $hash)
  {
    $output = '"user":{"nodata":"1"}';
  }
  else
  {
    $output = '[';

    foreach($users as $name)
    {
      $output .= '"'.addslashes(mb_substr($name, 0, 10)).'",';
    }

    $output = rtrim($output, ',');
    $output .= ']';

    $output = '"user":{"hash":"'.$hash.'","data":'.$output.',"userHash":"'.$userHash.'"}';
  }

  return $output;
}


/**
 * joinMessage()
 *
 * Add a user join message to the log
 */
function joinMessage($name)
{
  if (LACE_SHOW_JOIN_PART)
  {
    global $A;
    if($name && false === $A->keyExists($name))
    {
      $message = array
      (
        'action' => true,
        'time'   => time(),
        'name'   => 'Lace',
        'text'   => '<strong>'.$name.'</strong> joins the conversation',
      );
      addMessage($message);
    }
  }
}

/**
 * isFlooding()
 *
 * Determine whether a user is flooding
 */
function isFlooding()
{
  $postCount = cookieVar(LACE_FLOOD_COOKIE);

  if ($postCount === false)
  {
    setCookie(LACE_FLOOD_COOKIE, '1', time() + 9, LACE_URL_REL);
    return false;
  }

  setCookie(LACE_FLOOD_COOKIE, ++$postCount, time() + 9, LACE_URL_REL);

  if ($postCount > LACE_FLOOD_POST_COUNT)
  {
    return true;
  }

  return false;
}

/**
 * laceListener()
 *
 * Checks POST variables for incoming messages or
 * update requests.
 */
function laceListener($fromListener = true)
{
  $cookie_name = cookieVar(LACE_NAME_COOKIE, false);

  $post_name = postVar('name', false); // name
  $post_text = postVar('text', false); // text

  if ($post_name !== false && $post_text !== false)
  {
    if (validateSession() === false)
      return '"chat":{"nodata":"1"}';

    if (isFlooding() === true)
      return '"chat":{"nodata":"1"}';

    $message = prepareMessage($post_name, $post_text);

    if ($message !== false)
    {
      if ($cookie_name && $cookie_name != $post_name)
      {
        addNameChange($cookie_name, $post_name);
      }
      else
      {
        global $A; // Activity object
        joinMessage($post_name);
        $A->update($post_name);
      }

      // Reset $name just in case it has been changed
      global $name;
      $name = $post_name;
      setcookie(LACE_NAME_COOKIE, $post_name, time() + 259200, LACE_URL_REL);

      addMessage($message);
    }
  }

  if ($fromListener)
  {
    $chatHash = postVar('chatHash', false);

    if ($chatHash)
    {
      $hash = getMessageHash();

      if (validateSession() === false || $chatHash == $hash)
      {
        return '"chat":{"nodata":""}';
      }

      $json = '"chat":{"hash":"'.$hash.'","data":"';
      $json.= addslashes(str_replace("\n", "", printFileContentsHTML())).'"}';
      return $json;
    }

    return '"chat":{"nodata":""}';
  }

  return '"chat":{"nodata":""}';
}


/**
 * getMessageHash()
 *
 * Hash the main file for detecting changes
 */
function getMessageHash()
{
  // hash the main file
  if (LACE_HASH_MD5 === true)
    return md5(file_get_contents(LACE_FILE));
  else
  {
    clearstatcache();
    return filemtime(LACE_FILE).':'.filesize(LACE_FILE);
  }
}

/**
 * getFileContentsRaw()
 *
 * Retrieve raw file contents as one giant string
 * (Why? You can't pass arrays between PHP and Javascript,
 *  unless you use something like PHP-JSON/JSON-PHP, of course.)
 */
function getFileContentsRaw($file = LACE_FILE)
{
  $today      = date('l');
  $dayString  = '';
    $hourString = '';
  $finalOutput  = '';

  // Read the file
  $fileContents = file($file);

  if(is_array($fileContents) && count($fileContents) > 0)
  {
    // We want logfiles in reverse order.
    if ($file != LACE_FILE)
      $fileContents = array_reverse($fileContents);

    // Create the proper HTML for each line
    foreach ($fileContents as $line)
    {
      if (0 == preg_match("/^(\d{10})\|\|(.+?)\|\|(.+?)$/", $line))
        continue;

      // Turn the record into an array full of info
      $line = extractMessageArray($line);

      $output = '';

      // Check for new Day
      if ($file == LACE_FILE)
      {
        if ($line['day'] != $dayString)
        {
          $first     = ($dayString == '') ? '*' : '';
          $dayString = $line['day'];
          $output   .= 'date-'.$line['timestamp'].'||';
          $output   .= $first.$line['date_full'].'||||';
        }
      }
      else
      {
        // Logfiles don't have multiple days
        if($hourString == '')
          $output .= 'date||*' . $line['date_full'] . '||||';
      }

      // Check for new Hour
      if ( ($file == LACE_FILE && $line['day'] == $today
        && $line['hour'] != $hourString)
        || ($file != LACE_FILE && $line['hour'] != $hourString) )
      {
        $first      = ($hourString == '') ? '*' : '';
        $hourString = $line['hour'];
        $output    .= 'hour-'.$line['hour'].'||'.$first.$hourString.':00||||';
      }

      // Check for Action
      $action  = ($line['action']) ? '*' : '';
      $timestr = ($file == LACE_FILE)
          ? 'Posted about '.$line['age'].' ago at '.$line['time']
          : $line['time'];

      $output .= $line['timestamp'].'||'.$timestr.'||'.$action.$line['name'];
      $output .= '||'.$line['text'].'||||';

      $finalOutput .= $output;
    }
  }
  else
  // $fileContents array is empty
  {
    $finalOutput .= 'date||*' . date('l, d F Y') . '||||';
    $finalOutput .= time() . '|| ||!Lace||';
    $welcome      = ($file == LACE_FILE)
      ? 'Welcome to '.LACE_SITE_NAME.'. No one has said anything yet.'
      : 'The current log is empty.';
    $finalOutput .= (file_exists($file))
      ? $welcome
      : 'Sorry, the file you asked for doesn\'t exist.';
  }

  return rtrim($finalOutput, '|');
}

/**
 * printFileContentsHTML()
 *
 * Grab the files's contents and format it with HTML
 * all in one step.
 */
function printFileContentsHTML($file = LACE_FILE) {
  return formatFileContents(getFileContentsRaw($file));
}

/**
 * formatFileContents()
 *
 * Wrap raw file contents in HTML for display
 */
function formatFileContents($rawFileContents)
{
  // break apart the file contents into records
  $items  = explode('||||', $rawFileContents);
  $count  = count($items);
  $output = '';

  for ($i = 0; $i < $count; $i++)
  {
    // break record into fields
    $fields = explode('||', $items[$i]);

    if (mb_substr($fields[0], 0, 4) == 'date')
    {
      // show a date heading

      // fields[0] = id attribute
      // fields[1] = date string (a * prefix denotes that it's
      //                          class attribute is 'first')
      $first   = ($fields[1]{0} == '*');
      $class   = ($first) ? ' class="daystamp first"' : ' class="daystamp"';
      $date    = ($first) ? mb_substr($fields[1], 1) : $fields[1];

      $output .= '<div' . $class . '><h4>'.$date.'</h4></div>'."\n";
      continue;
    }

    if (mb_substr($fields[0], 0, 4) == 'hour')
    {
      if (LACE_SHOW_HOUR === true)
      {
        // show the hourly timestamp

        // fields[0] = id attribute
        // fields[1] = hour string (a * prefix denotes that it's
        //                          class attribute is 'first')
        $first   = ($fields[1]{0} == '*');
        $class   = ($first) ? ' class="hourstamp first"' : ' class="hourstamp"';
        $hour    = ($first) ? mb_substr($fields[1], 1) : $fields[1];
        // Message id attributes sometimes were non-unique because the messages shared
        // the same timestamp.  Until a solution is found - and one may not be necessary -
        // the id is simply ignored and is not needed.  (Also helps keep XHTML valid)
        //$output .= '<div id="' . $fields[0] . '"' . $class . '><h5>' . $hour . '</h5></div>'."\n";
        $output .= '<div' . $class . '><h5>' . $hour . '</h5></div>'."\n";
      }
      continue;
    }

    // show the message

    // $fields[0] = id attribute
    // $fields[1] = time string
    // $fields[2] = name (a * prefix denotes an action, a ! denotes a system notice)
    // $fields[3] = text
    $action = ($fields[2]{0} == '*');
    $notice = ($fields[2]{0} == '!');
    $name   = ($action || $notice) ? mb_substr($fields[2], 1) : $fields[2];

    // A system message is defined by an action by the user Lace
    $system = ($action && $name == 'Lace');
    $class  = ($action && !$system) ? 'message action' : 'message';

    // Build up message HTML
    $output .= '<p';
    $output .= ($notice) ? ' class="notice"' : '';
    $output .= ($system) ? ' class="system"' : '';
    $output .= '><span class="name">';
    $output .= ($action && !$system)
      ? '&nbsp;'
      : $name.' <a title="'.$fields[1].'">::</a> ';
    $output .= '</span><span class="' . $class . '">';
    $output .= ($action && !$system)
      ? '<a title="'.$fields[1].'">'.$name.'</a> '
      : '';
    $output .= $fields[3] . '</span></p>' . "\n";
  }

  return $output;
}

/**
 * getName()
 *
 * Attempt to find a user's name in the $_POST
 * and $_COOKIE variables
 */
function getName()
{
  // Look for the name in $_POST then in
  // $_COOKIE, or give a new generic name
  if (array_key_exists('name', $_POST) && mb_strlen(trim($_POST['name'])) > 0)
  {
    $name = urldecode($_POST['name']);
    setcookie(LACE_NAME_COOKIE, $name, time()+3600*24*30, LACE_URL_REL);
  }
  else
    $name = cookieVar(LACE_NAME_COOKIE, 'Guest ' . mb_substr(rand(0,9999), 0, 4));

  return $name;
}

/**
 * extractMessageArray()
 *
 * Convert a record from the data file
 * into a usable array of data
 */
function extractMessageArray($line)
{
  $linearray = explode('||', $line);

  // Snag the unix timestamp and perform some date calculations
  $datetime = array_shift($linearray);

  // Time elapsed (e.g. 1.5 hours, 4 days, etc.)
  $age = duration_str(time() - $datetime, false, 2);

  // Long format date
    $date_full = date("l, d F Y", $datetime);

    // Short format date
    $date = date('m/d/Y', $datetime);

    // Time of day
    $time = date('H:i', $datetime);

    // Day of week
    $day = date('l', $datetime);

  // Hour
  $hour = date('H', $datetime);

    // Next snag the name
    $name = array_shift($linearray);

  // Check for action or system notice
  $action = ($name{0} == '*') ? true : false;
  $notice = ($name{0} == '!') ? true : false;

  if ($action || $notice)
    $name = mb_substr($name, 1);

  // Now put the post back together
  $words = trim(implode(' ', $linearray));

  // return this mess of info
  return array
  (
    'timestamp' => $datetime,
    'date_full' => $date_full,
    'date'      => $date,
    'time'      => $time,
    'day'       => $day,
    'hour'      => $hour,
    'age'       => $age,
    'action'    => $action,
    'notice'    => $notice,
    'name'      => $name,
    'text'      => $words
  );
}

/**
 * preFilterName()
 *
 * Perform custom filtering on names
 * that lib_filter doesn't cover
 */
function preFilterName($name)
{
  $name = trim($name);

  // Prevent long names from disrupting mesage flow.
  $name = mb_substr($name, 0, 10);

  // System messages are from the user Lace.  No one
  // can use the name Lace.
  if ($name == 'Lace')
    $name = 'Not Lace';

  //$name = htmlentities($name);

  // Lace uses an asterisk prefix in the name to denote actions,
  // so users can't have one in that position.
  if ($name{0} == '*')
    $name = mb_substr($name, 1);

  // Lace uses a bang prefix in the name to denote system notices,
  // so users can't have one in that position
  if ($name{0} == '!')
    $name = mb_substr($name, 1);

  // Replace all < and > characters with entities
  // And, sorry, Lace uses pipes as delimiters... no soup for you!
  $search  = array('<', '>', '|');
  $replace = array('&lt;', '&gt;', '');
  $name    = str_replace($search, $replace, $name);

  return $name;
}

/**
 * preFilterLink()
 *
 * Filter Link text by separating the URL from the link text
 * and filtering the link text as normal.
 *
 * If the URL somehow contains malicious characters, they should
 * be filtered out by htmlentities() when the URL is output as
 * a link, but this might break the link.
 */
function preFilterLink($text)
{
  $array = explode(' ', $text);
  $url   = array_shift($array);
  $text  = implode(' ', $array);
  $text  = preFilterText($text);
  return $url . ' ' . $text;
}

/**
 * codeTagFilter()
 *
 * Replace the contents of <code> tags with HTML Entities so
 * that lib_filter will leave it alone.
 *
 * Note:
 * If the closing <code> tag is missing, this step is skipped and
 * when lib_filter kicks in, malicious code will be stripped
 * and the closing tag added, which means the contents of any code
 * tags will likely be missing or mangled.
 */
function codeTagFilter($text)
{
  $pattern = '%(<code>)(.*?)(</code>)%se';
  $replace = "'\\1'.htmlentities(codeTagFilter('\\2')).'\\3'";
  return stripslashes(preg_replace($pattern, $replace, $text));
}

/**
 * preFilterText()
 *
 * Perform custom filtering that lib_filter normally misses.
 */
function preFilterText($text)
{
  // Make sure the submitted text isn't too long
  // This shouldn't affect valid URLs as AutoLinks
  if (mb_strlen($text) > LACE_MAX_TEXT_LENGTH)
    $text = mb_substr($text, 0, LACE_MAX_TEXT_LENGTH);

  // Wrap long lines if there are more than 35 characters
  // and less than three spaces.
  if (mb_strlen($text) > 35 && mb_substr_count($text, ' ') < 3)
    $text = real_wordwrap($text, 35, ' ');

   // Filter the contents of <code> tags so that lib_filter
   // doesn't interfere with them.
  $text = codeTagFilter($text);

  // Add rel attribute to links
  if (mb_strpos($text, '<a') !== false && mb_strpos($text, '<a rel=') === false)
    $text = str_replace('<a ', '<a rel="external" ', $text);

  // First pass at attempting to fix number comparisons before
  // lib_filter can munge them.
  //
  // Input       Output
  // 800<=1000   800 &lt;= 1000
  // 400> 200    400 &gt; 200
  // 100 <>500   100 &lt;&gt; 500
  // etc...
  $pattern = '%(\d)\s*([<>=]{1,2})\s*(\d)%se';
  $replace = "'\\1'.htmlentities(' \\2 ').'\\3'";
  $text = preg_replace($pattern, $replace, $text);

  // Replace all orphaned < and > characters with entities to keep
  // lib_filter from hosing them...
  // And, sorry, Lace uses pipes as delimiters - broken vertical bar for you!
  $search  = array(' < ', ' > ', '|');
  $replace = array(' &lt; ', ' &gt; ', '&brvbar;');
  $text    = str_replace($search, $replace, $text);

  // Replace all mid-message newlines with <br> tags
  // Currently, this break large blocks of code within
  // <code> tags...
  $text = str_replace("\n", '<br>', $text);

  // Replace all <br /> tags with <br> tags
  $text = str_replace('<br />', '<br>', $text);

  // Reduce groups of 3 <br> tags to just 2
  while(mb_strpos($text, '<br><br><br>') !== false)
  {
    $text = str_replace('<br><br><br>', '<br><br>', $text);
  }

  return $text;
}

/**
 * getCommand()
 * Parse incoming message text for a command
 *
 * Commands must be within the first 4 characters
 * of the message
 *
 * The two supported commands are actions
 * (designated by '/me ' or '\me '), and links
 * (designated by 'http' or 'www.')
 */
function getCommand($text)
{
  $cmd = strtolower(mb_substr($text, 0, 4));
  switch ($cmd)
  {
    case '/me ':
    case '\me ':
      $command = 'action';
      break;
    case 'http':
    case 'www.':
      $command = 'link';
      break;
    default:
      $command = false;
      break;
  }

  return $command;
}

/**
 * prepareMessage()
 *
 * Prepare incoming message data for storage
 */
function prepareMessage(&$name, $text)
{
  $message = array();

  // Parse text for commands and format accordingly
  $cmd = getCommand($text);

  // Perform some custom prefiltering
  $name = prefilterName($name);
  $text = ($cmd == 'link') ? preFilterLink($text) : preFilterText($text);

  // HTML filter
  $filter = new lib_filter();

  $action = false;

  switch ($cmd)
  {
    case 'action':
      // Action
      $action = true;
      $text = $filter->go(mb_substr($text, 3));
      break;
    case 'link':
      // AutoLink
      // Grab the URL from the message
      $input = explode(' ', trim($text));
      $url   = array_shift($input);
      if (mb_substr($url, 0, 4) == 'www.')
        $url = 'http://'.$url;
      $urlparts = @parse_url($url);

      if (array_key_exists('host', $urlparts))
      {
        // Url is most likely valid (parse_url() is
        // not the best way to check this)

        if (count($input) > 0)
          // There is link text
          $urltext = implode(' ', $input);
        else
          // the url becomes the link text, and is shotened if necessary
          $urltext = (mb_strlen($url) > 40) ? str_shorten($url, 25) : $url;

        $text = '<a href="'.htmlentities($url).'"';
        $text.= 'title="['.htmlentities($urlparts['host']).']" rel="external">';
        $text.= htmlentities($urltext).'</a>';
      }
      else
        // Url is most likely invalid
        return false;
      break;
    default:
      // No command
      $text = $filter->go($text);
      break;
  }

  if (mb_strlen(trim($text)) == 0)
    // Message text is invalid
    return false;

  $message['action'] = $action;
  $message['time']   = time();
  $message['name']   = $name;
  $message['text']   = $text;

  return $message;
}

/**
 * newLog()
 *
 * Create a new archive log file and delete ones past their prime
 */
function newLog($log, $date)
{
  // Find the age threshold for deleting old logs
  $minage = time() - LACE_ARCHIVE_DAYS * 86400;

  // Delete logs that are too old
  $handle = opendir(LACE_LOGDIR);
  while ($file = readdir($handle))
  {
    if($file == '.' || $file == '..')
      continue;

    if (getDateFromFileName($file) < $minage)
      unlink(LACE_LOGDIR.$file);
  }
  closedir($handle);

  // Write the new log file
  $filename = date('mdY', $date).'.dat';
    $log      = implode("\n", array_map('trim',$log))."\n";
    $handle   = fopen(LACE_LOGDIR.$filename, 'a');
    fwrite($handle, $log);
    fclose($handle);
    // return an empty array, signifying
    // that the main logfile is empty
     return array();
}

/**
 * logMessage()
 *
 * Add message to the logfile
 */
function logMessage($line) {
  // Pull the current log contents
    $log = file(LACE_LOGFILE);

    // Grab the date of the
    // most recent post in the log
    $date = (count($log) > 0)
        ? array_shift($temp = explode('||', $log[0]))
        : false;

    // Write yesterday to file if necessary
  if($date !== false && date('d', $date) != date('d'))
        $log = newLog($log, $date);

    // Write the new message
    $logfile = fopen(LACE_LOGFILE, 'w');
    fwrite($logfile, $line."\n");

    // Write any other remaining messages
    if (count($log) > 0)
      fwrite($logfile, implode("\n", array_map('trim',$log))."\n");

    fclose($logfile);
}

/**
 * addNameChange()
 *
 * Add message to the main data file
 */
function addNameChange($from, $to)
{
  global $A; // Activity object
  $A->changeName($from, $to);

  if (LACE_SHOW_NAME_CHANGE)
  {
    $message = array
    (
      'action' => true,
      'time'   => time(),
      'name'   => 'Lace',
      'text'   => '<strong>'.$from.'</strong> is now <strong>'.$to.'</strong>',
    );

    addMessage($message);
  }
}

/**
 * addMessage()
 *
 * Add message to the main data file
 */
function addMessage($message)
{
  $name = ($message['action']) ? '*'.$message['name'] : $message['name'];
  $text = $message['text'];
  $time = $message['time'];

  if(mb_strlen($name) == 0)
    return;

  $line = $time.'||'.$name.'||'.$text;

  // Pull the current file contents
  $fileContents = file(LACE_FILE);
  $size = count($fileContents);

  if ($size >= LACE_FILE_MAX_SIZE)
  {
    // Push the oldest entries out
    // and put the new one in
    for ($i = 0; $i <= $size - LACE_FILE_MAX_SIZE; $i++)
      array_shift($fileContents);

    $fileContents[] = $line;
    $fileContents = implode("\n", array_map('trim', $fileContents))."\n";

    // Write it to file
    file_put_contents(LACE_FILE, trim($fileContents));
  }
  else
  {
    // No need to push anything off the stack,
    // just write to file
    $file = fopen(LACE_FILE, 'a');
    fwrite($file, $line."\n");
    fclose($file);
  }

  // Add to the log
  logMessage($line);
}

/**
 * printLogList()
 *
 * Display the log history navigation
 */
function printLogList($currentFile)
{
  // Grab the filenames from the
  // log directory
  $recentLogs = array();

  $handle = opendir(LACE_LOGDIR);
  while ($file = readdir($handle))
  {
    if($file == '.' || $file == '..' || $file == 'index.php')
      continue;
    $recentLogs[] = $file;
  }
  closedir($handle);

  // Date preparations
  $today     = date('d');
  $filemtime = filemtime(LACE_LOGFILE);
  $filedate  = date('d', $filemtime);

  if ($today == $filedate)
    $day = 'Today';
  else if (date('d', time()-3600*24) == $filedate)
    $day = 'Yesterday';
  else
    $day = date('d F', $filemtime);

  // Print the list
  $output  = "<h4>View Logs</h4>\n";
  $output .= '<ul>'."\n";

  if (count($recentLogs) > 0)
  {
    $class   = ($currentFile == LACE_LOGFILE) ? ' class="this"' : '';
    $output .= '<li'.$class.'><a href="'.LACE_URL_REL.'logs/"
      title="View '.$day.'\'s Log">'.$day.'</a></li>'."\n";

    // We just want the 'date' part of the filenames
    // so we can parse it, and also use it to
    // make pretty URLs
    $currentFile = str_replace('.dat', '', basename($currentFile));

    // Sort logs most recent first
    sort($recentLogs);
    $recentLogs = array_reverse($recentLogs);

    foreach($recentLogs as $log)
    {
      $log = str_replace('.dat', '', $log);

      $date = getDateFromFileName($log);

      $title = (date('j') - 1 == (int)$d) ? 'Yesterday' : $date;
      $class = ($log == $currentFile) ?' class="this"' : '';

      $output .= '<li'.$class.'><a href="'.LACE_URL_REL.'logs/';
      $output .= LACE_LOGS_DIRIFIED ? $log.'/' : '?date='.$log;
      $output .= '" title="View log for '.$title.'">'.$title.'</a></li>'."\n";
    }
  }
  else
    $output .= '<li>No logs.</li>';

  $output .= "</ul>\n";

  echo $output;
}

function getDateFromFileName($filename, $dateFormat = 'd F')
{
  $m = mb_substr($filename, 0, 2);
  $d = mb_substr($filename, 2, 2);
  $y = mb_substr($filename, 4, 4);
  return date($dateFormat, strtotime("$m/$d/$y"));
}