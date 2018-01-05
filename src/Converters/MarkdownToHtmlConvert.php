<?php


namespace App\Converters;


class MarkdownToHtmlConvert extends Converter
{
    const THEME_DEFAULT = 'default';

    public $markdown;
    public $theme = self::THEME_DEFAULT;
    public $pageTitle;

    public function __construct(string $markdown)
    {
        $this->markdown = $markdown;
    }

    /**
     * @return string
     */
    public function getThemePath()
    {
        return dirname(dirname(__DIR__)) . '/themes';
    }

    /**
     * 可用主题
     *
     * @return array
     */
    public static function getThemes()
    {
        return [
            'default',
            'bootstrap',
            'solarized-dark',
            'solarized-light'
        ];
    }

    /**
     * @inheritdoc
     */
    public function convert()
    {
        $parsedown = new \Parsedown();
        $content = $parsedown->parse($this->markdown);

        $template = file_get_contents($this->getThemePath() . '/' . $this->theme . '.html');
        return strtr($template, [
            '{{title}}' => $this->pageTitle,
            '{{content}}' => $content,
        ]);
    }

}
