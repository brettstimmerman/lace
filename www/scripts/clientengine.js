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

function Lace() {
  this.init();
}

Lace.prototype.init = function() {
  this.interval   = 0;
  this.defaultInterval = LaceConfig.interval * 1000; // Default interval if IntervalManager is not present
  this.url        = LaceConfig.url + 'lace.php';
  this.nameObj    = $('name');
  this.textObj    = $('text');
  this.laceDomRef = $('laceoutput');

  // Javascript has scope issues with using 'this'
  // inside of an anonymous function, so we use a
  // copy of 'this' (thisObj)
  var thisObj = this;

  //Inititalize Interval Manager if present
  if (window.IntervalManager) {
    this.intManObj = new IntervalManager();
  }

  this.textObj.setAttribute('autocomplete', 'off');
  this.textObj.focus();

  // Used for detecting updates
  this.chatHash = 'default hash';
  this.userHash = 'default hash';
  this.userList = [];

  // Setup the internal name change monitor
  this.name = encodeURIComponent(this.nameObj.value);
  this.nameObj.onblur = function() { thisObj.validateName(); };


  // Lace state and timer properties
  this.isActive = false;
  this.interval = false;

  this.httpSendObj = this.httpObject();
  this.httpGetObj  = this.httpObject();

  // Start Lace if XMLHttpRequest is present.  Also, we need
  // to use encodeURIComponent.  Sorry IE5.0...
  if (this.httpSendObj !== false && window.encodeURIComponent) {
    $('laceform').onsubmit = function() {thisObj.send(); return false;};
    this.statusDisplay();
    this.start();
  }
};

Lace.prototype.validateName = function() {
   name = this.nameObj.value;
  // ARGH!  Could not get Regex working in Safari...
   if ( name.indexOf('!') !== -1
     || name.indexOf('#') !== -1
     || name.indexOf('%') !== -1
     || name.indexOf('&') !== -1
     || name.indexOf('*') !== -1
     || name.indexOf('+') !== -1
     || name.indexOf('|') !== -1
     || name.indexOf('<') !== -1
     || name.indexOf('>') !== -1
   ) {
    var error = 'Sorry, your name contains one or more of the following'+
      ' illegal characters:\n\n! # % & * + | < > \n\n' +
      'Please remove them and try again.';
    alert(error);
    this.nameObj.value = decodeURIComponent(this.name);
    return false;
  }

  name = encodeURIComponent(name);
  if (name == this.name)
    return true;

  var searchName = this.nameObj.value.trim();
  var nameExists = this.userList.linearSearchI(searchName);
  if (nameExists) {
     alert('Sorry, another user has that name.\n\nPlease choose a different name.');
     this.nameObj.value = decodeURIComponent(this.name);
     return false;
   }
};

Lace.prototype.disableInputs = function() {
  this.textObj.disabled = true;
  this.nameObj.disabled = true;
  $('say').disabled = true;
};

Lace.prototype.enableInputs = function() {
  this.textObj.disabled = false;
  this.nameObj.disabled = false;
  $('say').disabled = false;
  this.resetInputs();
};

Lace.prototype.resetInputs = function() {
  // Clear field value - even in Safari
  this.textObj.blur();
  this.textObj.value='';
  this.textObj.focus();
};

Lace.prototype.floodCountdown = function(s) {

  if (s == 0) {
    deleteCookie(LaceConfig.floodCookie, LaceConfig.url);
    this.enableInputs();
    this.textObj.value = this.floodText;
    delete this.floodText;
    if (this.isActive)
      this.send();
    return;
  }

  this.disableInputs();
  this.textObj.value = 'Flood Protection: Your message will be sent in ' + s + ' seconds.';
  var thisObj = this;
  setTimeout(function() {thisObj.floodCountdown(--s); }, 1000);
};

Lace.prototype.isNameChange = function(name) {
  msgTokens = name.split(' ');
  if (msgTokens[0].toLowerCase() === '/nick')
  {
    msgTokens.shift();
    this.nameObj.value = msgTokens.join(' ').substring(0,10);
    this.resetInputs();
    return true;
  }

  return false;
}

