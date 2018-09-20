<?php

namespace Hybrid\Svg;

use Hybrid\App;
use Hybrid\Contracts\Attr\Attributes;
use Hybrid\Contracts\Renderable;
use Hybrid\Contracts\Displayable;


class SvgMaker implements Renderable, Displayable {

	/**
	 * The name of the SVG object.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string
	 */
	protected $name = '';

	/**
	 * The SVG file that we're getting. Use a relative path to the theme
	 * folder where the file is.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string
	 */
	protected $file = '';

	/**
	 * The class of the SVG object.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string
	 */
	protected $class = '';

	/**
	 * Used to add or replace an existing `<title>` element in the SVG.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string
	 */
	protected $title = '';

	/**
	 * Used to add or replace an existing `<desc>` element in the SVG.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string
	 */
	protected $desc = '';

	/**
	 * Path info about the file.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    array
	 */
	protected $pathinfo = [];

	/**
	 * Sets up the object properties.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  string  $file  The SVG file name.
	 * @param  array   $args  An array of arguements to apply to the SVG.
	 * @return void
	 */
	public function __construct( $file, array $args = [] ) {

		foreach ( array_keys( get_object_vars( $this ) ) as $key ) {

			if ( isset( $args[ $key ] ) ) {
				$this->$key = $args[ $key ];
			}
		}

		// Define the file property.
		$this->file = $file;

		// Get the file path info.
		$this->pathinfo = pathinfo( $this->file );

		// If the file has no extension, add a `.svg`.
		if ( ! isset( $this->pathinfo['extension'] ) ) {
			$this->file = "{$this->file}.svg";
		}

		// Get a name for use in hooks and such.
		$this->name = isset( $this->pathinfo['filename'] )
			? $this->pathinfo['filename']
			: basename( $this->file );
	}

	protected function getSvgContent() {

		$path = trim( apply_filters( 'hybrid/svg/path', '' ), '/' );

		$file = $path ? "{$path}/{$this->file}" : $this->file;

		$collection = App::resolve( 'hybrid/svg/collection' );

		$svg = $collection ? $collection->get( $file ) : '';

		if ( ! $svg ) {

			$svg = file_get_contents( get_theme_file_path( $file ) );

			if ( $svg && $collection ) {
				$collection->add( $file, $svg );
			}
		}

		return $svg ?: '';
	}

	/**
	 * Returns the SVG output.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return string
	 */
	public function render() {

		$svg = $this->getSvgContent();

		if ( ! $svg ) {
			return '';
		}

		// Get the attributes and inner HTML.
		preg_match( '/<svg(.*?)>(.*?)<\/svg>/is', $svg, $matches );

		if ( ! empty( $matches ) && isset( $matches[1] ) && isset( $matches[2] ) ) {

			$inner_html = $matches[2];

			// Create an array of existing attributes.
			$atts = wp_kses_hair( $matches[1], [ 'http', 'https' ] );

			// Sets up our attributes array.
			$attr = array_combine(
				array_column( $atts, 'name' ),
				array_column( $atts, 'value' )
			);

			// This doesn't actually help us in any way because we're
			// not building the `<title>` and `<desc>` elements.
			if ( $this->title ) {

				$unique_id = esc_attr( uniqid() );

				$attr['aria-labelledby'] = sprintf(
					$this->desc ? 'svg-title-%1$s svg-desc-%1$s' : 'svg-title-%s', $unique_id
				);

				$patterns = [
					'/<title.*?<\/title>/is',
					'/<desc.*?<\/desc>/is',
				];

				$inner_html = preg_replace( $patterns, '', $inner_html );

				$title_desc = sprintf(
					'<title id="svg-title-%s">%s</title>',
					$unique_id,
					esc_html( $this->title )
				);

				if ( $this->desc ) {

					$title_desc .= sprintf(
						'<desc id="svg-desc-%s">%s</desc>',
						$unique_id,
						esc_html( $this->desc )
					);
				}

				$inner_html = $title_desc . $inner_html;

			} else {
				$attr['aria-hidden'] = 'true';
				$attr['focusable']   = 'false';
			}

			$attr['role'] = 'img';

			if ( $this->class ) {
				$attr['class'] = $this->class;
			}

			$attr = App::resolve( Attributes::class, [
				'name'    => 'svg',
				'context' => $this->name,
				'attr'    => $attr
			] );

			$svg = sprintf( '<svg %s>%s</svg>', $attr->render(), $inner_html );
		}

		return $svg;
	}

	/**
	 * Renders the SVG output.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function display() {

		echo $this->render();
	}
}
