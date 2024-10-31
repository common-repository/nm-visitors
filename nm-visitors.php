<?php
/*
Plugin Name: NM Visitors
Plugin URI: http://www.nitinmaurya.com/nm-visitors/
Description: To fetch the visitors.
Version: 1.0
Author: Nitin Maurya
Author URI: http://www.nitinmaurya.com/
*/
if ( !class_exists( 'NmVisitors' ) ) {

if( ! class_exists( 'WP_List_Table' ) ) {
    //require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
    require_once( ABSPATH . 'wp-admin/includes/class-wp-screen.php' );//added
    require_once( ABSPATH . 'wp-admin/includes/screen.php' );//added
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
    require_once( ABSPATH . 'wp-admin/includes/template.php' );
}
class NmVisitors extends WP_List_Table {


	function __construct() {
	    add_action( 'admin_head', array( $this, 'admin_header' ) ); 
		add_action( 'admin_menu', array( $this, 'init' ) );
		if ( !is_admin() ) { add_action( 'init', array( $this, 'addVistors' ) ); }
		register_activation_hook( __FILE__, array( $this, 'plugin_activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'plugin_deactivation' ) );
		
	}


	/**
	 * add Menu page for Visitor Plugin.
	 * 
	 */
	public function init() {
		add_menu_page('NM Visitors', 'NM Visitors', 'administrator', 'nm_vistior', array( $this,'nm_vistior'), 'dashicons-format-links',90);
		
	}
	/**
	 * List of vistors
	 * @return String List of visitors
	 */
	public function nm_vistior() {
		
		include('visitors_list.php');
		$visitorListTable = new Visitor_List_Table();
        $visitorListTable->prepare_items();
		?>
        <div class="wrap">
        <h1>Visitors</h1>
        <?php $visitorListTable->display(); ?>
        </div> 
	<?php 
	}//end of nm_vistior;
	


	/**
	 * Insert data into database.
	 * 
	 */
	public function addVistors(){
		global $wpdb,$post;
		$table_name		= $wpdb->prefix.'nmvisitors';
		$currentURL		= $this->get_protocol().'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		
		$ip_address		= $this->get_client_ip();
		$country 		= $this->get_country($ip_address);
		$visit_date		= date('Y-m-d H:i:s');
		
		
		$webUrlData 	= $wpdb->get_results("SELECT * FROM $table_name WHERE 
										  pageurl='".trim( sanitize_text_field($currentURL))."' and ip_address='".$ip_address."'");

		if(count($webUrlData)<=0){
			$current_user 	= wp_get_current_user();
			$user_id		= (empty($current_user->ID)?0:$current_user->ID);
			$wpdb->insert($table_name, array('pageurl' 		=>$currentURL,
											 'ip_address' 	=>$ip_address,
											 'country' 		=>$country ,
											 'user_id' 		=>$user_id,
											 'visit_date' 	=>$visit_date));
		} //endif
		  
		
	  
	}

	
	/**
	 * Function to get the client IP address
	 * @param  String $ip Ip address
	 * @return String     Country Name
	 */
	public function get_country($ip) {
		$details = json_decode(file_get_contents("http://ipinfo.io/{$ip}"));
		return (empty($details->country)?'-':$details->country);
	}

	/**
	 * Function to get the client IP address
	 * @return String return the ip address
	 */
	public function get_client_ip() {
	    $ipaddress = '';
	    if (getenv('HTTP_CLIENT_IP'))
	        $ipaddress = getenv('HTTP_CLIENT_IP');
	    else if(getenv('HTTP_X_FORWARDED_FOR'))
	        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
	    else if(getenv('HTTP_X_FORWARDED'))
	        $ipaddress = getenv('HTTP_X_FORWARDED');
	    else if(getenv('HTTP_FORWARDED_FOR'))
	        $ipaddress = getenv('HTTP_FORWARDED_FOR');
	    else if(getenv('HTTP_FORWARDED'))
	       $ipaddress = getenv('HTTP_FORWARDED');
	    else if(getenv('REMOTE_ADDR'))
	        $ipaddress = getenv('REMOTE_ADDR');
	    else
	        $ipaddress = 'UNKNOWN';
	    return $ipaddress;
	}
	/**
	* get_protocol Function to get the product of current page
	*/
	public function get_protocol() {
      $protocol = 'http';
      if ( isset( $_SERVER["HTTPS"] ) && strtolower( $_SERVER["HTTPS"] ) == "on" ) {
          $protocol .= "s";
      }
      return $protocol;
	 } // function to get the product of current page

	/**
	 * Activate the plugin
	 * @static
	 */
	public static function plugin_activation() {
		global $wpdb;
            $table_name=$wpdb->prefix.'nmvisitors';
            $sql="CREATE TABLE $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            pageurl TEXT NOT NULL,
            ip_address varchar(255) NOT NULL,
            country varchar(255) NOT NULL,
            user_id int(11) NOT NULL,
            visit_date DATETIME NOT NULL,
            UNIQUE KEY id (id)
            );";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );
		
	}
	
	/**
	 * Removes all connection options
	 * @static
	 */
	public static function plugin_deactivation( ) {
		global $wpdb;
            $table_name=$wpdb->prefix.'nmvisitors';
            $sql='DROP TABLE '.$table_name;
                $wpdb->query($sql);
	}






}


/**
 * Create object of class
 */
new NmVisitors();

}

?>