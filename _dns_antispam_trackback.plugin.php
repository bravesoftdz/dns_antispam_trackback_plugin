<?php
/**
 * This file implements the DNS_ANTISPAM_TRACKBACK plugin for {@link http://b2evolution.net/}.
 *
 * @copyright (c)2007 by Larry Nieves - {@link http://liberal-venezolano.net/}.
 *
 * @license GNU General Public License 2 (GPL) - http://www.opensource.org/licenses/gpl-license.php
 *
 * @package plugins
 *
 * @author Larry Nieves
 *
 * @version $Id: _dns_antispam_trackback.plugin.php,v 0.3 2007/08/28 02:52:01 lnieves Exp $
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );


/**
 * dns_antispam_trackback Plugin
 *
 * This Plugins check the reverse dns address of an incoming trackback and compares the
 * result with the originating website. If the IP addresses are different the 
 * trackback is rejected as spam.
 *
 * @package plugins
 */
class dns_antispam_trackback_plugin extends Plugin
{
	/**
	 * Variables below MUST be overriden by plugin implementations,
	 * either in the subclass declaration or in the subclass constructor.
	 */
	public $name = 'DNS Antispam trackback 2';
	/**
	 * Code, if this is a renderer or pingback plugin.
	 */
	public $code = 'dns_trackback';
	public $priority = 50;
	public $version = '0.3';
	public $author = 'Larry Nieves (aka Austriaco)';
	public $help_url = 'http://cronicaslinuxeras.com/dns_trackback_anti_spam_plugin';
	public $group = 'antispam' ;



	/**
	 * Init
	 *
	 * This gets called after a plugin has been registered/instantiated.
	 */
	function PluginInit( & $params )
	{
		$this->short_desc = $this->T_('DNS Antispam trackback plugin');
		$this->long_desc = $this->T_('This plugin provides rejection criteria for trackbacks based on dns IP address resolution.');
	}


	/**
	 * Define settings that the plugin uses/provides.
	 */
	function GetDefaultSettings( & $params )
	{
		return array(
      'whitelist' => array(
        'type' => 'textarea',
        'label' => $this->T_( 'IP whitelist' ),
        'defaultvalue' =>
        "72.232.131.30
72.232.131.29
72.232.131.31
72.233.2.49
72.233.2.30
66.135.48.143
72.9.234.70",
        'cols' => 15,
        'rows' => 10,
        /* 'valid_pattern' => array(
          'pattern' => '/((\d{1,3}\.){3}\d{1,3}\s?)?/',
          'error' => $this->T_( 'The supplied IP address is invalid' ) ), */
        'note' => $this->T_('Input one IP address you want to exempt per line') )
      );
	}


	/**
	 * Define user settings that the plugin uses/provides.
	 */
	function GetDefaultUserSettings( & $params )
	{
		return array(

			);
	}


	// If you use hooks, that are not present in b2evo 1.8, you should also add
	// a GetDependencies() function and require the b2evo version your Plugin needs.
	// See http://doc.b2evolution.net/stable/plugins/Plugin.html#methodGetDependencies


	// Add the methods to hook into here...
	// See http://doc.b2evolution.net/stable/plugins/Plugin.html

	function compare_ip_address( $Comment )
	{
    $iplist = array () ;

    /* Get the list of IP addresses we want to ignore */
    $whitelist = preg_split( "#\s+#", $this->Settings->get( 'whitelist') ) ;
    /* Now $whitelist should contain an array of IP addresses */

    /* First, check the whitelist. If the incoming Trackback comes from a
     * whitelisted address we return true inmediately */
    foreach ( $whitelist as $wip ) {
      if ( $wip == $Comment->author_IP ) {
        return true ;
      }
    }

    /* The incoming trackback was not in our whitelist, procceed to check
     * its legitimacy otherwise */
		$url_parsed = parse_url( $Comment ->author_url ) ;
		$host = $url_parsed['host'] ;
		$iplist = gethostbynamel( $host ) ;
    foreach ( $iplist as $ip ) {
      if ( $ip == $Comment->author_IP ) {
        return true  ;
      }
    }
    return false ;
	}

  function BeforeTrackbackInsert( & $params )
  {
    if( ! $this->compare_ip_address( $params['Comment'] ) )
    {
      $this->msg( T_('The trackback does not come from referring host.'), 'error' );
    }
  }


}

/* Changes:
 * v0.3 mar ago 28 20:23:14 CEST 2007: Added parameter 'whitelist', to provide
 * for a set of IP address we don't want to check, because presumably we know
 * they are good guys
 */
?>