<?php
/**
 * Restrict form to all selected members
 *
 * Motherclass for all Restrictions
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package AwesomeForms/Restrictions
 * @version 1.0.0
 * @since   1.0.0
 * @license GPL 2
 *
 * Copyright 2015 awesome.ug (support@awesome.ug)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if( !defined( 'ABSPATH' ) )
{
	exit;
}

class AF_Restriction_SelectedMembers extends AF_Restriction
{

	/**
	 * Constructor
	 */
	public function init()
	{
		$this->title = __( 'Selected Members', 'af-locale' );
		$this->name = 'selectedmembers';

		$this->option_name = __( 'Selected Members of site', 'af-locale' );

		// add_action( 'form_functions', array( $this, 'invite_buttons' ) );

		add_action( 'af_formbuilder_save', array( $this, 'save' ), 10, 1 );

		add_action( 'wp_ajax_form_add_participiants_allmembers', array( $this, 'ajax_add_participiants_allmembers' ) );
		add_action( 'wp_ajax_form_invite_participiants', array( $this, 'ajax_invite_participiants' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 15 );
		add_action( 'admin_print_styles', array( $this, 'register_admin_styles' ) );

		$this->settings_fields = array(
			'invitations'        => array(
				'title'       => esc_attr( 'Invitation Mail Template', 'af-locale' ),
				'description' => esc_attr( 'Setup Mail Templates for the Invitation Mail for selected Members.', 'af-locale' ),
				'type'        => 'title'
			),
			'invite_from_name'   => array(
				'title'       => esc_attr( 'From Name', 'af-locale' ),
				'description' => esc_attr( 'The Mail Sender Name.', 'af-locale' ),
				'type'        => 'text',
				'default'     => get_bloginfo( 'name' )
			),
			'invite_from'        => array(
				'title'       => esc_attr( 'From Email', 'af-locale' ),
				'description' => esc_attr( 'The Mail Sender Email.', 'af-locale' ),
				'type'        => 'text',
				'default'     => get_option( 'admin_email' )
			),
			'invite_text'        => array(
				'title'       => esc_attr( 'Email Text', 'af-locale' ),
				'description' => esc_attr( 'The Text of the Mail.', 'af-locale' ),
				'type'        => 'textarea'
			),
			'reinvitations'      => array(
				'title'       => esc_attr( 'Reinvitation Mail Template', 'af-locale' ),
				'description' => esc_attr( 'Setup Mail Templates for the Reinvitation Mail for selected Members.', 'af-locale' ),
				'type'        => 'title'
			),
			'reinvite_from_name' => array(
				'title'       => esc_attr( 'From Name', 'af-locale' ),
				'description' => esc_attr( 'The Mail Sender Name.', 'af-locale' ),
				'type'        => 'text',
				'default'     => get_bloginfo( 'name' )
			),
			'reinvite_from'      => array(
				'title'       => esc_attr( 'From Email', 'af-locale' ),
				'description' => esc_attr( 'The Mail Sender Email.', 'af-locale' ),
				'type'        => 'text',
				'default'     => get_option( 'admin_email' )
			),
			'reinvite_text'      => array(
				'title'       => esc_attr( 'Email Text', 'af-locale' ),
				'description' => esc_attr( 'The Text of the Mail.', 'af-locale' ),
				'type'        => 'textarea'
			)
		);
	}

	/**
	 * Invitations box
	 *
	 * @since 1.0.0
	 */
	public static function invite_buttons()
	{
		global $post;

		$af_invitation_text_template = af_get_mail_template_text( 'invitation' );
		$af_reinvitation_text_template = af_get_mail_template_text( 'reinvitation' );

		$af_invitation_subject_template = af_get_mail_template_subject( 'invitation' );
		$af_reinvitation_subject_template = af_get_mail_template_subject( 'reinvitation' );

		$html = '';

		if( 'publish' == $post->post_status ):
			$html .= '<div class="form-function-element">';
			$html .= '<input id="form-invite-subject" type="text" name="form_invite_subject" value="' . $af_invitation_subject_template . '" />';
			$html .= '<textarea id="form-invite-text" name="form_invite_text">' . $af_invitation_text_template . '</textarea>';
			$html .= '<input id="form-invite-button" type="button" class="button" value="' . esc_attr__( 'Invite Participiants', 'af-locale' ) . '" /> ';
			$html .= '<input id="form-invite-button-cancel" type="button" class="button" value="' . esc_attr__( 'Cancel', 'af-locale' ) . '" />';
			$html .= '</div>';

			$html .= '<div class="form-function-element">';
			$html .= '<input id="form-reinvite-subject" type="text" name="form_invite_subject" value="' . $af_reinvitation_subject_template . '" />';
			$html .= '<textarea id="form-reinvite-text" name="form_reinvite_text">' . $af_reinvitation_text_template . '</textarea>';
			$html .= '<input id="form-reinvite-button" type="button" class="button" value="' . esc_attr__( 'Reinvite Participiants', 'af-locale' ) . '" /> ';
			$html .= '<input id="form-reinvite-button-cancel" type="button" class="button" value="' . esc_attr__( 'Cancel', 'af-locale' ) . '" />';

			$html .= '</div>';
		else:
			$html .= '<p>' . esc_attr__( 'You can invite Participiants to this form after it is published.', 'af-locale' ) . '</p>';
		endif;

		echo $html;
	}

	/**
	 * Saving data
	 *
	 * @param int $form_id
	 *
	 * @since 1.0.0
	 */
	public static function save( $form_id )
	{
		global $wpdb, $af_global;

		/**
		 * Saving restriction options
		 */
		if( array_key_exists( 'form_restrictions_selectedmembers_same_users', $_POST ) )
		{
			$restrictions_same_users = $_POST[ 'form_restrictions_selectedmembers_same_users' ];
			update_post_meta( $form_id, 'form_restrictions_selectedmembers_same_users', $restrictions_same_users );
		}
		else
		{
			update_post_meta( $form_id, 'form_restrictions_selectedmembers_same_users', '' );
		}

		/**
		 * Saving restriction options
		 */
		$add_participiants_option = $_POST[ 'form_add_participiants_option' ];
		update_post_meta( $form_id, 'add_participiants_option', $add_participiants_option );

		/**
		 * Saving participiants
		 */
		$form_participiants = $_POST[ 'form_participiants' ];
		$af_participiant_ids = explode( ',', $form_participiants );

		$sql = "DELETE FROM {$af_global->tables->participiants} WHERE form_id = %d";
		$sql = $wpdb->prepare( $sql, $form_id );
		$wpdb->query( $sql );

		if( is_array( $af_participiant_ids ) && count( $af_participiant_ids ) > 0 ):
			foreach( $af_participiant_ids AS $user_id ):
				$wpdb->insert( $af_global->tables->participiants, array(
					'form_id' => $form_id,
					'user_id'   => $user_id
				) );
			endforeach;
		endif;
	}

	/**
	 * Adding user by AJAX
	 *
	 * @since 1.0.0
	 */
	public static function ajax_add_participiants_allmembers()
	{
		$users = get_users( array( 'orderby' => 'ID' ) );

		$return_array = array();

		foreach( $users AS $user ):
			$return_array[] = array(
				'id'            => $user->ID,
				'user_nicename' => $user->user_nicename,
				'display_name'  => $user->display_name,
				'user_email'    => $user->user_email,
			);
		endforeach;

		echo json_encode( $return_array );

		die();
	}

	/**
	 * Invite participiants AJAX
	 *
	 * @since 1.0.0
	 */
	public static function ajax_invite_participiants()
	{
		global $wpdb, $af_global;

		$return_array = array( 'sent' => FALSE );

		$form_id = $_POST[ 'form_id' ];
		$subject_template = $_POST[ 'subject_template' ];
		$text_template = $_POST[ 'text_template' ];

		$sql = "SELECT user_id FROM {$af_global->tables->participiants} WHERE form_id = %d";
		$sql = $wpdb->prepare( $sql, $form_id );
		$user_ids = $wpdb->get_col( $sql );

		if( 'reinvite' == $_POST[ 'invitation_type' ] ):
			$user_ids_new = '';
			if( is_array( $user_ids ) && count( $user_ids ) > 0 ):
				foreach( $user_ids AS $user_id ):
					if( !af_user_has_participated( $form_id, $user_id ) ):
						$user_ids_new[] = $user_id;
					endif;
				endforeach;
			endif;
			$user_ids = $user_ids_new;
		endif;

		$post = get_post( $form_id );

		if( is_array( $user_ids ) && count( $user_ids ) > 0 ):
			$users = get_users( array(
				                    'include' => $user_ids,
				                    'orderby' => 'ID',
			                    ) );

			$content = str_replace( '%site_name%', get_bloginfo( 'name' ), $text_template );
			$content = str_replace( '%survey_title%', $post->post_title, $content );
			$content = str_replace( '%survey_url%', get_permalink( $post->ID ), $content );

			$subject = str_replace( '%site_name%', get_bloginfo( 'name' ), $subject_template );
			$subject = str_replace( '%survey_title%', $post->post_title, $subject );
			$subject = str_replace( '%survey_url%', get_permalink( $post->ID ), $subject );

			foreach( $users AS $user ):
				if( '' != $user->data->display_name )
				{
					$display_name = $user->data->display_name;
				}
				else
				{
					$display_name = $user->data->user_nicename;
				}

				$user_nicename = $user->data->user_nicename;
				$user_email = $user->data->user_email;

				$subject_user = str_replace( '%displayname%', $display_name, $subject );
				$subject_user = str_replace( '%username%', $user_nicename, $subject_user );

				$content_user = str_replace( '%displayname%', $display_name, $content );
				$content_user = str_replace( '%username%', $user_nicename, $content_user );

				af_mail( $user_email, $subject_user, stripslashes( $content_user ) );
			endforeach;

			$return_array = array( 'sent' => TRUE );
		endif;

		echo json_encode( $return_array );

		die();
	}

	/**
	 * Registers and enqueues admin-specific styles.
	 *
	 * @since 1.0.0
	 */
	public static function register_admin_styles()
	{
		wp_enqueue_style( 'af-selected-member-styles', AF_URLPATH . 'components/restrictions/base-restrictions/includes/css/selected-members.css' );
	}

	/**
	 * Adds content to the option
	 */
	public function option_content()
	{
		global $wpdb, $post, $af_global;

		$form_id = $post->ID;

		$html = '<div id="form-selectedmembers-userfilter">';
		$html .= '<h3>' . esc_attr( 'Restrict Members', 'af-locale' ) . '</h3>';

		/**
		 * Check User
		 */
		$restrictions_same_users = get_post_meta( $form_id, 'form_restrictions_selectedmembers_same_users', TRUE );
		$checked = 'yes' == $restrictions_same_users ? ' checked' : '';

		$html .= '<div class="form-restrictions-same-users-userfilter">';
		$html .= '<input type="checkbox" name="form_restrictions_selectedmembers_same_users" value="yes" ' . $checked . '/>';
		$html .= '<label for="form_restrictions_selectedmembers_same_users">' . esc_attr( 'Prevent multiple entries from same User', 'af-locale' ) . '</label>';
		$html .= '</div>';
		$html .= '</div>';

		/**
		 * Add participiants functions
		 */
		$html .= '<div id="form-add-participiants">';

		$options = apply_filters( 'form_add_participiants_options', array( 'allmembers' => esc_attr__( 'Add all actual Members', 'af-locale' ), ) );

		$add_participiants_option = get_post_meta( $form_id, 'add_participiants_option', TRUE );

		$html .= '<div id="af-add-participiants-options">';
		$html .= '<label for"form_add_participiants_option">' . esc_attr( 'Add Members', 'af-locale' ) . '';
		$html .= '<select id="form-add-participiants-option" name="form_add_participiants_option">';
		foreach( $options AS $name => $value ):
			$selected = '';
			if( $name == $add_participiants_option )
			{
				$selected = ' selected="selected"';
			}
			$html .= '<option value="' . $name . '"' . $selected . '>' . $value . '</option>';
		endforeach;
		$html .= '</select>';
		$html .= '</label>';
		$html .= '</div>';

		$html .= '<div id="form-add-participiants-content-allmembers" class="form-add-participiants-content-allmembers form-add-participiants-content">';
		$html .= '<input type="button" class="form-add-participiants-allmembers-button button" id="form-add-participiants-allmembers-button" value="' . esc_attr__( 'Add all members as Participiants', 'af-locale' ) . '" />';
		$html .= '<a class="form-remove-all-participiants">' . esc_attr__( 'Remove all Participiants', 'af-locale' ) . '</a>';
		$html .= '</div>';

		// Hooking in
		ob_start();
		do_action( 'form_add_participiants_content' );
		$html .= ob_get_clean();

		$html .= '</div>';

		/**
		 * Getting all users which have been added to participiants list
		 */
		$sql = $wpdb->prepare( "SELECT user_id FROM {$af_global->tables->participiants} WHERE form_id = %s", $form_id );
		$user_ids = $wpdb->get_col( $sql );

		$users = array();

		if( is_array( $user_ids ) && count( $user_ids ) > 0 )
		{
			$users = get_users( array(
				                    'include' => $user_ids,
				                    'orderby' => 'ID'
			                    ) );
		}
		/**
		 * Participiants Statistics
		 */
		$html .= '<div id="form-participiants-status" class="form-participiants-status">';
		$html .= '<p>' . count( $users ) . ' ' . esc_attr__( 'participiant/s', 'af-locale' ) . '</p>';
		$html .= '</div>';

		/**
		 * Participiants list
		 */

		// Head
		$html .= '<div id="form-participiants-list">';
		$html .= '<table class="wp-list-table widefat">';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th>' . esc_attr__( 'ID', 'af-locale' ) . '</th>';
		$html .= '<th>' . esc_attr__( 'User nicename', 'af-locale' ) . '</th>';
		$html .= '<th>' . esc_attr__( 'Display name', 'af-locale' ) . '</th>';
		$html .= '<th>' . esc_attr__( 'Email', 'af-locale' ) . '</th>';
		$html .= '<th>' . esc_attr__( 'Status', 'af-locale' ) . '</th>';
		$html .= '<th>&nbsp</th>';
		$html .= '</tr>';
		$html .= '</thead>';

		$html .= '<tbody>';

		$form_participiants_value = '';

		if( is_array( $users ) && count( $users ) > 0 )
		{

			// Content
			foreach( $users AS $user ):
				if( af_user_has_participated( $form_id, $user->ID ) ):
					$user_css = ' finished';
					$user_text = esc_attr__( 'finished', 'af-locale' );
				else:
					$user_text = esc_attr__( 'new', 'af-locale' );
					$user_css = ' new';
				endif;

				$html .= '<tr class="participiant participiant-user-' . $user->ID . $user_css . '">';
				$html .= '<td>' . $user->ID . '</td>';
				$html .= '<td>' . $user->user_nicename . '</td>';
				$html .= '<td>' . $user->display_name . '</td>';
				$html .= '<td>' . $user->user_email . '</td>';
				$html .= '<td>' . $user_text . '</td>';
				$html .= '<td><a class="button form-delete-participiant" rel="' . $user->ID . '">' . esc_attr__( 'Delete', 'af-locale' ) . '</a></td>';
				$html .= '</tr>';
			endforeach;

			$form_participiants_value = implode( ',', $user_ids );
		}
		$html .= '<tr class="no-users-found">';
		$html .= '<td colspan="6">' . esc_attr( 'No Users found.', 'af-locale' ) . '</td>';
		$html .= '</tr>';

		$html .= '</tbody>';

		$html .= '</table>';

		$html .= '<input type="hidden" id="form-participiants" name="form_participiants" value="' . $form_participiants_value . '" />';
		$html .= '<input type="hidden" id="form-participiants-count" name="form-participiants-count" value="' . count( $users ) . '" />';

		$html .= '</div>';

		return $html;
	}

	/**
	 * Checks if the user can pass
	 */
	public function check()
	{
		global $ar_form_id;

		if( !is_user_logged_in() )
		{
			$this->add_message( 'error', esc_attr( 'You have to be logged in to participate.', 'af-locale' ) );

			return FALSE;
		}

		if( !$this->is_participiant() )
		{
			$this->add_message( 'error', esc_attr( 'You can\'t participate.', 'af-locale' ) );

			return FALSE;
		}

		$restrictions_same_users = get_post_meta( $ar_form_id, 'form_restrictions_selectedmembers_same_users', TRUE );

		if( 'yes' == $restrictions_same_users && af_user_has_participated( $ar_form_id ) )
		{
			$this->add_message( 'error', esc_attr( 'You have already entered your data.', 'af-locale' ) );

			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Checks if a user can participate
	 *
	 * @param int $form_id
	 * @param int $user_id
	 *
	 * @return boolean $can_participate
	 * @since 1.0.0
	 */
	public function is_participiant( $user_id = NULL )
	{
		global $wpdb, $current_user, $af_global, $ar_form_id;

		$is_participiant = FALSE;

		// Setting up user ID
		if( NULL == $user_id ):
			get_currentuserinfo();
			$user_id = $user_id = $current_user->ID;
		endif;

		$sql = $wpdb->prepare( "SELECT user_id FROM {$af_global->tables->participiants} WHERE form_id = %d", $ar_form_id );
		$user_ids = $wpdb->get_col( $sql );

		if( !in_array( $user_id, $user_ids ) )
		{
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Enqueue Scripts
	 */
	public function enqueue_scripts()
	{
		$translation = array(
			'delete'                              => esc_attr__( 'Delete', 'af-locale' ),
			'yes'                                 => esc_attr__( 'Yes', 'af-locale' ),
			'no'                                  => esc_attr__( 'No', 'af-locale' ),
			'just_added'                          => esc_attr__( 'just added', 'af-locale' ),
			'invitations_sent_successfully'       => esc_attr__( 'Invitations sent successfully!', 'af-locale' ),
			'invitations_not_sent_successfully'   => esc_attr__( 'Invitations could not be sent!', 'af-locale' ),
			'reinvitations_sent_succes  sfully'   => esc_attr__( 'Renvitations sent successfully!', 'af-locale' ),
			'reinvitations_not_sent_successfully' => esc_attr__( 'Renvitations could not be sent!', 'af-locale' ),
			'added_participiants'                 => esc_attr__( 'participiant/s', 'af-locale' ),
		);

		wp_enqueue_script( 'af-selected-members', AF_URLPATH . 'components/restrictions/base-restrictions/includes/js/selected-members.js' );
		wp_localize_script( 'af-selected-members', 'translation_sm', $translation );
	}
}

af_register_restriction( 'AF_Restriction_SelectedMembers' );