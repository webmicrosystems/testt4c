<?php
if ( !class_exists('CptGravity') )
{
	class CptGravity
	{
		var $custom_post_id;
		var $user_created_id;
		public function __construct()
		{
			add_action( 'gform_after_submission_3', array( $this,'cpt_organization_after_submission'), 10, 2 );
			add_action( 'gform_after_submission_5', array( $this,'cpt_team_after_submission'), 10, 2 );
			add_filter( 'gform_validation_3', array( $this,'cpt_custom_validation' ));
			add_action( 'wp', array( $this,'cpt_load_gf_formdata' ));
		}
		public function cpt_custom_validation( $validation_result )
		{
			$form = $validation_result['form'];
			
			//supposing we don't want input 1 to be a value of 86
			$user_logn = sanitize_text_field(rgpost( 'input_4' ));
			//$get_link_value = sanitize_title(rgpost( 'input_16' ));
			$get_link_value = get_parse_url(rgpost( 'input_16' ));
			$fundraising_link = get_link_by_slug( $get_link_value , 'organization' );
			if( isset( $get_link_value ) && !empty( $get_link_value ) && !empty( $fundraising_link ) )
			{
				$validation_result['is_valid'] = false;
				foreach( $form['fields'] as &$field )
				{
					if ( $field->id == '16' )
					{
						$field->failed_validation = true;
						$field->validation_message = 'The URL entered is already in use please choose another URL and click submit.';
						break;
					}
				}
			}
			if ( username_exists( $user_logn ) || email_exists( $user_logn ) ) {		 
				// set the form validation to false
				$validation_result['is_valid'] = false;		 
				//finding Field with ID of 1 and marking it as failed validation
				foreach( $form['fields'] as &$field ) {		 
					//NOTE: replace 1 with the field you would like to validate
					if ( $field->id == '4' ) {
						$field->failed_validation = true;
						$field->validation_message = 'Email is already registered!';
						break;
					}
				}		 
			}
			//Assign modified $form object back to the validation result
			$validation_result['form'] = $form;
			return $validation_result;
		}
		public function cpt_team_after_submission( $entry, $form )
		{
			$acf_key_field = array( "2" => "short_name" , "3" => "searchable_name" , "4" => "primary_color" , "5" => "secondary_color" , "6" => "tertiary_color" , "7" => "conference" , "8" => "additional_information" , "9" => "last_years_touchdown_standing" , "10" => "amount_team_has_raised" , "11" => "teams_current_place_rank_out_of_total_number_teams" , "12" => "numbers_of_touchdowns_weekly_updated_value_show_by_week" , "13" => "number_of_contributors" );
			$my_post = array_filter(array(
				'post_title'    => $entry['1'],
				'post_type'     => 'team',
				'post_status'   => 'pending'
			));
			if( !empty($my_post) )
			{
				$post_id = wp_insert_post( $my_post );
			}
			if( !empty($post_id) )
			{
				foreach( $entry as $entry_key => $entry_val )
				{
					if( empty($entry_val) || empty($post_id) || !array_key_exists( $entry_key , $acf_key_field ) )
					continue;
					if( array_key_exists( $entry_key , $acf_key_field ) )
					{
						if( $entry_key == 4 || $entry_key == 5 || $entry_key == 6 )
						{
							$entry_val = '#'.$entry_val;
						}
						update_field($acf_key_field[$entry_key], $entry_val, $post_id);
					}
				}
			}
		}
		public function cpt_organization_after_submission( $entry, $form )
		{
			//echo '<pre>';
			//print_r($entry);die;
			$acf_key_field = array( "2" => "title" , "3.3" => "name" , "3.4" => "middle_name" , "3.6" => "last_name" , "4" => "username_email" , "5" => "phone_number" ,"6" => "would_you_like_to_add_another_user_to_this_account" , "7" => "title_another" , "8.3" => "first_name_another" , "8.4" => "middle_name_another" , "8.6" => "last_name_another" , "9" => "email_another" , "10" => "phone_another" , "17.1" => "address" , "15" => "team_name" , "16" => "fundraising_link" , "19" => "main_image" , "22" => "logo" , "25" => "about_information", "28" => "fundraising_goal", "29" => "touchdown_team_all_team_or_single_team" , "30.1" => "select_all_teams" , "35" => "password" );
			
			if( !empty($entry['4']) )
			{
				$userdata = array(
						'user_login'  =>  sanitize_text_field($entry['4']),
						'user_email'  =>  sanitize_text_field($entry['4']),
						'role'        =>  'org_manager',
						'user_pass'   =>  $entry['35']  // When creating an user, `user_pass` is expected.
					);
				$this->user_created_id = $this->cpt_create_user( $userdata , $entry );
			}
			$address_array = array();
			$post_id = $this->cpt_create_post( $entry );
			if( !empty($post_id) )
			{
				foreach( $entry as $entry_key => $entry_val )
				{
					if( empty($entry_val) || empty($post_id) || !array_key_exists( $entry_key , $acf_key_field ) )
					continue;
					
					if( array_key_exists( $entry_key , $acf_key_field ) )
					{
						if( $entry_key == 17.1 )
						{
							$address_array ['address1'] = $entry['17.1'];
							$address_array ['address2'] = $entry['17.2'];
							$address_array ['city'] = $entry['17.3'];
							$address_array ['state'] = $entry['17.4'];
							$address_array ['postal_code'] = $entry['17.5'];
							$entry_val = $address_array;
						}
						if( $entry_key == 19 || $entry_key == 22 )
						{
							$entry_val = $this->set_image_attachment( $entry_val , $post_id );
						}
						update_field($acf_key_field[$entry_key], $entry_val, $post_id);
					}
					
				}
			}				

		}
		public function cpt_create_post( $entry = '' )
		{
			if( empty($entry) )
			return;
			$url_parse = get_parse_url($entry['16']);
			$my_post = array_filter(array(
			    'post_title'    => $entry['13'],
				'post_name'    => $url_parse,
				'post_type'     => 'organization',
				'post_status'   => 'pending',				
				'post_author'   => $this->user_created_id
			));
			if( !empty($my_post) && !empty($this->user_created_id) )
			{
				$post_id = wp_insert_post( $my_post );
				if( !empty($post_id) )
				{
					update_user_meta( $this->user_created_id, 'user_created_post_id', $post_id );
					update_user_meta( $this->user_created_id, 'gform_entry_id', $entry['id'] );
				}	
				return $post_id;			
			}
			return;
		}
		public function cpt_create_user( $userdata = '' , $entry = '' )
		{
			if( empty($userdata) )
			{
				return;
			}
			
			$user_id = wp_insert_user( $userdata ) ;			
			//On success
			if ( !is_wp_error( $user_id ) ) {
				update_user_meta( $user_id, 'all_entry_field_of_entity_form', $entry );
				return $user_id;
			}else
			{
				return;
			}
		}
		public function set_image_attachment( $image_path = '' , $post_id = '' )
		{
			if( empty($image_path) || empty($post_id) )
			return '';
			$filename = $image_path;
			$filetype = wp_check_filetype( basename( $filename ), null );
			$wp_upload_dir = wp_upload_dir();
			$attachment = array(
								'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ), 
								'post_mime_type' => $filetype['type'],
								'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
								'post_content'   => '',
								'post_status'    => 'inherit'
							);
			$attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
			return $attach_id;
		}
		public function cpt_load_gf_formdata()
		{
			$post_id = '';
			if( isset($_GET['post_id']) && !empty($_GET['post_id']) )
			{
				$this->custom_post_id = $_GET['post_id'];
			}
			if( !empty($this->custom_post_id) )
			{
				//add_filter( 'gform_field_value', array( $this,'populate_fields'), 10, 3 );
			}
		}
		public function populate_fields( $value, $field, $name )
		{
			$values = array(
					'first_name'   => 'value one',
					'middle_name'   => 'value two',
					'last_name' => 'value three',
				);
			 
				return isset( $values[ $name ] ) ? $values[ $name ] : $value;
		}
	}
	$CptGravity = new CptGravity();
}