Lace.prototype.send = function() {
  var thisObj = this;

  if (this.isNameChange(this.textObj.value) === true)
    return;

  if (this.textObj.value.indexOf("undefined") === 0 ||
    this.textObj.value.indexOf("Flood Protection: Your message will be sent in") === 0) {
    resetInputs();
    return;
  }

  var name = encodeURIComponent(this.nameObj.value);
  var text = encodeURIComponent(this.textObj.value);

  // No flooding
  var floodCookie = getCookie(LaceConfig.floodCookie);
  if (floodCookie !== null && floodCookie >= LaceConfig.floodCount) {
    this.floodText = this.textObj.value;
    this.resetInputs();
    this.floodCountdown(10);
    return;
  }

  if (name !== '' && text !== '') {
    if (this.httpSendObj === null)
      this.start();

    if (this.httpSendObj.readyState === 4 || this.httpSendObj.readyState === 0) {
      this.name = name;
      this.resetInputs();

      var param = 'name=' + name + '&text=' + text;
      param += '&chatHash=' + encodeURIComponent(this.chatHash);
      param += '&userHash=' + encodeURIComponent(this.userHash);
      this.httpSendObj.open('POST', this.url, true);
      this.httpSendObj.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
      this.httpSendObj.onreadystatechange = function() { thisObj.handleSend(); };
      this.httpSendObj.send(param);
    }else {
      setTimeout(function() { thisObj.send(); }, 250);
    }
  }
};

Lace.prototype.handleSend = function() {
  if (this.isActive && this.httpSendObj !== null && this.httpSendObj.readyState === 4) {
    this.timerReset();
    var response = this.httpSendObj.responseText;
    this.handleResponse(response);
    scrollToBottom($('main'), true);
  }
};

Lace.prototype.get = function(system) {
  var thisObj = this;

  if (this.httpGetObj !== null && (this.httpGetObj.readyState === 4 || this.httpGetObj.readyState === 0)) {
    var param = 'chatHash=' + encodeURIComponent(this.chatHash);
    param += '&userHash=' + encodeURIComponent(this.userHash);
    this.httpGetObj.open('POST', this.url, true);
    this.httpGetObj.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
    this.httpGetObj.onreadystatechange = function() { thisObj.handleGet(system); };
    this.httpGetObj.send(param);
  } else {
    setTimeout(function() { thisObj.get(); }, 500);
  }
};

Lace.prototype.handleGet = function(system) {
  if (this.httpGetObj !== null && this.httpGetObj.readyState === 4) {
    this.timerStep(system);
    var response = this.httpGetObj.responseText;
    this.handleResponse(response);
    scrollToBottom($('main'));
  }
};

Lace.prototype.handleResponse = function(response) {
  // Very useful for debugging
  //alert(response);
  if (response !== null && typeof(response) != "undefined") {
    //response = '('+response+')';
    var json = eval( '('+response+')' );
    this.insertResults(json.response);
  }
};

Lace.prototype.insertResults = function(json) {
  if (json.chat.hash) {
    this.chatHash = json.chat.hash;
    this.laceDomRef.innerHTML = json.chat.data;
  }

  if (json.user.hash) {
    this.userHash = json.user.hash;
    this.userList = json.user.data;

    var ul = $('userlist');

    while (ul.hasChildNodes()) ul.removeChild(ul.firstChild);

    for (var i=0; i<json.user.data.length; i++)
    {
      var name = json.user.data[i];
      if (name !== null && typeof(name) != "undefined") {
        var li = document.createElement('li');
          li.appendChild(document.createTextNode(name));
          ul.appendChild(li);
      }
    }
  }
};

Lace.prototype.start = function() {
  this.setActive(true);
  this.timerStart();
};

Lace.prototype.stop = function() {
  if (this.timerStop() === true) {
    this.setActive(false);
  }
};

Lace.prototype.toggle = function() {
  if (this.isActive === false) {
    this.start();
  } else {
    this.stop();
  }
};


