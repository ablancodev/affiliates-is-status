<?php
/**
 * affiliates-is-status.php
 *
 * Copyright (c) 2011,2017 Antonio Blanco http://www.ablancodev.com
 *
 * This code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt.
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This header and all notices must be kept intact.
 *
 * @author Antonio Blanco
 * @package affiliates-is-status
 * @since affiliates-is-status 1.0.0
 *
 * Plugin Name: Affiliates is Status shortcode
 * Plugin URI: http://www.eggemplo.com
 * Description: Ex. [affiliates_is_affiliate_status status="pending"] Some text [/affiliates_is_affiliate_status]
 * Version: 1.0.0
 * Author: eggemplo
 * Author URI: http://www.ablancodev.com
 * License: GPLv3
 */

class Affiliates_Is_Status_Plugin {

	public static function init() {
		add_action ( 'init', array (
				__CLASS__,
				'wp_init' 
		) );
	}
	public static function wp_init() {

		add_shortcode( 'affiliates_is_affiliate_status', array( __CLASS__, 'affiliates_is_affiliate_status' ) );

	}

	/**
	 * Affiliate pending content shortcode.
	 * Renders the content if the current user is an affiliate pending.
	 *
	 * @param array $atts attributes (none used)
	 * @param string $content this is rendered for affiliates
	 */
	public static function affiliates_is_affiliate_status( $atts, $content = null ) {
	
		remove_shortcode( 'affiliates_is_affiliate_pending' );
		$content = do_shortcode( $content );
		add_shortcode( 'affiliates_is_affiliate_pending', array( __CLASS__, 'affiliates_is_affiliate_pending' ) );

		extract( shortcode_atts( array( 'status' => 'active' ), $atts ) );
		$output = "";
		if ( self::affiliates_user_is_affiliate_status( get_current_user_id(), $status ) ) {
			$output .= $content;
		}
		return $output;
	}

	/**
	 * Returns true if the user is an affiliate in the status indicated.
	 * @param int|object $user (optional) specify a user or use current if none given
	 * @param string $status the status to filter
	 */
	public static function affiliates_user_is_affiliate_status( $user_id = null, $status = 'active' ) {
		global $wpdb;
		$result = false;
		if ( is_user_logged_in() ) {
			if ( $user_id == null ) {
				$user = wp_get_current_user();
			} else {
				$user = get_user_by( 'id', $user_id );
			}
			if ( $user ) {
				$user_id = $user->ID;
				$affiliates_table = _affiliates_get_tablename( 'affiliates' );
				$affiliates_users_table = _affiliates_get_tablename( 'affiliates_users' );
				$affiliates = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM $affiliates_users_table LEFT JOIN $affiliates_table ON $affiliates_users_table.affiliate_id = $affiliates_table.affiliate_id WHERE $affiliates_users_table.user_id = %d AND $affiliates_table.status ='$status'", intval( $user_id ) )
						);
				$result = !empty( $affiliates );
			}
		}
		return $result;
	}
}
Affiliates_Is_Status_Plugin::init();
