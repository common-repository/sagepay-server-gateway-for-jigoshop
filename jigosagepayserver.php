<?php
/**
 * Plugin Name: SagePay Server Gateway for Jigoshop
 * Plugin URI: http://www.patsatech.com
 * Description: Jigoshop Plugin for accepting payment through SagePay Server Gateway.
 * Version: 1.0.1
 * Author: PatSaTECH
 * Author URI: http://www.patsatech.com
 * Contributors: patsatech
 * Requires at least: 3.5
 * Tested up to: 4.0
 *
 * Text Domain: patsatech-jigo-sagepay-server
 * Domain Path: /lang/
 *
 * @package SagePay Server Gateway for Jigoshop
 * @author PatSaTECH
 */

add_action('plugins_loaded', 'init_jigoshop_sagepayserver', 0);

function init_jigoshop_sagepayserver() {

    if ( ! class_exists( 'jigoshop_payment_gateway' ) ) { return; }
    
	load_plugin_textdomain( 'patsatech-jigo-sagepay-server', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
	
	class jigoshop_sagepayserver extends jigoshop_payment_gateway{
		
		public function __construct() {
			
			parent::__construct();
			
	        $this->id               = 'sagepayserver';
	        $this->method_title     = __('SagePay Server', 'patsatech-jigo-sagepay-server');
		    $this->icon 			= plugins_url() . "/" . plugin_basename( dirname(__FILE__)) . '/images/sagepay.png';
	        $this->has_fields       = false;
	        $this->notify_url   	= jigoshop_request_api::query_request('?js-api=JS_Gateway_SagePayServer', false);
			
	        // variables
			$this->enabled			= Jigoshop_Base::get_options()->get_option('jigoshop_sagepayserver_enabled');
	        $this->title            = Jigoshop_Base::get_options()->get_option('jigoshop_sagepayserver_title');
	        $this->description      = Jigoshop_Base::get_options()->get_option('jigoshop_sagepayserver_description');
	        $this->vendor_name      = Jigoshop_Base::get_options()->get_option('jigoshop_sagepayserver_vendorname');
	        $this->mode             = Jigoshop_Base::get_options()->get_option('jigoshop_sagepayserver_mode');
	        $this->transtype        = Jigoshop_Base::get_options()->get_option('jigoshop_sagepayserver_transtype');
	        $this->paymentpage      = Jigoshop_Base::get_options()->get_option('jigoshop_sagepayserver_paymentpage');
	        $this->iframe        	= Jigoshop_Base::get_options()->get_option('jigoshop_sagepayserver_iframe');
			$this->cardtypes		= Jigoshop_Base::get_options()->get_option('jigoshop_sagepayserver_cardtypes');
	        $this->currency         = apply_filters( 'jigoshop_multi_currencies_currency', Jigoshop_Base::get_options()->get_option('jigoshop_currency') );
			
			if( $this->mode == 'test' ){
				$this->gateway_url = 'https://test.sagepay.com/gateway/service/vspserver-register.vsp';
			}else if( $this->mode == 'live' ){
				$this->gateway_url = 'https://live.sagepay.com/gateway/service/vspserver-register.vsp';
			}
	        
	        // actions
			add_action( 'init', array( $this, 'successful_request') );
			add_action( 'receipt_sagepayserver', array( $this, 'receipt_page' ) );
			
		}
		
		/**
		* Default Option settings for WordPress Settings API using the Jigoshop_Options class
		*
		* These will be installed on the Jigoshop_Options 'Payment Gateways' tab by the parent class 'jigoshop_payment_gateway'
		*
		*/
		protected function get_default_options() {
			
			$defaults = array();
			
			// Define the Section name for the Jigoshop_Options
			$defaults[] = array( 'name' => __('SagePay Server', 'patsatech-jigo-sagepay-server'), 'type' => 'title', 'desc' => __('SagePay Server works by processing Credit Cards in i-Frame or on SagePay\'s secure site.', 'patsatech-jigo-sagepay-server') );
			
			// List each option in order of appearance with details
			$defaults[] = array(
				'name'		=> __('Enable SagePay Server','patsatech-jigo-sagepay-server'),
				'desc' 		=> '',
				'tip' 		=> '',
				'id' 		=> 'jigoshop_sagepayserver_enabled',
				'std' 		=> 'yes',
				'type' 		=> 'checkbox',
				'choices'	=> array(
									'no'	=> __('No', 'patsatech-jigo-sagepay-server'),
									'yes'	=> __('Yes', 'patsatech-jigo-sagepay-server')
									)
			);
			
			$defaults[] = array(
				'name'		=> __('Enable i-Frame Mode','patsatech-jigo-sagepay-server'),
				'desc' 		=> 'Make sure your site is SSL Protected before using this feature.',
				'tip' 		=> '',
				'id' 		=> 'jigoshop_sagepayserver_iframe',
				'std' 		=> 'yes',
				'type' 		=> 'checkbox',
				'choices'	=> array(
					'no'			=> __('No', 'patsatech-jigo-sagepay-server'),
					'yes'			=> __('Yes', 'patsatech-jigo-sagepay-server')
				)
			);
			
			$defaults[] = array(
				'name'		=> __('Method Title','patsatech-jigo-sagepay-server'),
				'desc' 		=> '',
				'tip' 		=> __('This controls the title which the user sees during checkout.','patsatech-jigo-sagepay-server'),
				'id' 		=> 'jigoshop_sagepayserver_title',
				'std' 		=> __('SagePay Server','patsatech-jigo-sagepay-server'),
				'type' 		=> 'text'
			);
			
			$defaults[] = array(
				'name'		=> __('Description','patsatech-jigo-sagepay-server'),
				'desc' 		=> '',
				'tip' 		=> __('This controls the description which the user sees during checkout.','patsatech-jigo-sagepay-server'),
				'id' 		=> 'jigoshop_sagepayserver_description',
				'std' 		=> __("Pay via SagePay Server; Enter your Credit Card Details Below", 'patsatech-jigo-sagepay-server'),
				'type' 		=> 'longtext'
			);
			
			$defaults[] = array(
				'name'		=> __('Vendor Name','patsatech-jigo-sagepay-server'),
				'desc' 		=> __('Please enter your vendor name provided by SagePay','patsatech-jigo-sagepay-server'),
				'tip' 		=> '',
				'id' 		=> 'jigoshop_sagepayserver_vendorname',
				'std' 		=> '',
				'type' 		=> 'text'
			);
			
			$defaults[] = array(
				'name'		=> __('Mode Type','patsatech-jigo-sagepay-server'),
				'desc' 		=> __( 'Select Test or Live modes.', 'patsatech-jigo-sagepay-server' ),
				'tip' 		=> '',
				'id' 		=> 'jigoshop_sagepayserver_mode',
				'std' 		=> 'simulator',
				'type' 		=> 'select',
				'choices'	=> array(
									'test' => 'Test',
									'live' => 'Live'
									)
			);
			
			$defaults[] = array(
				'name'		=> __('Payment Page Type','patsatech-jigo-sagepay-server'),
				'desc' 		=> __( 'This is used to indicate what type of payment page should be displayed. <br>LOW returns simpler payment pages which have only one step and minimal formatting. Designed to run in i-Frames. <br>NORMAL returns the normal card selection screen. We suggest you disable i-Frame if you select NORMAL.', 'patsatech-jigo-sagepay-server' ),
				'tip' 		=> '',
				'id' 		=> 'jigoshop_sagepayserver_paymentpage',
				'std' 		=> 'LOW',
				'type' 		=> 'select',
				'choices'	=> array( 
									'LOW' => 'LOW',
									'NORMAL' => 'NORMAL'
									)
			);
			
			$defaults[] = array(
				'name'		=> __('Transaction Type','patsatech-jigo-sagepay-server'),
				'desc' 		=> __( 'Select Payment, Deferred or Authenticated.', 'patsatech-jigo-sagepay-server' ),
				'tip' 		=> '',
				'id' 		=> 'jigoshop_sagepayserver_transtype',
				'std' 		=> 'PAYMENT',
				'type' 		=> 'select',
				'choices'	=> array(
									'PAYMENT' => __('Payment', 'patsatech-jigo-sagepay-server'), 
									'DEFFERRED' => __('Deferred', 'patsatech-jigo-sagepay-server'), 
									'AUTHENTICATE' => __('Authenticate', 'patsatech-jigo-sagepay-server')
									)
			);
			
			return $defaults;
		}
		
		/**
		 * There are no payment fields for sagepayserver, but we want to show the description if set.
		 **/
		function payment_fields() {
			if ($jigoshop_sagepayserver_description = get_option('jigoshop_sagepayserver_description')) echo wpautop(wptexturize($jigoshop_sagepayserver_description));
		}
	    
		/**
		 * Generate the sagepayserver button link
		 **/
	    public function generate_sagepayserver_form( $order_id ) {
			
	        $order = new jigoshop_order( $order_id );
			
			if($this->iframe == 'yes'){
				
				return '<iframe src="'. esc_url( get_transient('sagepay_server_next_url') ) .'" name="sagepayserver_payment_form" width="100%" height="900px" ></iframe>';
				
			}else{
				
				return '<form action="'.esc_url( get_transient('sagepay_server_next_url') ).'" method="post" id="sagepayserver_payment_form">
						<input type="submit" class="button alt" id="submit_sagepayserver_payment_form" value="'.__('Submit', 'patsatech-jigo-sagepay-server').'" /> <a class="button cancel" href="'.esc_url( $order->get_cancel_order_url() ).'">'.__('Cancel order &amp; restore cart', 'patsatech-jigo-sagepay-server').'</a>
					</form>
					<script type="text/javascript">
						jQuery(document).ready(function($){
							$("body").block({
								message: "<img src=\"'.esc_url( jigoshop::plugin_url() ).'/assets/images/ajax-loader.gif\" alt=\"Redirecting...\" style=\"float:left; margin-right: 10px;\" />'.__('Thank you for your order. We are now redirecting you to verify your card.', 'patsatech-jigo-sagepay-server').'",
								overlayCSS:
								{
									background: "#fff",
									opacity: 0.6
								},
								css: {
								        padding:        20,
										textAlign:      "center",
										color:          "#555",
										border:         "3px solid #aaa",
										backgroundColor:"#fff",
										cursor:         "wait",
										lineHeight:		"32px"
									}
							});
							$("#submit_sagepayserver_payment_form").click();
						});
					</script>';
				
			}
			
		}
	    
		/**
		* 
	    * process payment
	    * 
	    */
	    function process_payment( $order_id ) {
			
	        $order = new jigoshop_order( $order_id );
	        
	        $time_stamp = date("ymdHis");
	        $orderid = $this->vendor_name . "-" . $time_stamp . "-" . $order_id;
	        
			$basket = '';
			
			// If prices include tax, send the whole order as a single item
			if(Jigoshop_Base::get_options()->get_option('jigoshop_prices_include_tax') == 'yes'){
				
				$item_loop = 0;
				
				$item_names = array();
				
				foreach($order->items as $item){
					$_product = $order->get_product_from_item($item);
					$title = $_product->get_title();
					
					//if variation, insert variation details into product title
					if($_product instanceof jigoshop_product_variation){
						$title .= ' ('.jigoshop_get_formatted_variation($_product, $item['variation'], true).')';
					}
					
					$item_names[] = $title.' x '.$item['qty'];
				}
				
				$item_cost = number_format($order->order_total - $order->order_shipping - $order->order_shipping_tax + $order->order_discount, 2, '.', '');
				
				$item_loop++;
				$basket .= str_replace(':',' = ', sprintf(__('Order %s', 'patsatech-jigo-sagepay-server'), $order->get_order_number()).' - '.implode(', ', $item_names) ).':1:'.$item_cost.':---:'.$item_cost.':'.$item_cost;
				
				if(($order->order_shipping + $order->order_shipping_tax) > 0){
					$item_loop++;
					$basket .= ':Shipping Cost:---:---:---:---:'.number_format($order->order_shipping + $order->order_shipping_tax, 2, '.', '');
				}
				
				// Discount
				if ( $order->order_discount > 0 ){
					$item_loop++;
					$basket .= ':Discount:---:---:---:---:-'.number_format((float)$order->order_discount, 2);
				}
				
			} else {
				
				// Cart Contents
				$item_loop = 0;
				foreach($order->items as $item){
					
					$_product = $order->get_product_from_item($item);
					
					if($_product->exists() && $item['qty']){
						$item_loop++;
						$title = $_product->get_title();
						
						//if variation, insert variation details into product title
						if($_product instanceof jigoshop_product_variation){
							$title .= ' ('.jigoshop_get_formatted_variation($_product, $item['variation'], true).')';
						}
						
						$cost = number_format((float)$item['cost']*$item['qty'], 2);
						
						if($item_loop > 1){
							$basket .= ':';
						}
						
						$sku = '';
						if ( $_product->get_sku() ) {
							$sku = '['.$_product->get_sku().']';
						}
						
						$basket .= str_replace(':',' = ',$sku).str_replace(':',' = ',$title).':'.$item['qty'].':'.$item['cost'].':---:'.$cost.':'.$cost;
						
					}
					
				}
				
				// Shipping Cost
				if(jigoshop_shipping::is_enabled() && $order->order_shipping > 0){
					$item_loop++;
					$basket .= ':'.__( 'Shipping', 'patsatech-jigo-sagepay-server' ).':---:---:---:---:'.number_format((float)$order->order_shipping, 2);
				}
				
				// Tax
				if ( $order->get_total_tax(false, false) > 0 ) {
					$item_loop++;
					$basket .= ':Tax:---:---:---:---:'.$order->get_total_tax(false, false);
				}
				
				// Discount
				if ( $order->order_discount > 0 ){
					$item_loop++;
					$basket .= ':Discount:---:---:---:---:-'.number_format((float)$order->order_discount, 2);
				}
				
			}
			
			$item_loop++;
			
			$basket .= ':Order Total:---:---:---:---:'.$order->order_total;
			
			$basket = $item_loop.':'.$basket;
			
	        $sd_arg['ReferrerID'] 			= 'CC923B06-40D5-4713-85C1-700D690550BF';
	        $sd_arg['Amount'] 				= $order->order_total;
	        $sd_arg['CustomerEMail'] 		= $order->billing_email;
	        $sd_arg['BillingSurname'] 		= $order->billing_last_name;
	        $sd_arg['BillingFirstnames'] 	= $order->billing_first_name;
	        $sd_arg['BillingAddress1'] 		= $order->billing_address_1;
	        $sd_arg['BillingAddress2'] 		= $order->billing_address_2;
	        $sd_arg['BillingCity'] 			= $order->billing_city;
			if( $order->billing_state == 'US' ){
	        	$sd_arg['BillingState'] 		= $order->billing_state;
			}else{
	        	$sd_arg['BillingState'] 		= '';
			}
	        $sd_arg['BillingPostCode'] 		= $order->billing_postcode;
	        $sd_arg['BillingCountry'] 		= $order->billing_country;
	        $sd_arg['BillingPhone'] 		= $order->billing_phone;
	        $sd_arg['DeliverySurname'] 		= $order->shipping_last_name;
	        $sd_arg['DeliveryFirstnames'] 	= $order->shipping_first_name;
	        $sd_arg['DeliveryAddress1'] 	= $order->shipping_address_1;
	        $sd_arg['DeliveryAddress2'] 	= $order->shipping_address_2;
	        $sd_arg['DeliveryCity'] 		= $order->shipping_city;
			if( $order->shipping_state == 'US' ){
	        	$sd_arg['DeliveryState'] 		= $order->shipping_state;
			}else{
	        	$sd_arg['DeliveryState'] 		= '';
			}
	        $sd_arg['DeliveryPostCode'] 	= $order->shipping_postcode;
	        $sd_arg['DeliveryCountry'] 		= $order->shipping_country;
	        $sd_arg['DeliveryPhone'] 		= $order->billing_phone;
	        $sd_arg['Description'] 			= sprintf( __('Order #%s' , 'patsatech-jigo-sagepay-server' ), $order->id);
	        $sd_arg['Currency'] 			= $this->currency;
	        $sd_arg['VPSProtocol'] 			= 3.00;
	        $sd_arg['Vendor'] 				= $this->vendor_name;
	        $sd_arg['TxType'] 				= $this->transtype;
	        $sd_arg['VendorTxCode'] 		= $orderid;    
	        $sd_arg['Profile'] 				= $this->paymentpage;
	        $sd_arg['NotificationURL'] 		= $this->notify_url;
			$sd_arg['Basket'] 				= $basket;    
			
			$post_values = "";
	        foreach( $sd_arg as $key => $value ) {
	            $post_values .= "$key=" . urlencode( $value ) . "&";
	        }
	        $post_values = rtrim( $post_values, "& " );
	        
	        $response = wp_remote_post($this->gateway_url, array( 
															'body' => $post_values,
															'method' => 'POST',
	                										'headers' => array( 'Content-Type' => 'application/x-www-form-urlencoded' ),
															'sslverify' => FALSE
															));
			
			if (!is_wp_error($response) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) { 
				
		        $resp = array();   
		        
		        $lines = preg_split( '/\r\n|\r|\n/', $response['body'] );
		        foreach($lines as $line){
	                $key_value = preg_split( '/=/', $line, 2 );
	                if(count($key_value) > 1)
	                    $resp[trim($key_value[0])] = trim($key_value[1]);
		        }
				
	        	if(isset($resp['Status'])) update_post_meta($order->id, 'Status', $resp['Status']);
				
			    if(isset($resp['StatusDetail'])) update_post_meta($order->id, 'StatusDetail', $resp['StatusDetail']);
				
			    if(isset($resp['VPSTxId'])) update_post_meta($order->id, 'VPSTxId', $resp['VPSTxId']);
				
			    if(isset($resp['CAVV'])) update_post_meta($order->id, 'CAVV', $resp['CAVV']);
				
			    if(isset($resp['SecurityKey'])) update_post_meta($order->id, 'SecurityKey', $resp['SecurityKey']);
				
			    if(isset($resp['TxAuthNo'])) update_post_meta($order->id, 'TxAuthNo', $resp['TxAuthNo']);
				
			    if(isset($resp['AVSCV2'])) update_post_meta($order->id, 'AVSCV2', $resp['AVSCV2']);
				
			    if(isset($resp['AddressResult'])) update_post_meta($order->id, 'AddressResult', $resp['AddressResult']);
				
			    if(isset($resp['PostCodeResult'])) update_post_meta($order->id, 'PostCodeResult', $resp['PostCodeResult']);
				
			    if(isset($resp['CV2Result'])) update_post_meta($order->id, 'CV2Result', $resp['CV2Result']);
				
			    if(isset($resp['3DSecureStatus'])) update_post_meta($order->id, '3DSecureStatus', $resp['3DSecureStatus']);
				
			    if(isset($orderid)) update_post_meta($order->id, 'VendorTxCode', $orderid );
		        
				if ( $resp['Status'] == "OK" ){
					
		            $order->add_order_note( $resp['StatusDetail'] );
					
					set_transient( 'sagepay_server_next_url', $resp['NextURL'] ); 
			        
					return array(
						'result' 	=> 'success',
						'redirect'	=>  add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(get_option('jigoshop_pay_page_id'))))
					);
					
				}else{
					
					if(isset($resp['StatusDetail'])){
						
						jigoshop::add_error( 'Transaction Failed. '.$resp['Status'].' - '.$resp['StatusDetail'] );
						
					}
		            else{
						
						jigoshop::add_error( 'Transaction Failed with '.$resp['Status'].' - unknown error.' );
						
					}
				}
				
			}else{
				
				jigoshop::add_error( __('Gateway Error. Please Notify the Store Owner about this error.', 'patsatech-jigo-sagepay-server'));
				
			}
		}
		
		/**
		 * receipt_page
		 **/
		function receipt_page( $order ) {
			
			echo '<p>'.__('Thank you for your order.', 'patsatech-jigo-sagepay-server').'</p>';
			
			echo $this->generate_sagepayserver_form( $order );
			
		}
		
		/**
		 * Successful Payment!
		 **/
		function successful_request() {
			
			if ( isset( $_REQUEST['VendorTxCode'] ) ) {
		            
			    $eoln = chr(13) . chr(10);
					
		        $params = array();
		            
		        $params['Status'] = 'INVALID';
		            
		        if( isset( $_POST['VendorTxCode'] ) ){
					
					$vendor_tx_code = explode('-',$_POST['VendorTxCode']);
						
					$order = new jigoshop_order( $vendor_tx_code[2] );
						
					if( $_POST['Status'] == 'OK' ){
						$params = array('Status' => 'OK', 'StatusDetail' => __('Transaction acknowledged.', 'patsatech-jigo-sagepay-server') );
						$checkout_redirect = apply_filters( 'jigoshop_get_checkout_redirect_page_id', jigoshop_get_page_id('thanks') );
						$redirect_url =  add_query_arg('key', $order->order_key, add_query_arg('order', $order->id, get_permalink( $checkout_redirect )));
						$order->add_order_note( __('Sagepay Direct payment completed', 'patsatech-jigo-sagepay-server') . ' ( ' . __('Transaction ID: ','patsatech-jigo-sagepay-server') . $_POST['VendorTxCode'] . ' )' );
						$order->payment_complete();
					}elseif( $_POST['Status'] == 'ABORT' ){
						$params = array('Status' => 'INVALID', 'StatusDetail' => __('Transaction aborted - ', 'patsatech-jigo-sagepay-server') . $_POST['StatusDetail'] );	
						wc_add_error(__('Aborted by user.', 'patsatech-jigo-sagepay-server'));
						$redirect_url = get_permalink( get_option( 'jigoshop_checkout_page_id' ) );
					}elseif( $_POST['Status'] == 'ERROR' ){
						$params = array('Status' => 'INVALID', 'StatusDetail' => __('Transaction errored - ', 'patsatech-jigo-sagepay-server') . $_POST['StatusDetail'] );
		            	$redirect_url = $order->get_cancel_order_url();
					}else{
						$params = array('Status' => 'INVALID', 'StatusDetail' => __('Transaction failed - ', 'patsatech-jigo-sagepay-server') . $_POST['StatusDetail'] );
		            	$redirect_url = $order->get_cancel_order_url();
					}
				}else{
		        	$params['StatusDetail'] =  __('SagePay Server, No VendorTxCode posted.', 'patsatech-jigo-sagepay-server');
				}
		        
		        if($this->iframe == 'yes'){
		        	$params['RedirectURL'] =  add_query_arg( 'page', urlencode( $redirect_url ), plugins_url() ."/" . plugin_basename( dirname(__FILE__) ) . '/includes/pages/redirect.php' );
				}else{
					$params['RedirectURL'] = $redirect_url;
				}
				
		        $param_string = "";
		        foreach( $params as $key => $value ) {
		        	$param_string .= $key . "=" . $value  . $eoln;
				}
				
		        ob_clean();
		        echo $param_string;
		        exit();
				
			}
			
		}
		
	} 
	
	/**
	 * Add the gateway to Jigoshop
	 **/
	function add_sagepayserver_gateway( $methods )
	{
	    $methods[] = 'jigoshop_sagepayserver';
	    return $methods;
	}
	add_filter('jigoshop_payment_gateways', 'add_sagepayserver_gateway', 10 );
	
}