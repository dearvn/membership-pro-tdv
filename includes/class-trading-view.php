<?php
/**
 * TradingView class.
 *
 * @package MPTDV
 */

namespace MPTDV;

use MPTDV\Traits\Singleton;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hook some active with Membership Pro Pro plugin and integrate with TradingView.
 */
class TradingView {

	use Singleton;

    /**
     * Token Id
     *
     * @var string
     */
    private $token = '';
    
    /**
     * Session Id
     *
     * @var string
     */
    private $sessionid = '';
    
    private $urls = array(
        "tvcoins" => "https://www.tradingview.com/tvcoins/details/",
        "username_hint" => "https://www.tradingview.com/username_hint/",
        "list_users" => "https://www.tradingview.com/pine_perm/list_users/",
        "modify_access" => "https://www.tradingview.com/pine_perm/modify_user_expiration/",
        "add_access" => "https://www.tradingview.com/pine_perm/add/",
        "remove_access" => "https://www.tradingview.com/pine_perm/remove/",
        "pub_scripts" => "https://www.tradingview.com/pubscripts-get/",
        "pri_scripts" => "https://www.tradingview.com/pine_perm/list_scripts/",
        "pine_facade" => "https://pine-facade.tradingview.com/pine-facade/get/",
    );
    
    /**
     * Constructor
     *
     * Initialize plugin and registers actions and filters to be used
     *
     * @since  1.0.0
     */
    public function __construct() {
        $options = get_option( 'trading_view_option' );
        $session_id = !empty($options['session_id']) ? $options['session_id'] : '';

        if ($session_id) {
        
            $headers = array(
                'cookie' => 'sessionid='.$session_id
            );
        
            $test = wp_remote_get( $this->urls["tvcoins"], 
                array(
                    'headers' => $headers
                )
            );
            if ( wp_remote_retrieve_response_code( $test ) == 200 ) {
                $this->sessionid = $session_id;
            }
        }

        // adding a dropdown private charts to membership.
        add_action( 'ihc_filter_admin_section_edit_membership_after_membership_details', array( $this, 'add_tradingview_chart_ids' ), 10, 1 );
        // needing serialize array value to save database.
        add_action( 'ihc_admin_dashboard_after_top_menu', array( $this, 'convert_chart_id_value' ), 10);
        

        add_action( 'ump_action_admin_list_user_column_name_after_total_spend', array( $this, 'add_column_tradingview_username_header' ), 10);
        add_action( 'ump_action_admin_list_user_row_after_total_spend', array( $this, 'add_column_tradingview_username_body' ), 10, 1);
        
        // adding header label to user list.
        add_filter( 'manage_users_columns', array( $this, 'add_label_column_tradingview' ));
        // adding value column to user list.
        add_filter( 'manage_users_custom_column', array( $this, 'get_value_column_tradingview' ), 15, 3 );
        
        // hook payment
        //do_action( 'ihc_payment_completed', $paymentData['uid'], $paymentData['lid'], $this->levelData );
        //add_action( 'ihc_payment_completed', array( $this, 'add_username_to_trading_view' ), 10, 2);

        //do_action( 'ihc_action_before_after_order', $this->paymentOutputData ).
        // after order had been created we will add automatically to TradingView.
        add_action( 'ihc_action_before_after_order', array( $this, 'add_username_to_trading_view_after_save_order' ), 10, 1);
        
        // do_action( 'ihc_action_after_charge_payment', $this->paymentOutputData ).
        //add_action( 'ihc_action_after_charge_payment', array( $this, 'charge_payment_after_trading_view' ), 10, 1);     
        
        //do_action( 'ihc_action_payments_after_refund', $uid, $lid ).
        //do_action( 'ihc_action_payment_failed', $uid, $lid ).
        //do_action( 'ihc_action_after_subscription_delete', $uid, $lid ).
        // delete tradingview username from 3 actions above.
        add_action( 'ihc_action_after_subscription_delete', array( $this, 'delete_username_from_trading_view_after_subscription_delete' ), 10, 2);
        
        //do_action( 'ihc_action_after_cancel_subscription', $uid, $lid ).
        // delete tradingview username from this action.
        add_action( 'ihc_action_after_cancel_subscription', array( $this, 'delete_username_from_trading_view_after_subscription_delete' ), 10, 2);
        
        //do_action( 'ihc_action_pause_subscription', $uid, $lid ).
        // this action nothing todo.

        //do_action( 'ihc_action_resume_subscription', $uid, $lid ).
        // this action nothing todo.

        //do_action( 'ihc_action_cancel_subscription', $uid, $lid ).
        // delete tradingview username from this action.
        add_action( 'ihc_action_cancel_subscription', array( $this, 'delete_username_from_trading_view_after_subscription_delete' ), 10, 2);
        
        //do_action('ihc_new_subscription_action', $uid, $lid, $args ).
        //do_action( 'ihc_action_after_subscription_activated', $uid, $lid, $firstTime, $args ).
        //do_action( 'ihc_action_after_subscription_renew_activated', $uid, $lid, $args ).
        // update expire username TradingView when subscriptions was renewed.
        add_action( 'ihc_action_after_subscription_renew_activated', array( $this, 'update_username_from_trading_view_after_subscription_expire_changed' ), 10, 3);

        //do_action( 'ihc_action_after_subscription_first_time_activated', $uid, $lid, $args ).
        // update expire username TradingView when subscriptions was created.
        add_action( 'ihc_action_after_subscription_first_time_activated', array( $this, 'update_username_from_trading_view_after_subscription_expire_changed' ), 10, 3);
        
        //do_action('ihc_action_level_has_expired', $u_data->user_id, $u_data->level_id).
        add_action( 'ihc_action_level_has_expired', array( $this, 'update_username_from_trading_view_after_subscription_has_expired' ), 10, 2);
        
        //do_action( 'ihc_action_subscription_expired', $expiredSubscription['user_id'], $expiredSubscription['level_id'], $expiredSubscription ).
        add_action( 'ihc_action_subscription_expired', array( $this, 'update_username_from_trading_view_after_subscription_has_expired' ), 10, 2);
        
        //do_action( 'ihc_action_subscription_enter_grace_period', $expiredSubscription['uid'], $expiredSubscription['lid'], $expiredSubscription['expire_time'] ).
        add_action( 'ihc_action_subscription_enter_grace_period', array( $this, 'update_username_from_trading_view_after_subscription_enter_grace_period' ), 10, 3);
        
        //do_action( 'ihc_action_subscription_payment_due', $subscription['uid'], $subscription['lid'] ).
        add_action( 'ihc_action_subscription_payment_due', array( $this, 'update_username_from_trading_view_after_subscription_has_expired' ), 10, 2);
        
        //do_action( 'ihc_action_subscription_trial_expired', $subscription['uid'], $subscription['lid'] ).
        // this action nothing todo.
        //do_action( 'ihc_action_afters_delete_all_user_subscription', $uid ).
        // this action nothing todo.
    }


    
    /** 
     * Update tradingview_username after level expired.
     * 
     * @param int $user_id input value.
     * @param int $membership_id input value.
     * @param string $expire_time input value.
     * 
     * @return void
    */
    public function update_username_from_trading_view_after_subscription_has_expired($user_id, $membership_id) {
        // get tradingview_username from register page.
        $tradingview_username = get_the_author_meta( 'tradingview_username', $user_id );
        if (empty($tradingview_username)) {
            return;
        }

        // get all meta of membership.
        $data_meta = self::get_all_meta_for_membership($membership_id);

        if (empty($data_meta['trading_view_subscription_chart_ids'])) {
            return;
        }

        $pine_ids = json_decode($data_meta['trading_view_subscription_chart_ids']);
        if (empty($pine_ids)) {
            return;
        }
        $access_type = !empty($data_meta['access_type']) ? $data_meta['access_type'] : '';
        
        if($access_type == 'regular_period') {
            // case regular_period.
            $access_regular_time_value = $data_meta['access_regular_time_value'];
            $access_regular_time_type = $data_meta['access_regular_time_type'];
            $subscription_end = date('Y-m-d', indeed_get_unixtimestamp_with_timezone());

            if ($access_regular_time_type == 'D') {
                $subscription_end = date('Y-m-d', strtotime($subscription_end. " + $access_regular_time_value day"));
            } elseif ($access_regular_time_type == 'W') {
                $subscription_end = date('Y-m-d', strtotime($subscription_end. " + $access_regular_time_value week"));
            } elseif ($access_regular_time_type == 'M') {
                $subscription_end = date('Y-m-d', strtotime($subscription_end. " + $access_regular_time_value month"));
            } elseif ($access_regular_time_type == 'Y') {
                $subscription_end = date('Y-m-d', strtotime($subscription_end. " + $access_regular_time_value year"));
            }
            $expired_date = date_i18n( 'Y-m-d\T23:59:59.999\Z', strtotime($subscription_end));
            $is_validate_username = self::validate_username($tradingview_username);
            if (!$is_validate_username) {
                return;
            }
            
            foreach($pine_ids as $pine_id) {
                $access = self::get_access_details($tradingview_username, trim($pine_id));
                $result[] = self::add_access($access, $expired_date);
            }
        } else {
            foreach($pine_ids as $pine_id) {
                self::remove_access(trim($pine_id), $tradingview_username);
            }   
        }   
    }
    
