<?php

namespace AppBundle\Parser;

use Knp\Bundle\MarkdownBundle\Parser\MarkdownParser;

/**
 * Custom Markdown Parser
 */
class KontentikaParser extends MarkdownParser
{
    /**
     * @var array Enabled features
     */
    protected $features = array(
        'header' => false,
        'list' => true,
        'horizontal_rule' => true,
        'table' => false,
        'foot_note' => true,
        'fenced_code_block' => false,
        'abbreviation' => true,
        'definition_list' => false,
        'inline_link' => true, // [link text](url "optional title")
        'reference_link' => true, // [link text] [id]
        'shortcut_link' => true, // [link text]
        'images' => true,
        'html_block' => false,
        'block_quote' => true,
        'code_block' => false,
        'auto_link' => true,
        'auto_mailto' => false,
        'entities' => true,
        'no_html' => true,
    );

    /**
     * @param $text
     */
    public function transformMarkdown($text)
    {
        $text = preg_replace("/\r\n|\n\r/", "\n\r\n\r", $text);

        return parent::transform($text);
    }
}
