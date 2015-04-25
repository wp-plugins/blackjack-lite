<?php

/*
  Plugin Name: Blackjack
  Plugin URI: http://www.thulasidas.com/plugins/blackjack
  Description: <em>Lite Version</em>: Blackjack game. No complicated setup, no server load or submit, just a shortcode on a page!
  Version: 1.41
  Author: Manoj Thulasidas
  Author URI: http://www.thulasidas.com
 */

/*
  Copyright (C) 2008 www.ads-ez.com

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if (class_exists("Blackjack")) {
  $plg = "Blackjack Lite";
  $lite = plugin_basename(__FILE__);
  include_once('ezDenyLite.php');
  ezDenyLite($plg, $lite);
}
else {

  require_once('EzOptions.php');

  class Blackjack extends EzBasePlugin {

    var $title = "Blackjack Game", $help, $credit;
    var $options, $optionName;
    var $slug, $domain, $plgDir, $plgURL, $ezTran, $ezAdmin, $myPlugins;
    var $adminMsg = '';

    const shortCode = 'blackjack';

    static $gamePage;

    function Blackjack() { //constructor
      parent::__construct("blackjack", "Blackjack", __FILE__);
      $this->prefix = 'blackjack';
      $this->adminMsg = '';
      $defaultOptions = $this->mkDefaultOptions();
      $this->optionName = $this->prefix . get_option('stylesheet');
      $this->options = get_option($this->optionName);
      if (empty($this->options)) {
        $this->options = $defaultOptions;
      }
      else {
        $this->options = array_merge($defaultOptions, $this->options);
      }
      $this->credit = "<a href='http://buy.thulasidas.com/blackjack' target='_blank'>Blackjack</a> by <a href='http://www.Thulasidas.com/' target='_blank' title='Unreal Blog proudly brings you Blackjack'>Unreal</a>";
    }

    static function findShortCode($posts) {
      self::$gamePage = false;
      if (empty($posts)) {
        return $posts;
      }
      foreach ($posts as $post) {
        if (stripos($post->post_content, self::shortCode) !== false) {
          self::$gamePage = true;
          break;
        }
      }
      return $posts;
    }

    function blackjackStyles() {
      if (!self::$gamePage) {
        return;
      }
      if (is_admin()) {
        return;
      }
      wp_register_style('blackjackCSS', "{$this->plgURL}/blackjack.css");
      wp_enqueue_style('blackjackCSS');
    }

    function blackjackScripts() {
      if (!self::$gamePage) {
        return;
      }
      if (is_admin()) {
        return;
      }
      wp_register_script('blackjackJS', "{$this->plgURL}/blackjack.js", array("jquery"));
      wp_enqueue_script('blackjackJS');
    }

    function displayGame($atts, $content = '') {
      if (!empty($content)) {
        $this->title = $content;
      }
      $game = "<h2>{$this->title}</h2><div class='page'>
        <div class='dealer-cards'>
          <div class='card card1'></div>
          <div class='card flipped card2'></div>
          <div class='new-cards'></div>
          <div class='clear'></div>
          <div id='dealerTotal' class='dealer-total'></div>
        </div>
        <div class='clear'></div>
        <div class='player-cards'>
          <div class='card card1'></div>
          <div class='card card2'></div>
          <div class='new-cards'></div>
          <div class='clear'></div>
          <div id='playerTotal' class='player-total'></div>
        </div>
        <div class='buttons'>
          <div class='btn' id='hit'>Hit</div>
          <div class='btn' id='stand'>Stand</div>
        </div>
        <div class='betting-area'>
          <b>Your Bet</b><br />
          <div id='bet' class='bet money'>0</div>
          <div>
            <div class='btn' id='more'>+</div>
            <div class='btn' id='less'>-</div>
          </div>
          <div class='clear'></div>
        </div>
        <div>
          <b>Available Funds</b><br />
          <span id='money' class='money'>500</span>
          <div class='clear'></div>
        </div>
        <div class='clear'></div>
        <div id='message' class='message'></div>
      </div>";
      if ($this->options['showCredit']) {
        $game .= "<div style='text-align:center;font-size:x-small;'>{$this->credit}</div>\n";
      }
      return $game;
    }

    function mkDefaultOptions() {
      $options = array();
      $options['showCredit'] = false;
      $options['kill_author'] = false;
      return $options;
    }

    function handleSubmits() {
      if (empty($_POST)) {
        return;
      }
      if (!check_admin_referer("$this->prefix-submit", "$this->prefix-nonce")) {
        return;
      }

      if (isset($_POST['saveChanges'])) {
        $this->adminMsg = '<div class="updated"><p><strong>Options saved.</strong></p> </div>';
        $this->options['showCredit'] = isset($_POST['showCredit']);
        $this->options['kill_author'] = isset($_POST['kill_author']);
        update_option($this->optionName, $this->options);
      }
    }

    function printAdminPage() {
      $ez = parent::printAdminPage();
      if (empty($ez)) {
        return;
      }
      $this->handleSubmits();
      echo $this->adminMsg;
      if ($this->options['showCredit']) {
        $showCredit = "checked='checked'";
      }
      else {
        $showCredit = "";
      }
      if ($this->options['kill_author']) {
        $kill_author = "checked='checked'";
      }
      else {
        $kill_author = "";
      }

      echo <<<EOF1
<script type="text/javascript" src="{$this->plgURL}/wz_tooltip.js"></script>
<div class="wrap" style="width:850px">
<h2>Blackjack Help</h2>
<form method="post" action=''>
<table>
<tr><td style="width:40%">
<!--  Help Info here -->
<ul style="padding-left:10px;list-style-type:circle; list-style-position:inside;" >
<li>
<a href="#" title="Click for help" onclick="TagToTip('help0',WIDTH, 300, TITLE, 'How to Use it', STICKY, 1, CLOSEBTN, true, CLICKCLOSE, true, FIX, [this, 5, 5])">
How to use this plugin?
</a>
</li>
<li>
<a href="#" title="Click for help" onclick="TagToTip('help2',WIDTH, 300, TITLE, 'Color Customization', STICKY, 1, CLOSEBTN, true, CLICKCLOSE, true, FIX, [this, 5, 5])">
How can I change the colors?
</a>
</li>
</ul>
</td>
EOF1;
      include ($this->plgDir . '/head-text.php');
      $this->renderNonce();
      echo <<<EOF2
</tr>
<tr><td colspan="3">
<h3>Blackjack Plugin Options</h3>
<label for="showCredit" style="color:#e00;" onmouseover="Tip('If you would like to support this plugin development, you can show a tiny credit link below the game display.', WIDTH, 240, TITLE, '', FIX, [this, 5, 5])" onmouseout="UnTip()"><input type="checkbox" id="showCredit"  name="showCredit" $showCredit /> &nbsp; Show a tiny credit link at the bottom of the game.</label>
<br /><b>
<label for='kill_author' onmouseover="Tip('If you find the author links and ads on the plugin admin page distracting or annoying, you can suppress them by checking this box. Please remember to save your options after checking.', WIDTH, 240, TITLE, '', FIX, [this, 5, 5])" onmouseout="UnTip()">
<input type='checkbox' id='kill_author' name='kill_author' $kill_author /> &nbsp; Kill author links on the admin page?
</label>
</td></tr>
</table>
<div class="submit">
<input type="submit" name="saveChanges" value="Save Changes" title="Save the changes as specified above" onmouseover="Tip('Save the changes as specified above',WIDTH, 240, TITLE, 'Save Changes')" onmouseout="UnTip()"/>
</div>
</form>
<hr />
<div id="help0" style='display:none;'>
You use the plugin with the help of a shortcode. You create a new post or page and type in just <code>[blackjack]</code>. Visit this post/page with the plugin active, and you will see the game displayed.<br>
  If you would like to change the title, use the form <code>[blackjack]Your New Title[/blackjack]</code>.
</div>
<div id="help2" style='display:none;'>
[Work in Progress] In the Pro version, you will be able to tweak the colors using the color pickers.<br>
If you prefer to stay with the Lite version, you can change the game colors by editing the style file <code>blackjack.css</code> in the plugin folder.
</div>
EOF2;
      echo "<form method='post'>";
      $this->ezTran->renderTranslator();
      echo "</form><br />";
      $ez->renderSupport();
      $ez->renderWhyPro();
      include ($this->plgDir . '/tail-text.php');
      echo <<<EOF3
<table>
<tr><th scope="row">Credits</th></tr>
<tr><td>
<ul style="padding-left:10px;list-style-type:circle; list-style-position:inside;" >
<li>
<b>Blackjack</b> is a WordPress plugin interface to the <a href="https://github.com/tonyspiro/blackjack/ " title="blackjack code page">blackjack packge by Tony Spiro</a>, which does all the heavy-lifting of rendering the game.
</li>
<li>
<b>Blackjack Pro</b> uses the excellent Javascript color picker by <a href="http://jscolor.com" target="_blank" title="Javascript color picker"> JScolor</a>.
</li>
<li>
It also uses the excellent Javascript/DHTML tooltips by <a href="http://www.walterzorn.com" target="_blank" title="Javascript, DTML Tooltips"> Walter Zorn</a>.
</li>
</ul>
</td>
</tr>
</table>
</div>
EOF3;
    }

  }

} //End Class Blackjack

if (class_exists("Blackjack")) {
  $blackjack = new Blackjack();
  if (isset($blackjack)) {
    add_shortcode(Blackjack::shortCode, array($blackjack, 'displayGame'));
    add_action('wp_enqueue_scripts', array($blackjack, 'blackjackStyles'));
    add_action('wp_enqueue_scripts', array($blackjack, 'blackjackScripts'));
    add_filter('the_posts', array("Blackjack", "findShortCode"));
    if (is_admin()) {

      if (!function_exists('blackjack_ap')) {

        function blackjack_ap() {
          global $blackjack;
          $mName = 'Blackjack';
          add_options_page($mName, $mName, 'activate_plugins', basename(__FILE__), array($blackjack, 'printAdminPage'));
        }

      }

      add_action('admin_menu', 'blackjack_ap');
    }
  }
}
