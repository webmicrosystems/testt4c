<?php
function get_email( $post_id )
{
	if( empty($post_id) )
	{
		return get_option( 'admin_email' );
	}
	$get_user_email = get_user_data_using_post_id( $post_id );
	if( !empty($get_user_email) )
	{
		$multiple_recipients = array( get_option( 'admin_email' ) , $get_user_email );
		return $multiple_recipients;
	}
	return get_option( 'admin_email' );
}
/*echo '<pre>';
print_r(get_email(183));die;
get_user_data_using_post_id( 183 );*/
function get_user_data_using_post_id( $post_id )
{
	if( empty($post_id) )
	{
		return;
	}
	global $wpdb;
	$custom_query = $wpdb->get_results("SELECT post_author FROM $wpdb->posts WHERE ID = $post_id" , OBJECT );	
	if( isset($custom_query[0]) && !empty($custom_query[0]->post_author) )
	{
		$user_info = get_userdata( $custom_query[0]->post_author );
		if( isset($user_info) && !empty($user_info->user_email) )
		{
			return $user_info->user_email;
		}
	}
	return;
}
function t4c_update_post_status( $post_id , $status = '' ) {
	if ( empty( $post_id ) || empty($status) ) {
		return false;
	}
	$my_post = array(
      'ID'           => $post_id,
      'post_status'   => $status
  );
  if ( get_post_status ( $post_id ) != $status )
  {
	  $post_id = wp_update_post( $my_post );
	  if ( is_wp_error($post_id) )
	  {
		  return false;
	  }else
	  {
		  return true;
	  }
  }else
  {
	  return false;
  }
}
function get_all_custom_detail_of_post( $post_id = '' , $mailer = '' )
{
	if( empty($post_id) || empty($mailer) )
	return;
	$get_post_meta = get_post_meta($post_id);
	if( empty($get_post_meta) )
	return;
	$amount = cpt_money_format($get_post_meta['fundraising_goal'][0]);
	$find = array("~~FIRSTNAME~~", "~~LASTNAME~~", "~~AMOUNT~~" , "~~PAGELINK~~");
	$replace   = array($get_post_meta['name'][0], $get_post_meta['last_name'][0], $amount ,get_the_permalink($post_id));
	$mailer = str_replace( $find , $replace , $mailer );
	return $mailer;
}
function cpt_money_format( $number = '' )
{
	if( empty($number) )
	return;
	setlocale(LC_MONETARY,"en_US");
    $number = money_format("%(#10n", $number);
	return $number;
}
function t4c_email_for_approve_to_admin( $post_id, $headers ) {
	if( empty($post_id) )
	return 'Post id can not be null.';
	$mailer     = file_get_contents(get_template_directory_uri().'/assets/template/approve_template.html');//get_option( 'options_final_acceptance_email' );
	//$contributor_email	
	$send_email = get_email( $post_id );
	$get_updated_status = t4c_update_post_status( $post_id , 'publish' );	
	if ( $get_updated_status === true ) {
		$mailer = get_all_custom_detail_of_post( $post_id ,$mailer  );
		$mailR = wp_mail( $send_email, "t4c Application", $mailer, $headers );
		if( $mailR )		
		$error_message = "Post status has been changed.";
		else
		$error_message = "Unable to send mail however status is updated.";

	} else {
		$error_message = "This post is already approved.";
	}

	return $error_message;
}

function t4c_email_for_unapprove_to_admin( $post_id , $headers ) {
	$mailer        = file_get_contents(get_template_directory_uri().'/assets/template/disapprove_template.html');//get_option( 'options_denial_email' );
	//$contributor_email
	$send_email = get_email( $post_id );
	$get_updated_status = t4c_update_post_status( $post_id , 'pending' );	
	if ( $get_updated_status === true ) {
		$mailer = get_all_custom_detail_of_post( $post_id ,$mailer  );
		$mailR = wp_mail( $send_email, "t4c Application", $mailer, $headers );
		if( $mailR )		
		$error_message = "Post status has been changed.";
		else
		$error_message = "Unable to send mail however status is updated.";

	} else {
		$error_message = "This post is already unapproved.";
	}

	return $error_message;
}

