<?php 
/* Ajax call to insert message into the databse and update the table*/
$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' );



if($_POST["action"] == 'sendMessage'){
	$message = esc_textarea( $_POST["message"] );
	$delivery_date    = sanitize_text_field( $_POST["delivery_date"] );
	$delivery_date=date("Y-m-d h:i:s",strtotime($delivery_date));
	$user_id = $_POST["user_id"];
	
	//echo ' email =  '. $email. ' phone =  '. $phone. ' message =  '. $message;
	 
	
	//Insert message into database and update message credits
	function insertMessage( $message,  $delivery_date, $user_id,  $customerOrderId) {
	  global $wpdb;
	//echo 'message =  '. $message.'date =  '. $delivery_date.'userId =  '. $user_id  ;

	  	global $customerOrderId;
	if (!isset($customerOrderId)) $customerOrderId = $_POST["customerOrderId"];

	  
	  $table_name = $wpdb->prefix . 'messages';
	  $wpdb->insert( $table_name, array(
		'message_text' => $message,
		'message_delivery_date' => $delivery_date,
		'user_id' => $user_id,
		'message_status' => '0'
	  ) );
	  
	 global $lastMessageId;
	 
	 
	 $lastMessageId = $wpdb->insert_id; 

	  
	   $table_name = $wpdb->prefix . 'customers';

	  //get used message credits from databse
	  $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $customerOrderId));
															
	// echo '<p>Used message credits: ' . $used_message_credits . '</p>';
	//echo 'RMC = '.$remaining_message_credits;
	//if ($remaining_message_credits > 0) 
	
	/******update message credit (check customer order id)*******/
	$current_date =  date('Y-m-d');
	$table_name = $wpdb->prefix . 'customers';
	$rows = $wpdb->get_results( "SELECT * FROM {$table_name} WHERE user_id = {$user_id} AND message_end_date >= {$current_date} 
								ORDER BY message_end_date ASC"  );	

	if(count($rows) > 0 ){
			foreach ($rows as $row) {
					$max_message_credits =  $row->max_message_credits ;
					$used_message_credits =  $row->used_message_credits ;
					$remaining_message_credits = $max_message_credits - $used_message_credits;
					if ($remaining_message_credits > 0) { 
						$customerOrderId = $row->id;
						break;
					}
			}
	}

	/******End(check customer order id)*******/

	$used_message_credits++;
	 
	 $wpdb->update( $table_name, array('used_message_credits' => $used_message_credits), array('id' => $customerOrderId) 
	 );
	 
	 //$Response = array('RMC' => $remaining_message_credits);
	 //echo json_encode($Response);	
	 
	 //echo $wpdb->last_query;
	}


//update table with new onsert message
	function getMessages( $user_id ) { 
		global $wpdb;
		$table_name = $wpdb->prefix . 'messages';
	  
	   global $lastMessageId; // echo 'message id = '.$lastMessageId;
	   global $customerOrderId;

	   $result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} where message_id = %d" ,$lastMessageId ) );
		//var_dump($result);
		$Success = true; 
		$newDate = date("d - M - Y", strtotime($result->message_delivery_date));
		
		
		
	/******new message (check available credits)*******/
	$current_date =  date('Y-m-d');
	$table_name = $wpdb->prefix . 'customers';
	//echo $current_date; echo date("Y - m - d", time());  
	$rows = $wpdb->get_results( "SELECT * FROM {$table_name} WHERE user_id = {$user_id} AND message_end_date >= {$current_date} 
								ORDER BY message_end_date ASC"  );	

	if(count($rows) > 0 ){
			foreach ($rows as $row) {
					$max_message_credits =  $row->max_message_credits ;
					$used_message_credits =  $row->used_message_credits ;
					$remaining_message_credits = $max_message_credits - $used_message_credits;
					if ($remaining_message_credits > 0) { 
						$credits_available = 1;
						$customerOrderId = $row->id;
						break;
					}
					else { 
						$credits_available = 0;
					}
			}
	}

		
		
		
		$Response = array('Success' => $Success, 'Message' => $result->message_text, 'Date' => $newDate, 'Status' => $result->message_status, 
						  'Credits' => $credits_available, 'customerOrderId' => $customerOrderId ); 
		echo json_encode($Response);	

		//echo $table_html;

		
	}
		function send_firebase_message($title, $body){
				//send message to firebase
			//echo "Start of function\r\n";
			$ch = curl_init("https://fcm.googleapis.com/fcm/send");
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

			//The device token.
			$token = "/topics/Bristol";
			//Title of the Notification.
			//$title = $result->message_text;

			//Body of the Notification.
			//$body = $result->message_text;

			//Creating the notification array.
			$notification = array('title' =>$title , 'body' => $body);

			//This array contains, the token and the notification. The 'to' attribute stores the token.
			$arrayToSend = array('to' => $token, 'notification' => $notification);

			//Generating JSON encoded string form the above array.
			$json = json_encode($arrayToSend);

			//Setup headers:
			$headers = array();
			$headers[] = 'Content-Type: application/json';
			$headers[] = 'Authorization: key= AIzaSyDmLFxj96HXYenSQM_FiIbbz9tDq7Uzp8k';

			//Setup curl, add headers and post parameters.
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
			curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
			curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);       

			//Send the request
			curl_exec($ch);
			if(curl_errno($ch)){
				//echo 'Curl Error'. curl_error($ch);
			}
			//echo "sent\r\n";
			//echo $ch;
			//Close request
			curl_close($ch);

			//End of send message to firebase
	}

	

//	send_firebase_message($message, $message);
	insertMessage( $message,  $delivery_date, $user_id, $customerOrderId);
	getMessages( $user_id);
	
	}


?>