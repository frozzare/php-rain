<?php

use Sade\Contracts\Component\Tag;

class CustomTemplateTag implements Tag
{
    /**
     * Template construct.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
    }

    /**
     * Render template.
     *
     * @return string
     */
    public function render()
    {
        return 'CustomTemplateTag';
    }
}
