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
 ?>
<script type="text/javascript">
<!--
var targBox = "main";
function link_init() {
	if (document.getElementById) {
		var atags = document.getElementsByTagName('A');
		for (var i=0;i<atags.length;i++) {
			var ca = atags[i];
			if (ca.href.indexOf('#') > -1) {
				ca.onclick = function() {
					scrollDivToAnchor(this.href.split('#')[1]);
				}
			}
		}
	}
}
function scrollDivToAnchor(a) {
	var b = document.getElementById(targBox);
	b.scrollTop = document.getElementById(a).offsetTop - b.offsetTop + 30;
}
if (navigator.userAgent.indexOf('Safari') > -1) {
	addEvent(window, 'load', link_init, false);
}
-->
</script>
<div id="subnav">
	<h4>Contents</h4>
	<ul>
		<li><a href="#nicknames">Nicknames</a></li>
	  <li><a href="#timestamps">Timestamps</a></li>
		<li><a href="#actions">Actions</a></li>
		<li><a href="#html">Using HTML</a></li>
		<li><a href="#auto">AutoLinks</a></li>
	</ul>
</div>

<h4 id="windowTitle">Basic Help Using <?php echo LACE_SITE_NAME; ?></h4>
<div id="main">
<div id="laceoutput">
	<div class="infobox first" id="timestamps">
	<div class="name">Nicknames</div>
	<div class="message">
		<p>Use the nickname field on the bottom left to change your nickname.  Or,
		you may use the IRC-ish /nick command:</p>
		<p><code>/nick Bob</code></p>
		<p><strong>Note:</strong> The system won't be notified of your new nickname
		until you say something with it.</p>
	</div>
	</div>

	<div class="infobox" id="timestamps">
	<div class="name">Timestamps</div>
	<div class="message">
		<p>To view the timestamp of a post, hover your mouse cursor over the double colon symbol
		(<strong class="timestamp" title="Posted around 5 minutes ago at 15:35">::</strong>) and browsers that support the <code>title</code> attribute will display the time of the comment and how long ago
		it was as a 'tooltip.'</p>

		<p>Hover for an example: <strong class="timestamp" title="Posted around 5 minutes ago at 15:35">::</strong></p>
	</div>
	</div>

	<div class="infobox" id="actions">
	<div class="name">Actions</div>
	<div class="message">
		<p>To perform an action, begin your post with <kbd>/me</kbd> followed by a space
		and your action.</p>
		<p>For example, if your name is Biff and you say something like</p>
		<p><kbd>/me says, &quot;Hello McFly!&quot;</kbd></p>
		<p>it will appear as</p>
		<p><span class="action"><strong>Biff</strong> says, &quot;Hello McFly!&quot;</span></p>
	</div>
	</div>

	<div class="infobox" id="html">
	<div class="name">Using HTML</div>
	<div class="message">
		<p>The full list of allowed HTML is:</p>
		<ul class="features">
			<li><code>a</code></li>
			<li><code>b</code></li>
			<li><code>code</code></li>
			<li><code>em</code></li>
			<li><code>i</code></li>
			<li><code>strong</code></li>
		</ul>

		<p>Most mistakes like a missing end tag or mis-matched tags will be fixed automatically.</p>
	</div>
	</div>

	<div class="infobox" id="auto">
	<div class="name">AutoLinks</div>
	<div class="message">
		<p>Use AutoLinks for adding a single, quick link <strong>with no non-link text</strong> in your message.</p>
		<p>For example, entering</p>
		<p><kbd>http://google.com Google is a great search engine.</kbd></p>
		<p>will automatically create a link like so:</p>
		<p><a href="http://google.com" title="[google.com]">Google is a great search engine.</a></p>

		<p>The first 'word' (in this case, http://google.com) becomes the link address
		and all subsequent words (Google is a great search engine.) become the link text.</p>
		<p>The domain name (google.com) becomes the <code>title</code> attribute, which appears as a tooltip in most browsers.</p>

		<p><strong>Note:</strong> AutoLinks <em>must</em> begin with the characters <kbd>http</kbd> or <kbd>www.</kbd> to work properly.</p>
		<p>AutoLinks will <strong>not</strong> work if  <kbd>http</kbd> or <kbd>www.</kbd> are not the first four characters of your message.</p>
	</div>
	</div>

</div>
</div>

<div class="infobox" id="footer">
</div>