    /** 
     * Update tradingview_username after expire was changed.
     * 
     * @param int $user_id input value.
     * @param int $membership_id input value.
     * @param string $expire_time input value.
     * 
     * @return void
    */
    public function update_username_from_trading_view_after_subscription_enter_grace_period($user_id, $membership_id, $expire_time) {
        // get tradingview_username from register page.
        $tradingview_username = get_the_author_meta( 'tradingview_username', $user_id );
        if (empty($tradingview_username)) {
            return;
        }

        // get all meta of membership.
        $data_meta = self::get_all_meta_for_membership($membership_id);

        if (empty($data_meta['trading_view_subscription_chart_ids'])) {
            return;
        }

        $expired_date = date_i18n( 'Y-m-d\T23:59:59.999\Z', strtotime($expire_time));
        $is_validate_username = self::validate_username($tradingview_username);
        if (!$is_validate_username) {
            return;
        }
        
        $pine_ids = json_decode($data_meta['trading_view_subscription_chart_ids']);
        if (empty($pine_ids)) {
            return;
        }

        foreach($pine_ids as $pine_id) {
            $access = self::get_access_details($tradingview_username, trim($pine_id));
            $result[] = self::add_access($access, $expired_date);
        }
    }

    /** 
     * Update tradingview_username after expire was changed.
     * 
     * @param int $user_id input value.
     * @param int $membership_id input value.
     * @param array $args input value.
     * 
     * @return void
    */
    public function update_username_from_trading_view_after_subscription_expire_changed($user_id, $membership_id, $args) {
        // get tradingview_username from register page.
        $tradingview_username = get_the_author_meta( 'tradingview_username', $user_id );
        if (empty($tradingview_username)) {
            return;
        }

        // get all meta of membership.
        $data_meta = self::get_all_meta_for_membership($membership_id);

        if (empty($data_meta['trading_view_subscription_chart_ids'])) {
            return;
        }

        if (!empty($args['expire_time'])) {
            $expired_date = date_i18n( 'Y-m-d\T23:59:59.999\Z', strtotime($args['expire_time']));
            $is_validate_username = self::validate_username($tradingview_username);
            if (!$is_validate_username) {
                return;
            }
            
            $pine_ids = json_decode($data_meta['trading_view_subscription_chart_ids']);
            if (empty($pine_ids)) {
                return;
            }

            foreach($pine_ids as $pine_id) {
                $access = self::get_access_details($tradingview_username, trim($pine_id));
                $result[] = self::add_access($access, $expired_date);
            }
        } else {
            // add username to TradingView
            self::add_username_to_trading_view($tradingview_username, $data_meta);
        }
    }

    
    /**
     * Remove tradingview_username after subscription expired.
     * Deleted cases: order refund, order failed.
     * Action hook: ihc_action_payments_after_refund, ihc_action_payment_failed
     * @param int $user_id input value.
     * @param int $membership_id input value.
     * @param array $data input value.
     * 
     * @return void
     */
    public function delete_username_from_trading_view_after_subscription_expired($user_id, $membership_id, $data) {
        // get tradingview_username from register page.
        $tradingview_username = get_the_author_meta( 'tradingview_username', $user_id );
        if (empty($tradingview_username)) {
            return;
        }

        $meta_value = self::get_one_meta_for_membership($membership_id, 'trading_view_subscription_chart_ids');

        if (empty($meta_value)) {
            return;
        }

        $pine_ids = json_decode($meta_value);

        if (empty($pine_ids)) {
            return;
        }
        
        foreach($pine_ids as $pine_id) {
            self::remove_access(trim($pine_id), $tradingview_username);
        }
    }

