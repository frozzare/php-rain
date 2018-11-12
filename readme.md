# Rain

> Work in progress!

Create PHP Components with [Twig](https://twig.symfony.com/doc/2.x/) (2.x). This package will not do any preprocessing or somthing like that.

## Example

Single file:

```php
<template>
    <p>{{ greeting }} World!</p>
    <image alt="{{ greeting }}" />
</template>

<?php
    return [
        'data' => function() {
            return [
                'greeting' => 'Hello'
            ];
        },
        'components' => [
            'image.php'
        ]
    ];
?>

<style scoped>
    p {
        font-size: 2em;
        text-align: center;
    }
</style>
```

Multiple files:

```php
<template src="greeting.twig" />
<style src="greeting.css" />
<script src="greeting.js" />
<?php
    return [
        'data' => function() {
            return [
                'greeting' => 'Hello'
            ];
        },
    ];
?>
```

To render

```php
$rain = new \Frozzare\Rain\Rain( [
    'src_dir' => __DIR__ . '/examples',
    'style'   => [
        // Force scoped style.
        'scoped' => true
    ]
] );

echo $rain->render( 'greeting.php' );
```

## License

MIT © [Fredrik Forsmo](https://github.com/frozzare)