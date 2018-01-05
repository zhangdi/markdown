<?php


namespace App\Converters;


class MarkdownToHtmlConvert extends Converter
{
    const THEME_DEFAULT = 'default';

    public $theme = self::THEME_DEFAULT;
    public $pageTitle;

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
    public function convert($markdown)
    {
        $parsedown = new \Parsedown();
        $content = $parsedown->parse($markdown);

        $template = file_get_contents($this->getThemePath() . '/' . $this->theme . '.html');
        return strtr($template, [
            '{{title}}' => $this->pageTitle,
            '{{content}}' => $content,
        ]);
    }

}
