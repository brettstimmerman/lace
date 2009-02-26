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
 
function IntervalManager() {
  this.init();
}
  
IntervalManager.prototype.init = function() {
  this._timeout  = LaceConfig.timeout * 60;
  this._min      = 2;
  this._step     = 0.25;
  this._duration = 0;
  this._count    = 0;
  
  this._interval = this._min;
};

IntervalManager.prototype.reset = function() {
  this._interval = this._min;
  this._duration = 0;
  this._count    = 0;
    
  return this.get();
};

IntervalManager.prototype.add = function(amt) {
  this._interval += amt;
  this._duration += this._interval;    
  this._count++;  
};

IntervalManager.prototype.step = function() {
  if (this._duration >= this._timeout) {
    // Timer has reached the _timeout limit
    return false;
  }

  var increment = Math.round(this._step * (this._count/20));
  
  this.add(increment);
  return this.get();
};

IntervalManager.prototype.get = function() {
  return this._interval * 1000;
};