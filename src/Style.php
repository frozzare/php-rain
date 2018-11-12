<?php

namespace Frozzare\Rain;

use Sabberworm\CSS\Parser;

class Style
{
    /**
     * Style options.
     *
     * @var array
     */
    protected $options = [
        'attributes' => [],
        'content'    => '',
        'id'         => '',
    ];

    /**
     * CSS Parser
     *
     * @var \Sabberworm\CSS\Parser
     */
    protected $parser;

    /**
     * Style construct.
     *
     * @param array $options
     */
    public function __construct($options)
    {
        $options = is_array($options) ? $options : [];
        $this->options = array_merge($this->options, $options);
        $this->parser = new Parser($this->options['content']);
    }

    /**
     * Render style html.
     *
     * @return string
     */
    public function render()
    {
        $attributes = $this->options['attributes'];

        if (!is_array($attributes)) {
            $attributes = [];
        }

        if (empty($attributes['type'])) {
            $attributes['type'] = 'text/css';
        }

        $attr_html = '';

        foreach ($attributes as $key => $value) {
            $attr_html .= sprintf('%s="%s" ', $key, $value);
        }

        $css = $this->parser->parse();

        foreach ($css->getAllDeclarationBlocks() as $block) {
            foreach ($block->getSelectors() as $selector) {
                $selector->setSelector('#' . $this->options['id'] . ' ' . $selector->getSelector());
            }
        }

        return sprintf('<style %s>%s</style>', $attr_html, $css->render());
    }
}
