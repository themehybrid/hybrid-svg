# Hybrid\\SVG

Register Hybrid Core service provider:

```php
$app->provider( \Hybrid\Svg\SvgServiceProvider::class );
```

Quick usage notes:

```php
// Output.
Hybrid\Svg\Svg::display( $file, array $args = [] );

// Return.
Hybrid\Svg\Svg::render( $file, array $args = [] );
```

Example:

```php
Hybrid\Svg\Svg::display( 'dist/svg/chevron-down.svg', [
	'title' => __( 'Chevron Down' )
] );
```
