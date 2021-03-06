<?php

namespace Sade;

use Sade\Bridges\Node;
use Sade\Component\Component;
use Sade\Contracts\Sade as SadeContract;
use Sade\Config\Config;
use Sade\Container\Container;

class Sade extends Container implements SadeContract
{
    /**
     * Components directory.
     *
     * @var string
     */
    protected $dir = '';

    /**
     * Current component file.
     *
     * @var string
     */
    protected $file = '';

    /**
     * File directory.
     *
     * @var string
     */
    protected $fileDir = '';

    /**
     * Sade options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Rendered components.
     *
     * @var array
     */
    protected $rendered = [];

    /**
     * Component tags.
     *
     * @var array
     */
    protected $tags = [
        'template',
        'script',
        'style',
    ];

    /**
     * Sade construct.
     *
     * @param string $dir
     * @param array  $options
     */
    public function __construct($dir = '', array $options = [])
    {
        $this->setupDir($dir);
        $this->setupOptions($options);
        $this->setupContainer();
        $this->readCustomConfig();
    }

    /**
     * Get component file with directory.
     *
     * @param  string $file
     *
     * @return string
     */
    protected function file($file)
    {
        if (file_exists(realpath($file)) && strpos($file, $this->fileDir) !== false) {
            return $file;
        }

        $dir = rtrim($this->fileDir, '/') . '/';
        $file = ltrim($file, '/');

        if (strpos($file, $dir) !== false) {
            return $file;
        }

        return realpath($dir . $file);
    }

    /**
     * Set the only type (template, script or style) to include in the rendering.
     *
     * @param  string $type
     *
     * @return string
     */
    public function only($type)
    {
        $types = $this->tags;
        $options = [];

        foreach ($types as $key) {
            if ($key === $type) {
                continue;
            }

            $options[$key] = [
                'enabled' => false
            ];
        }

        return new Sade($this->dir, $options);
    }

    /**
     * Read custom config file.
     */
    protected function readCustomConfig()
    {
        $file = $this->get('config.file');
        $path = realpath($file);
        if (!file_exists($path)) {
            $path = realpath($this->dir . '/' . $file);
        }

        if (!file_exists($path)) {
            return;
        }

        $customConfig = require $path;
        if (!is_callable($customConfig)) {
            return;
        }

        call_user_func($customConfig, $this);
    }

    /**
     * Render component file.
     *
     * @param  string $file
     * @param  array  $data
     *
     * @return mixed
     */
    public function render($file, array $data = [])
    {
        if (is_array($file)) {
            $output = '';

            foreach ($file as $item) {
                $output .= $this->render($item) . "\n";
            }

            return $output;
        }

        // Default to `index.php` when only passing a directory.
        if (is_dir($this->file($file))) {
            $file = $file . '/index.php';
        }

        // Store additional directories.
        $dirs = explode('/', $file);
        $dirs = array_slice($dirs, 0, count($dirs) -1);
        $dirs = implode('/', $dirs);

        if (strpos($dirs, $this->dir) !== false) {
            $this->fileDir = $dirs;
        } else {
            $this->fileDir = implode('/', [$this->dir, $dirs]);
        }

        // Remove any path in file.
        $file = explode('/', $file);
        $file = array_pop($file);

        $filepath = $this->file($file);

        // Bail if file don't exists.
        if (!file_exists($filepath)) {
            return;
        }

        // Only render template tag if file already rendered.
        if (!empty($this->rendered[$filepath]['template']) && $this->get('cache')) {
            return $this->rendered[$filepath]['template'];
        }

        $component = new Component($this, $this->fileDir);

        $output = $component->render($file, $data);

        $this->rendered[$filepath] = $output;

        return trim(implode('', array_values($output)));
    }

    /**
     * Setup container.
     */
    protected function setupContainer()
    {
        $this->set('sade.bridges.node', new Node(getcwd(), $this->get('node')));
    }

    /**
     * Setup components directory.
     *
     * @param string $dir
     */
    protected function setupDir($dir)
    {
        $cwd = getcwd();

        if (!is_string($dir) || empty($dir)) {
            $dir = $cwd;
        }

        if (strpos($dir, $cwd) === false) {
            $dir = rtrim($cwd, '/') . '/' . ltrim($dir, '/');
        }

        $this->dir = $this->fileDir = $dir;
    }

    /**
     * Setup options.
     *
     * @param array $options
     */
    protected function setupOptions($options)
    {
        $defaults = [
            'config'   => [
                'file' => 'sade.php',
            ],
            'cache'    => true,
            'node'     => [
                'file' => 'sade.js',
                'path' => 'node',
            ],
            'scoped'   => false,
            'script'   => [
                'class'   => \Sade\Component\Script::class,
                'enabled' => true,
            ],
            'style'    => [
                'class'   => \Sade\Component\Style::class,
                'enabled' => true,
                'tag'     => 'script',
            ],
            'template' => [
                'class'   => \Sade\Component\Template::class,
                'enabled' => true,
            ],
            'url'      => [
                'base_path' => '',
            ],
        ];

        $this->set(array_replace_recursive($defaults, $options));
    }

    /**
     * Get tags.
     *
     * @return array
     */
    public function tags()
    {
        return $this->tags;
    }

    /**
     * Call dynamic methods.
     *
     * @param  string $method
     * @param  mixed  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (in_array($method, $this->tags, true)) {
            return $this->only($method)->render($parameters);
        }
    }
}