    /**
     * Remove tradingview_username after subscription had been deleted.
     * Deleted cases: order refund, order failed.
     * Action hook: ihc_action_payments_after_refund, ihc_action_payment_failed
     * @param int $user_id input value.
     * @param int $membership_id input value.
     * 
     */
    public function delete_username_from_trading_view_after_subscription_delete($user_id, $membership_id) {
        // get tradingview_username from register page.
        $tradingview_username = get_the_author_meta( 'tradingview_username', $user_id );
        if (empty($tradingview_username)) {
            return;
        }

        $meta_value = self::get_one_meta_for_membership($membership_id, 'trading_view_subscription_chart_ids');

        if (empty($meta_value)) {
            return;
        }

        $pine_ids = json_decode($meta_value);

        if (empty($pine_ids)) {
            return;
        }
        
        foreach($pine_ids as $pine_id) {
            self::remove_access(trim($pine_id), $tradingview_username);
        }
    }

    /** 
     * Add username to TradingView after the order had saved.
     * 
     * @param array $data input value.
     * 
     * @return void
     */
    public function add_username_to_trading_view_after_save_order($data) {
        // nothing todo if user id or membership id empty.
        if (empty($data['uid']) || empty($data['lid'])) {
            return;
        }
        $membership_id = $data['lid'];
        $user_id = $data['uid'];

        // get tradingview_username from register page.
        $tradingview_username = get_the_author_meta( 'tradingview_username', $user_id );
        if (empty($tradingview_username)) {
            return;
        }

        // get all meta of membership.
        $data_meta = self::get_all_meta_for_membership($membership_id);

        // add username to TradingView
        self::add_username_to_trading_view($tradingview_username, $data_meta);
    }

