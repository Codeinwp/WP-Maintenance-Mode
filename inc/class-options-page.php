<?php

/**
 * WordPress Options Page "Settings > Maintenance Mode"
 *
 * @since   21.05.2013 16:30:00
 * @version 21.05.2013 16:30:03
 * @author  fb
 */
class WP_MaintenanceMode_Options_Page extends WPMaintenanceMode {
	
	protected static $classobj = NULL;
	
	// string for plugin file
	static private   $plugin;
	
	static private   $option_string;
	
	public function __construct() {
		
		if ( ! is_admin() )
			return NULL;
		
		self::$plugin        = FB_WM_BASENAME;
		self::$option_string = FB_WM_TEXTDOMAIN;
		// get options
		$this->options = parent::get_options();
		
		// remove db entries on uninstall
		register_uninstall_hook( __FILE__, array( 'WP_MaintenanceMode_Options_Page', 'unregister_settings' ) );
		
		
		// settings for an active multisite
		if ( is_multisite() && is_plugin_active_for_network( self::$plugin ) ) {
			add_action( 'network_admin_menu',    array( $this, 'add_settings_page' ) );
			// add settings link
			add_filter( 'network_admin_plugin_action_links', array( $this, 'network_admin_plugin_action_links' ), 10, 2 );
			// save settings on network
			add_action( 'network_admin_edit_' . self::$option_string, array( $this, 'save_network_settings_page' ) );
			// return message for update settings
			add_action( 'network_admin_notices', array( $this, 'get_network_admin_notices' ) );
			// add script on settings page
		} else {
			add_action( 'admin_menu',            array( $this, 'add_settings_page' ) );
			// add settings link
			add_filter( 'plugin_action_links',   array( $this, 'plugin_action_links' ), 10, 2 );
			// use settings API
			add_action( 'admin_init',            array( $this, 'register_settings' ) );
		}
	}
	
	public static function get_object() {
		
		if ( NULL === self :: $classobj )
			self :: $classobj = new self;
		
		return self :: $classobj;
	}
	
	public function register_settings() {
		
		register_setting( self::$option_string . '_group', self::$option_string, array( $this, 'validate_settings' ) );
	}
	
	/**
	 * Unregister and delete settings; clean database
	 * 
	 * @uses    unregister_setting, delete_option
	 * @access  public
	 * @since   0.0.2
	 * @return  void
	 */
	public function unregister_settings() {
		
		unregister_setting( self::$option_string . '_group', self::$option_string );
		delete_option( self::$option_string );
	}
	
	public function contextual_help( $contextual_help, $screen_id, $screen ) {
			
		if ( 'settings_page_' . self::$option_string . '_group' !== $screen_id )
			return $contextual_help;
			
		$contextual_help = 
			'<p>' . __( '' ) . '</p>';
			
		return normalize_whitespace( $contextual_help );
	}
	
	/*
	 * Retrun string vor update message
	 * 
	 * @uses   
	 * @access public
	 * @since  2.0.0
	 * @return string $notice
	 */
	public function get_network_admin_notices() {
		
		// if updated and the right page
		if ( isset( $_GET['updated'] ) && 
			 'settings_page_WP-Maintenance-Mode/inc/class-options-page' === $GLOBALS['current_screen'] -> id
			) {
			$message = __( 'Options saved.', $this->get_textdomain() );
			$notice  = '<div id="message" class="updated"><p>' .$message . '</p></div>';
			echo $notice;
		}
	}
	
	/**
	 * Add settings link on plugins.php in backend
	 * 
	 * @uses   
	 * @access public
	 * @param  array $links, string $file
	 * @return string $links
	 */
	public function plugin_action_links( $links, $file ) {
		
		if ( FB_WM_BASENAME == $file  )
			$links[] = '<a href="options-general.php?page=' . plugin_basename( __FILE__ ) . '">' . __('Settings') . '</a>';
		
		return $links;
	}
	
	/**
	 * Add settings link on plugins.php on network admin in backend
	 * 
	 * @uses   
	 * @access public
	 * @param  array $links, string $file
	 * @return string $links
	 */
	public function network_admin_plugin_action_links( $links, $file ) {
		
		if ( FB_WM_BASENAME == $file  )
			$links[] = '<a href="settings.php?page=' . plugin_basename( __FILE__ ) . '">' . __( 'Settings' ) . '</a>';
		
		return $links;
	}
	
	public function add_settings_page () {
		
		if ( is_multisite() && is_plugin_active_for_network( self::$plugin ) ) {
			add_submenu_page(
				'settings.php',
				parent :: get_plugin_data( 'Name' ) . ' ' . __( 'Settings', FB_WM_TEXTDOMAIN ),
				parent :: get_plugin_data( 'Name' ),
				'manage_options',
				plugin_basename(__FILE__),
				array( $this, 'get_settings_page' )
			);
		} else {
			add_options_page(
				parent :: get_plugin_data( 'Name' ) . ' ' . __( 'Settings', FB_WM_TEXTDOMAIN ),
				parent :: get_plugin_data( 'Name' ),
				'manage_options',
				plugin_basename(__FILE__),
				array( $this, 'get_settings_page' )
			);
			add_action( 'contextual_help', array( $this, 'contextual_help' ), 10, 3 );
		}
	}
	
	public function get_settings_page() {
		
		if ( is_multisite() && is_plugin_active_for_network( self::$plugin ) )
			$action = 'edit.php?action=' . self::$option_string;
		else
			$action = 'options.php';
		?>
		<div class="wrap">
			<?php screen_icon('options-general'); ?>
			<h2><?php echo parent :: get_plugin_data( 'Name' ); ?></h2>
			
			<div id="poststuff">
			
				<div id="post-body" class="metabox-holder columns-2">
				
					<!-- main content -->
					<div id="post-body-content">
						
						<div class="meta-box-sortables ui-sortable">
							<form method="post" action="<?php echo $action; ?>">
								
							<?php do_action( self::$option_string . '_settings_page' );
				
var_dump($this->options);
							?>
							<p class="submit">
								<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />						</p>
						
							</form>
						</div> <!-- .meta-box-sortables .ui-sortable -->
						
					</div> <!-- post-body-content -->
					
					<!-- sidebar -->
					<div id="postbox-container-1" class="postbox-container">
						
						<div class="meta-box-sortables">
						<?php do_action( self::$option_string . '_settings_page_sidebar' ); ?>
						</div> <!-- .meta-box-sortables -->
						
					</div> <!-- #postbox-container-1 .postbox-container -->
					
				</div> <!-- #post-body .metabox-holder .columns-2 -->
				
				<br class="clear">
			</div> <!-- #poststuff -->
			
		</div>
		<?php
	}

}
$wp_maintenance_mode_options_page = WP_MaintenanceMode_Options_Page::get_object();