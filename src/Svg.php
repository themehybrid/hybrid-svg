<?php

namespace Hybrid\Svg;

use Hybrid\App;

class Svg {

	public static function instance( $file, array $args = [] ) {

		return App::resolve( SvgMaker::class, compact( 'file', 'args' ) );
	}

	public static function __callStatic( $method, $args ) {

		$instance = static::instance( ...$args );

		return $instance ? $instance->$method() : null;
	}
}