    /**
     * Add label column tradingview_username.
     * 
     * @param array $columns input value.
     * 
     * @return array as array.
     */
    public function add_label_column_tradingview( $columns ) {
        $columns['tradingview_username'] = 'TDV Username';
        return $columns;
    }


    /**
     * Get value of tradingview_username column.
     * 
     * @param string $content input param.
     * @param string $column input param.
     * @param int $user_id input param.
     * 
     * @return string as content of column.
     */
    public function get_value_column_tradingview( $content, $column, $user_id ) {

        if ( 'tradingview_username' === $column ) {
            $content = get_the_author_meta( 'tradingview_username', $user_id );
        }

        return $content;
    }

    /**
     * Add header of tradingview_username column.
     * 
     * @return void
     */
    public function add_column_tradingview_username_header() {
        ?>
        <th class="manage-column ihc-users-table-col2">
            <?php esc_html_e('TDV Username', 'ihc');?>
        </th>
        <?php
    }


    /**
     * Show value of tradingview username to body.
     * 
     * @param int $user_id input value.
     * 
     * @return void
     */
    public function add_column_tradingview_username_body($user_id) {
        $tradingview_username = get_the_author_meta( 'tradingview_username', $user_id );
        ?>
        <td><?php
            echo $tradingview_username;
        ?></span></td>
        <?php
    }

    /**
     * Add username to TradingView.
     * 
     * @param int $lid input value.
     * @param array $data input value.
     * 
     * @return void
     */
    public function charge_payment_after_trading_view( $data ) {
        
    }