function t4c_process_admin_grid_action( $admin_grid_action_data ) {

	$error_message = '';
	$post_ids   = $admin_grid_action_data['checkbox_id_action'];

	$admin_grid_select_action = $admin_grid_action_data['admin_grid_select_action'];
	$admin_email              = get_option( 'admin_email' );
	$headers                  = 'From: T4C <' . $admin_email . '>' . "\r\n";
	$headers                  .= 'Reply-To: T4C <' . $admin_email . '>' . "\r\n";
	$headers                  .= 'MIME-Version: 1.0' . "\r\n";
	$headers                  .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers                  .= 'X-Mailer: PHP/' . phpversion();

	if ( $admin_grid_select_action == 'approve' ) {
		foreach ( $post_ids as $post_id ) {
			$error_message = t4c_email_for_approve_to_admin( $post_id, $headers );
		}
	}

	if ( $admin_grid_select_action == 'unapprove' ) {
		//do action
		foreach ( $post_ids as $post_id ) {
			$error_message = t4c_email_for_unapprove_to_admin( $post_id, $headers );
		}
	}

	return $error_message;
}
add_action( 'wp_enqueue_scripts', 't4c_custom_scripts' );
function t4c_custom_scripts()
{
	wp_enqueue_script( 't4c-colorpicker', get_theme_file_uri( '/assets/js/jscolor.min.js' ), array( 'jquery' ), '1.0', true );
}
add_action('wp_footer', 't4c_add_this_script_footer');
function t4c_add_this_script_footer(){ ?>

<script type="text/javascript">
jQuery(document).ready(function() {

	jQuery("li.jscolor input").addClass("jscolor {required:false}");

});
</script>

<?php } 
$result = add_role( 'org_manager', __(
'Org Manager' ),
array(

'read' => true, // true allows this capability
'edit_posts' => true, // Allows user to edit their own posts
'edit_pages' => false, // Allows user to edit pages
'edit_others_posts' => false, // Allows user to edit others posts not just their own
'create_posts' => true, // Allows user to create new posts
'manage_categories' => true, // Allows user to manage post categories
'publish_posts' => true, // Allows the user to publish, otherwise posts stays in draft mode
'edit_themes' => false, // false denies this capability. User can’t edit your theme
'install_plugins' => false, // User cant add new plugins
'update_plugin' => false, // User can’t update any plugins
'update_core' => false // user cant perform core updates

)

);
add_action( 'wp_ajax_nopriv_fetch_page_permalink', 'fetch_page_permalink' );
add_action( 'wp_ajax_fetch_page_permalink', 'fetch_page_permalink' );
function fetch_page_permalink()
{
	global $wpdb;
	$response_array = array();
	$page_name = $_POST['pagename'];
	$only_display = $_POST['only_display'];
	if( isset($only_display) && $only_display == 1 )
	{
		$page_name = get_parse_url($page_name);
		$response_array['page_name'] = site_url('/'.$page_name);//$page_name;
		$response_array['only_display'] = $only_display;
		echo json_encode($response_array);die;
	}
	$check_for_exist = get_link_by_slug( $page_name , 'organization' );
	if( empty($check_for_exist) )
	{
		$response_array['page_name'] = 'This link is available';
		$response_array['only_display'] = $only_display;
		echo json_encode($response_array);die;	
	}else
	{
		$response_array['page_name'] = 'The URL entered is already in use please choose another URL and click submit.';
		$response_array['only_display'] = $only_display;
		echo json_encode($response_array);die;
	}	
}
function get_link_by_slug( $slug , $type = 'post' )
{
  //$type = array('post','organization','page','teams','individual_contribut','individual_fundra');
  $url_parse = get_parse_url($slug);
  $type = get_post_types();
  $post = get_page_by_path($url_parse, OBJECT , $type);
  //echo '<pre>';
  //print_r($post);die;
  if( !empty($post) )
  {
	  return get_permalink($post->ID);
  }else
  {
	  return '';
  }  
}
function get_parse_url( $url = '' )
{
	if( empty($url) )
	return '';
	$url_parse = wp_parse_url($url);
    $url_parse = sanitize_title($url_parse['path']);
	return $url_parse;
	
}
add_action( 'wp_footer', 'cpt_inject_script_function', 100 );
function cpt_inject_script_function()
{ ?>
	<script>
jQuery(document).ready(function(){
    jQuery("input#input_3_13").blur(function(){
		var get_page_name = jQuery("input#input_3_13").val();
		if( get_page_name != '' )
		{
			cpt_ajax_call( get_page_name, 1 );
		}		
		});
	jQuery("input#input_3_16").blur(function(){
		var get_page_name = jQuery("input#input_3_16").val();
		if( get_page_name != '' )
		{
			cpt_ajax_call( get_page_name, 1 );
		}		
		});
	jQuery(".check_permalink").click(function(){		
		var get_page_name = jQuery("input#input_3_16").val();
		if( get_page_name != '' )
		{
			cpt_ajax_call( get_page_name, 0 );
		}		
		});
});
function cpt_ajax_call( get_page_name , display )
{
	var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
	jQuery.ajax({
				method: 'POST',
				url: ajaxurl,
				data: {
					action : 'fetch_page_permalink',
					pagename: get_page_name,
					only_display: display
				},
				success: function(data, status) {
					var data_array = jQuery.parseJSON(data);
					if( data_array['only_display'] == 1 )
					{
						jQuery("input#input_3_16").val(data_array['page_name'])
					}else
					{
						alert(data_array['page_name']);
					}					
				},
				error: function(data, status, err) {
					alert(status);
				}
			
			});
}
</script>
<?php 
}
add_action( 'template_redirect', 'cpt_redirect_to_specific_page' );
function cpt_redirect_to_specific_page()
{
	if ( is_page(114) && !current_user_can('administrator') )
	{
		wp_redirect( '/wp-login.php', 301 );exit;
	}
}
function na_remove_slug( $post_link, $post, $leavename ) {

    if ( 'events' != $post->post_type || 'publish' != $post->post_status ) {
       // return $post_link;
    }

    $post_link = str_replace( '/' . $post->post_type . '/', '/', $post_link );

    return $post_link;
}
add_filter( 'post_type_link', 'na_remove_slug', 10, 3 );
function na_parse_request( $query ) {

    if ( ! $query->is_main_query() || 2 != count( $query->query ) || ! isset( $query->query['page'] ) ) {
        return;
    }
    $type = get_post_types();
    if ( ! empty( $query->query['name'] ) ) {
        $query->set( 'post_type', array( 'post', 'organization', 'page' ) );
    }
}
add_action( 'pre_get_posts', 'na_parse_request' );