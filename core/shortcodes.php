<?php
/**
 * Awesome Forms Core Shortcodes
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package AwesomeForms/Core
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

class AF_ShortCodes
{
	var $tables;
	var $components = array();

	/**
	 * Loading all Shortcodes
	 */
	public static function init()
	{
		add_shortcode( 'survey', array( __CLASS__, 'form' ) ); // @todo: Delete later, because it's deprecated
		add_shortcode( 'form', array( __CLASS__, 'form' ) );

		add_action( 'af_formbuilder_options', array( __CLASS__, 'show_form_shortcode' ), 15 );
	}

	public static function form( $atts )
	{
		$defaults = array(
				'id' => '',
				'title' => __( 'Form', 'af-locale' ),
				'show' => 'embed', // embed, iframe
		        'iframe_width' => '100%',
		        'iframe_height' => '100%'
		);

		$atts = shortcode_atts( $defaults, $atts );

		$af_form_id = $atts[ 'id' ];

		if( '' == $af_form_id )
		{
			return esc_attr( 'Please enter an id in the form shortcode!', 'af-locale' );
		}

		if( !af_form_exists( $af_form_id ) )
		{
			return esc_attr( 'Form not found. Please enter another ID in your shortcode.', 'af-locale' );
		}

		switch(  $atts[ 'show' ] )
		{
			case 'iframe':
				$url = get_permalink( $af_form_id );
				$width = $atts[ 'iframe_width' ];
				$height = $atts[ 'iframe_height' ];

				$html = '<iframe src="' . $url . '" style="width:' . $width . ';height:' . $height . ';"></iframe>';
				break;
			default:
				$html = AF_FormLoader::get_form( $af_form_id );
				break;
		}

		return $html;
	}

	public static function show_form_shortcode()
	{
		global $post;

		if( !af_is_formbuilder() )
		{
			return;
		}

		$html  = '<div class="misc-pub-section form-options">';
		$html .= af_clipboard_field( __( 'Form Shortcode:', 'af-locale' ), '[form id=' . $post->ID . ']' );
		$html .= '</div>';

		echo $html;
	}
}

AF_ShortCodes::init();