    /**
     * Add username to TradingView.
     * 
     * @param int $lid input value.
     * @param array $data input value.
     * 
     * @return void
     */
    public function add_username_to_trading_view( $tradingview_username, $data_meta = [] ) {
        
        $is_validate_username = self::validate_username($tradingview_username);
        if (!$is_validate_username) {
            return;
        }

        if (empty($data_meta['trading_view_subscription_chart_ids'])) {
            return;
        }

        $pine_ids = json_decode($data_meta['trading_view_subscription_chart_ids']);
        if (empty($pine_ids)) {
            return;
        }
        
        $access_type = !empty($data_meta['access_type']) ? $data_meta['access_type'] : '';
        $expired_date = null;
        if ($access_type != 'unlimited') {
            // case limited.
            if ($access_type == 'limited') {
                $access_limited_time_value = $data_meta['access_limited_time_value'];
                $access_limited_time_type = $data_meta['access_limited_time_type'];
                $subscription_end = date('Y-m-d', indeed_get_unixtimestamp_with_timezone());

                if ($access_limited_time_type == 'D') {
                    $subscription_end = date('Y-m-d', strtotime($subscription_end. " + $access_limited_time_value day"));
                } elseif ($access_limited_time_type == 'W') {
                    $subscription_end = date('Y-m-d', strtotime($subscription_end. " + $access_limited_time_value week"));
                } elseif ($access_limited_time_type == 'M') {
                    $subscription_end = date('Y-m-d', strtotime($subscription_end. " + $access_limited_time_value month"));
                } elseif ($access_limited_time_type == 'Y') {
                    $subscription_end = date('Y-m-d', strtotime($subscription_end. " + $access_limited_time_value year"));
                }
                $expired_date = (string)date_i18n( 'Y-m-d\T23:59:59.999\Z', strtotime($subscription_end));
            } elseif($access_type == 'regular_period') {
                // case regular_period.
                $access_regular_time_value = $data_meta['access_regular_time_value'];
                $access_regular_time_type = $data_meta['access_regular_time_type'];
                $subscription_end = date('Y-m-d', indeed_get_unixtimestamp_with_timezone());

                if ($access_regular_time_type == 'D') {
                    $subscription_end = date('Y-m-d', strtotime($subscription_end. " + $access_regular_time_value day"));
                } elseif ($access_regular_time_type == 'W') {
                    $subscription_end = date('Y-m-d', strtotime($subscription_end. " + $access_regular_time_value week"));
                } elseif ($access_regular_time_type == 'M') {
                    $subscription_end = date('Y-m-d', strtotime($subscription_end. " + $access_regular_time_value month"));
                } elseif ($access_regular_time_type == 'Y') {
                    $subscription_end = date('Y-m-d', strtotime($subscription_end. " + $access_regular_time_value year"));
                }
                $expired_date = date_i18n( 'Y-m-d\T23:59:59.999\Z', strtotime($subscription_end));
            } elseif($access_type == 'date_interval') {
                // case date_interval.
                $access_interval_end = $data_meta['access_interval_end'];
                $expired_date = date_i18n( 'Y-m-d\T23:59:59.999\Z', strtotime($access_interval_end));
            }
        }

        foreach($pine_ids as $pine_id) {
            $access = self::get_access_details($tradingview_username, trim($pine_id));
            $result[] = self::add_access($access, $expired_date);
        }
    }

