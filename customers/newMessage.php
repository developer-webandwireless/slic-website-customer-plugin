<?php 
/* @package customer-account */
/* Creats Customer Account  */
/*
Plugin Name: Customer Account	
Description: Manage Customer Account
Author: Rajal Bhave
Version: 1
*/

//require_once('adminMenu.php');

class customerAccount extends WP_Widget{
	
	
	public function __construct(){
		$params = array(
		'name' => 'Customer Account ',
		'description' => 'Manages Customer Account '
		);
		parent::__construct('customerAccount', '', $params);
		
	}
	
public function widget($args, $instance){
	
	 extract($args);
	//var_dump($args);
	extract($instance);
	
	$title = apply_filters('widget_title', $title );
	$description = apply_filters('widget_description', $description);
	  echo $before_widget;
            echo $before_title . $title . $after_title; 
	  echo $after_widget;
 
 }
 
  //Backend form for widget
 public function form($instance){
	extract($instance);
	//widget configuration
}

}



function newMessage(){
if(is_user_logged_in()){

	$userId = 	get_current_user_id( );
	global $wpdb;
	


/*********** test code start ************	
	$customer_orders = get_posts( array(
    'numberposts' => -1,
    'meta_key'    => '_customer_user',
    'meta_value'  => get_current_user_id(),
    'post_type'   => wc_get_order_types(),
    'post_status' => array_keys( wc_get_order_statuses() ),
) );



$filters = array(
    'post_status' => 'any',
    'post_type' => 'shop_order',
    'posts_per_page' => 200,
    'paged' => 1,
    'orderby' => 'modified',
	'meta_value'  => get_current_user_id(),
    'order' => 'ASC'
);

$loop = new WP_Query($filters);

while ($loop->have_posts()) {
    $loop->the_post();
    $order = new WC_Order($loop->post->ID);

    foreach ($order->get_items() as $key => $lineItem) {

        //uncomment the following to see the full data
                echo '<pre>';
               // print_r($lineItem);
                echo '</pre>';
        echo '<br>' . 'Product Name : ' . $lineItem['name'] . '<br>';
        echo 'Product ID : ' . $lineItem['product_id'] . '<br>';
        if ($lineItem['variation_id']) {
            echo 'Product Type : Variable Product' . '<br>';
        } else {
            echo 'Product Type : Simple Product' . '<br>';
        }
    }
}




/*********** test code end ************/


	$current_date =  date('Y-m-d');
	$table_name = $wpdb->prefix . 'customers';
	//echo $current_date; echo date("Y - m - d", time());  
	$results = $wpdb->get_results( "SELECT * FROM {$table_name} 
									where user_id = {$userId} AND message_end_date >= {$current_date} 
									ORDER BY message_end_date ASC"  );
	//echo $wpdb->last_query;
	$remaining_message_credits = 0;
	
	if(count($results) > 0 ){
			foreach ($results as $result) {
					$max_message_credits =  $result->max_message_credits ;
					$used_message_credits =  $result->used_message_credits ;
					$remaining_message_credits = $max_message_credits - $used_message_credits;
					if ($remaining_message_credits > 0) { //echo 'credits';
						$customerOrderId = $result->id;
						$newMessageButton = '<input title="New Message" type="button" value="New Message" id="add-new-message" >' ;
						break;
					}
	
					else { //echo 'no credits';
						$newMessageButton = '<input class="disabled"  type="button" value="New Message" id="add-new-message" >' ;
					}
			}
	}else { //echo 'no credits';
						$newMessageButton = '<input class="disabled"  type="button" value="New Message" id="add-new-message" >' ;
	}

	
	

	$form_html = '
<div class="message-wrapper width-80">
			
			<div id="success" class="float-left">
		  <div id="success-show">
			Your message will be sent on the  scheduled date.
		  </div>
		</div>

		<div id="error" class="float-left">
		  <div id="error-show">
				Something went wrong, try refreshing and sending the mesage again.
		  </div>
		</div>'
		.$newMessageButton.
		
'</div>';


			$form_html .= '
			<div id = "new-message-dialog" title="New Message">
			<form id="newMessage" name="newMessage" method="post" action="../wp-content/plugins/customers/sendMessage.php">  
		  <fieldset>  
		  <div class="wrap_label_text">
			<label for="message" id="message">Message<span class="required">*</span></label>
			<textarea rows="3" cols="50" name="message" id="message" required> </textarea>
		  </div>
			<div class="wrap_label_text" style="float: left;  margin-right: 50px;">
				<label for="delivery_date" id="delivery_date"> Date <span class="required">*</span></label>
				<input type="text" id="datepicker" name="delivery_date" required />

			</div>
			<!--div class="wrap_label_text" >
				<label for="delivery_time" id="delivery_time"> Time <span class="required">*</span></label>
				<input type="text" id="timepicker" name="delivery_time" required />

			</div-->
			
		   <input type="hidden" name="action" value="sendMessage"/>
		   <input type="hidden" name="user_id" value="' . $userId . '"/>
		   <input type="hidden" name="customerOrderId" value="' . $customerOrderId . '"/>
			
			<div><input id="submit" type="submit" name="submit" value="Send" />  </div>
		  </fieldset>  
		</form> </div>';

		$form_html .= '
					<div id = "no-message-dialog" title="New Message">
					<p> You do not have enough message credits to send a message. </p>
					</p><a class="button" href="/slic/product-category/message-credits/"><i class="uk-icon-envelope"> </i> Buy Message Credits</a></p>
					</div>';

$form_html .= '

<table class="display-message">
		<tr>
			<th>Message</th>
			<th>Delivery Date</th> 
			<th>Delivery Status </th>
		</tr>

';
		$table_name = $wpdb->prefix . 'messages';
		$results = $wpdb->get_results( "SELECT * FROM {$table_name} where user_id = {$userId} ORDER BY message_id DESC"  );
		
		if(count($results) > 0){
					foreach ($results as $result) {
					//	echo $result->message_id.'<br/>';
									
			$form_html .= '
						<tr>
						<td >'.  $result->message_text . '</td>';
						
						$newDate = date("d - M - Y", strtotime($result->message_delivery_date));
			$form_html .=	'<td>'.  $newDate . ' </td>'; 

						if( $result->message_status == 0) $status = "<i class='uk-badge uk-badge-danger' >MESSAGE NOT DELIVERED"; else $status = "<i class='uk-badge uk-badge-success' >MESSAGE DELIVERED";
			$form_html .=	'<td> '.  $status . '</i> </td>
					</tr>
					';
			}
	}else{
		$form_html .='<tr><td colspan ="3"> There are no messages.</td></tr>';
	}
	
	
$form_html .= '</table> </div>';

return $form_html;
}else{
	 $url = get_site_url();
	header("Location: {$url}/my-account");
	die();
}
}

//create shortcode
add_shortcode('new_message','newMessage');

if(!is_admin()){
  wp_enqueue_script('google-hosted-jquery', '//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js');


//including jQuery libraries
function add_libraries() {
 
    // Registering Scripts

     wp_register_script('jquery-validation-plugin', 'http://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js', array('google-hosted-jquery'));
     
	 wp_register_script('javascript-ajax-submit', 'http://malsup.github.com/jquery.form.js"', false);
	 
	 wp_register_script('javascript-jquery-ui', '//code.jquery.com/ui/1.11.4/jquery-ui.js"', false);
	 wp_register_style('style-jquery-ui', '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css"', false);
	 
	 wp_register_script('timepicker_script', plugins_url('/jquery-ui-timepicker-addon.js', __FILE__));
	 wp_register_script('slider_script', plugins_url('/jquery-ui-sliderAccess.js', __FILE__));
	 wp_register_style('timepicker_style', plugins_url('/jquery-ui-timepicker-addon.css', __FILE__));
    // Enqueueing Scripts to the head section
    
    wp_enqueue_script('jquery-validation-plugin');
	wp_enqueue_script('javascript-ajax-submit');
    
	wp_enqueue_script('javascript-jquery-ui');
	wp_enqueue_style('style-jquery-ui');
	
	wp_enqueue_script('timepicker_script');
	wp_enqueue_script('slider_script');
	wp_enqueue_style('timepicker_style');

}
 
// Wordpress action that says, hey wait! lets add the scripts mentioned in the function as well.
add_action( 'init', 'add_libraries' );







//register jQuery and css file

function register_script(){
	wp_register_style('form_style', plugins_url('/style.css', __FILE__));
	wp_enqueue_style('form_style');
	wp_register_script('form_script', plugins_url('/jQuery.js', __FILE__));
	wp_enqueue_script('form_script');

}

add_action('init','register_script');
}