/* Lace's timer functions.
 * These functions should be better
 * abstracted into the IntervalManager.
 */
Lace.prototype.timerStart = function() {
  if (this.isActive === false) {
    return false;
  }

  if (this.intManObj) {
    var interval = this.intManObj.reset();
    this.timerSet(interval);
    return true;
  } else {
    this.timerSet(this.defaultInterval);
  }

  return false;
};

Lace.prototype.timerStop = function() {
  if (this.isActive === false) {
    return true;
  }

  if (this.intManObj) {
    clearInterval(this.timerID);
    this.interval = false;
  }

  return true;
};

Lace.prototype.timerSet = function(interval) {
  if (this.isActive === false) {
    return false;
  }

  this.interval = interval;
  var thisObj = this;
  clearInterval(this.timerID);
  this.timerID = setInterval(function() { thisObj.get(true); }, interval);

  return true;
};

Lace.prototype.timerReset = function() {
  if (this.isActive === false) {
    return false;
  }
  if (this.intManObj) {
    var interval = this.intManObj.reset();
    return this.timerSet(interval);
  }

  this.timerStart();
  return false;
};

Lace.prototype.timerStep = function(system) {
  if (this.isActive === false) {
    if (system !== true) {
      return this.start();
    }
    return false;
  }

  if (this.intManObj) {
    var interval = this.intManObj.step();
    if (interval !== false) {
      return this.timerSet(interval);
    }

    return this.stop();
  }
  return false;
};

Lace.prototype.httpObject = function() {
  var xmlhttp = false;
  /*@cc_on
  @if (@_jscript_version >= 5)
  try {
    xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
  } catch (e) {
    try {
      xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    } catch (E) {
      xmlhttp = false;
    }
  }
  @else
  xmlhttp = false;
  @end @*/
  if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
      try {
        xmlhttp = new XMLHttpRequest();
      } catch (e) {
        xmlhttp = false;
      }
  }
  return xmlhttp;
};

Lace.prototype.setActive = function(active) {
  var img  = $('statusimage');
  var text = $('statustext');
  var main = $('main');
  var userList = $('userlist');

  if (active === true) {
    img.setAttribute('src', LaceConfig.url + 'images/pause.gif');
    img.setAttribute('alt', 'Stop');
    img.setAttribute('title', 'Click to stop');

    this.isActive  = true;
    text.innerHTML = 'Active';
    main.setAttribute('class', 'active');
    main.setAttribute('className', 'active');
    userList.setAttribute('class', 'active');
    userList.setAttribute('className', 'active');

    this.httpGetObj  = this.httpObject();
    this.httpSendObj = this.httpObject();

    this.get();
  } else if (active === false) {
    img.setAttribute('src', LaceConfig.url + 'images/play.gif');
    img.setAttribute('alt', 'Start');
    img.setAttribute('title', 'Click to start');

    this.httpGetObj  = null;
    this.httpSendObj = null;

    this.isActive = false;
    text.innerHTML  = 'Stopped';
    main.setAttribute('class', 'inactive');
    main.setAttribute('className', 'inactive');
    userList.setAttribute('class', 'inactive');
    userList.setAttribute('className', 'inactive');
    clearInterval(this.timerID);
  }
};

Lace.prototype.statusDisplay = function() {
  var outer = document.createElement('div');
  outer.setAttribute('id', 'status');

  var div = document.createElement('div');
  div.setAttribute('id', 'statuswrap');

  var txt = document.createElement('span');
  txt.setAttribute('id', 'statustext');

  var img = document.createElement('img');
  img.setAttribute('width', '13');
  img.setAttribute('height', '13');
  img.setAttribute('id', 'statusimage');

  var thisObj = this;
  img.onclick = function() {
    if (thisObj.isActive === true) {
      thisObj.stop();
    } else {
      thisObj.start();
    }
  };

  div.appendChild(txt);
  div.appendChild(img);
  outer.appendChild(div);

  var parent = $('subnav');
  parent.appendChild(outer);
};