    /**
     * Convert array chart id value to string before save.
     * 
     * @return void
     */
    public function convert_chart_id_value() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST['trading_view_subscription_chart_ids'] = json_encode($_POST['trading_view_subscription_chart_ids']);
        }
    }

    /**
	 * Add Username of TradingView to membership.
	 *
	 * @param array $level_data input value.
     * 
	 * @return void
	 */
	public function add_tradingview_chart_ids( $level_data ) {
        ?> 
        
        <div class="iump-form-line iump-no-border">
            <div class="row">
                <div class="col-xs-4">
                    <div class="input-group">
                    <?php
                        $charts = !empty($level_data['trading_view_subscription_chart_ids']) ?  (array)json_decode($level_data['trading_view_subscription_chart_ids']) : [];

                        $pine_ids = self::get_private_indicators();
                        if ($pine_ids) { ?>
                            <span class="input-group-addon"><?php esc_html_e('Charts', 'ihc');?></span>
                            <select name="trading_view_subscription_chart_ids[]" multiple class="ihc-form-element ihc-form-element-select ihc-form-select" >
                                <?php
                                    foreach ( $pine_ids as $key => $item){
                                        $selected = (is_array($charts) && in_array($item['id'], $charts)) ? 'selected' : ($item['id'] == $level_data['trading_view_subscription_chart_ids'] ? 'selected' : '');
                                        ?>
                                            <option value="<?php echo esc_attr($item['id']);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($item['name']);?></option>
                                        <?php
                                    }
                                ?>
                            </select>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <?php
    }
    
    /**
     * Get all private indicator from TradingView.
     * 
     * @return array value.
     */
    public function get_private_indicators() {
        $headers = array(
            'cookie' => 'sessionid='.$this->sessionid
        );

        $resp = wp_remote_get( $this->urls["pri_scripts"], 
            array(
                'headers' => $headers
            )
        );
        $result = [];
        if ( wp_remote_retrieve_response_code( $resp ) == 200 ) {
            $data = wp_remote_retrieve_body($resp);
            $result = (array)json_decode( $data );
        }

        if (empty($result)) {
            return [];
        }
        
        $headers['origin'] = 'https://www.tradingview.com';
        $headers['Content-Type'] = 'application/x-www-form-urlencoded';

        $resp = wp_remote_post( $this->urls["pub_scripts"], 
            array(
                'headers' => $headers,
                'body' => [
                    'scriptIdPart' => implode(",", $result),
                    'show_hidden' => true]
            )
        );
        $indicators = [];
        if ( wp_remote_retrieve_response_code( $resp ) == 200 ) {
            $data = wp_remote_retrieve_body($resp);
            $items = (array)json_decode( $data );
            foreach($items as $item) {
                $indicators[] = [
                    'id' => $item->scriptIdPart,
                    'name' => $item->scriptName
                ];
            }
        }
        
        return $indicators;
    }
    
    public function get_name_chart($pine_id) {
        $headers = array(
            'cookie' => 'sessionid='.$this->sessionid
        );

        $resp = wp_remote_get( $this->urls["pine_facade"]."{$pine_id}/1?no_4xx=true", 
            array(
                'headers' => $headers
            )
        );
        $result = [];
        if ( wp_remote_retrieve_response_code( $resp ) == 200 ) {
            $data = wp_remote_retrieve_body($resp);
            $result = (array)json_decode( $data );
        }
        
        return !empty($result['scriptName']) ? $result['scriptName'] : '';
    }
    
    /**
     * Get list user of a chart.
     * 
     * @param string $pine_id input value.
     * 
     * @return array value.
     */
    public function get_list_users($pine_id) {
        $payload = array(
            'pine_id' => $pine_id
        );

        $headers = array(
            'origin' => 'https://www.tradingview.com',
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Cookie' => 'sessionid='.$this->sessionid
        );
        $resp = wp_remote_post( $this->urls['list_users'].'?limit=30&order_by=-created', 
            array(
                'body'    => $payload,
                'headers' => $headers
            )
        );
        
        $result = [];
        if ( wp_remote_retrieve_response_code( $resp ) == 200 ) {
            $data = wp_remote_retrieve_body($resp);
            $result = (array)json_decode( $data );
        }
        
        return $result;
    }
    
    /**
     * Validate a username that valid on TradingView.
     * 
     * @param string $username input value.
     * 
     * @return boolean value.
     */
    public function validate_username($username) {
        $resp = wp_remote_get( $this->urls["username_hint"]."?s={$username}");
        if( is_wp_error( $resp ) || wp_remote_retrieve_response_code( $resp ) != 200 && wp_remote_retrieve_response_code( $resp ) != 201) {
            return false;
        }
        
        $data = wp_remote_retrieve_body($resp);
        $usersList = (array)json_decode( $data );
        
        $validUser = false;
        foreach( $usersList as $user) {
            $item = (array)$user;
            if (strtolower($item['username']) == strtolower($username)) {
                $validUser = true;
                break;
            }
        }
        return $validUser;
    }
    
    /**
     * Get info of a username in a chart script.
     * 
     * @param string $username input value.
     * @param string $pine_id input value.
     * 
     * @return array info of username.
     */
    public function get_access_details($username, $pine_id) {
        $payload = array(
            'pine_id' => $pine_id,
            'username' => $username,
        );

        $headers = array(
            'origin' => 'https://www.tradingview.com',
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Cookie' => 'sessionid='.$this->sessionid
        );
        $resp = wp_remote_post( $this->urls['list_users'].'?limit=10&order_by=-created', 
            array(
                'body'    => $payload,
                'headers' => $headers
            )
        );
        
        $access_details = array(
            'hasAccess' => false,
            'currentExpiration' => '',
            'noExpiration' => false,
            'pine_id' => $pine_id,
            'username' => $username,
        );
        if( is_wp_error( $resp ) || wp_remote_retrieve_response_code( $resp ) != 200 && wp_remote_retrieve_response_code( $resp ) != 201) {
            return $access_details;
        }
        
        $data = wp_remote_retrieve_body($resp);
        $usersList = (array)json_decode( $data );
        
        $users = $usersList['results'];

        if (empty($users)) {
            return $access_details;
        }
        
        $hasAccess = false;
        $noExpiration = false;
        $expiration = '';
        foreach( $users as $user) {
            $item = (array)$user;
            if (strtolower($item['username']) == strtolower($username)) {
                $hasAccess = true;
                if (!empty($item["expiration"])) {
                    $expiration = $item['expiration'];
                } else {
                    $noExpiration = true;
                }
                break;
            }
        }
        $access_details['hasAccess'] = $hasAccess;
        $access_details['noExpiration'] = $noExpiration;
        $access_details['currentExpiration'] = $expiration;
                
        return $access_details;
    }
    
    /**
     * Add username into TradingView.
     * 
     * @param array $access_details input value.
     * @param string $expiration input value.
     * 
     * @return boolean as value.
     */
    public function add_access($access_details, $expiration) {
        
        //$noExpiration = $access_details['noExpiration'];
        $access_details['expiration'] = $access_details['currentExpiration'];
        $access_details['status'] = 'Not Applied';
        $payload = array(
            'pine_id' => $access_details['pine_id'],
            'username_recip' => $access_details['username']
        );
        if (!empty($expiration)) {
            $payload['expiration'] = $expiration;
        } else {
            $payload['noExpiration'] = true;
        }
            
        $enpoint_type = $access_details['hasAccess'] ? 'modify_access' : 'add_access';

        $headers= array(
            'origin' => 'https://www.tradingview.com',
            'Content-Type' => 'application/x-www-form-urlencoded',
            'cookie' => 'sessionid='.$this->sessionid
        );
        
        $resp = wp_remote_post( $this->urls[$enpoint_type], 
            array(
                'body'    => $payload,
                'headers' => $headers
            )
        );
        
        if ( wp_remote_retrieve_response_code( $resp ) == 200 || wp_remote_retrieve_response_code( $resp ) == 201 ) {
            return true;
        }
            
        return false;
    }

    /**
     * Remove username fro TradingView.
     * 
     * @param string $pine_id input value.
     * @param string $username input value.
     * 
     * @return boolean value true or false.
     */
    public function remove_access($pine_id, $username) {
        $payload = array(
            'pine_id' => $pine_id,
            'username_recip' => $username
        );
        
        $headers = array(
            'origin' => 'https://www.tradingview.com',
            'Content-Type' => 'application/x-www-form-urlencoded',
            'cookie' =>  'sessionid='.$this->sessionid
        );
        
        $resp = wp_remote_post( $this->urls['remove_access'], 
            array(
                'body'    => $payload,
                'headers' => $headers
            )
        );
        if ( wp_remote_retrieve_response_code( $resp ) == 200 || wp_remote_retrieve_response_code( $resp ) == 201 ) {
            return true;
        }
        
        return false;
    }

    /**
     * Get all meta of membership by id.
     * 
     * @param int $membership_id input value.
     * 
     * @return array value of membership.
     */
    public function get_all_meta_for_membership( $membership_id )
    {
        global $wpdb;
        $dbPrefix = $wpdb->prefix;
        $query = $wpdb->prepare( "SELECT meta_key, meta_value
                                      FROM {$dbPrefix}ihc_memberships_meta
                                      WHERE membership_id=%d;", $membership_id );
        $all = $wpdb->get_results( $query );
        if ( !$all ){
            return [];
        }
        foreach ( $all as $object ){
            $meta[ $object->meta_key ] = $object->meta_value;
        }
        return $meta;
    }

    /**
     * Get value of meta membership by id and key.
     * 
     * @param int $membership_id input value.
     * @param string input value.
     * 
     * @return string as value of meta membership.
     */
    public function get_one_meta_for_membership( $membership_id, $meta_key )
    {
        global $wpdb;
        $dbPrefix = $wpdb->prefix;
        if ( empty($membership_id) || empty($meta_key) ){
            return null;
        }
        $query = $wpdb->prepare( "SELECT id,meta_value
                                      FROM {$dbPrefix}ihc_memberships_meta
                                      WHERE membership_id=%d
                                      AND meta_key=%s
                                      ORDER BY id DESC LIMIT 1;", $membership_id, $meta_key );
        $data = $wpdb->get_row( $query );
        if ( isset( $data->id ) ){
            return $data->meta_value;
        }
        return null;
    }
}
