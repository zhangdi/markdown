<?php


namespace App\Commands;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class HtmlCommand extends Command
{
    const THEME_DEFAULT = 'default';
    public $themePath;
    public $theme;

    public static function getDefaultName()
    {
        return 'to-html';
    }

    protected function configure()
    {
        $this->themePath = dirname(dirname(__DIR__)) . '/themes';
        $this->setDescription('将 Markdown 格式转换为 HTML 格式')
            ->addArgument('markdown-file', InputArgument::REQUIRED, 'Markdown 文件')
            ->addArgument('html-file', InputArgument::REQUIRED, '转换成功后的 HTML 文件')
            ->addOption('theme', null, InputOption::VALUE_REQUIRED, 'HTML 主题', self::THEME_DEFAULT);
    }

    protected function toHtml($markdown, $title)
    {
        $parsedown = new \Parsedown();
        $content = $parsedown->parse($markdown);

        $template = file_get_contents($this->themePath . '/' . $this->theme . '.html');

        return strtr($template, [
            '{{title}}' => $title,
            '{{content}}' => $content,
        ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $markdownFile = $input->getArgument('markdown-file');
        $htmlFile = $input->getArgument('html-file');
        $this->theme = $input->getOption('theme');

        $output->writeln('校验 Markdown 文件: ' . $markdownFile);
        if (!file_exists($markdownFile)) {
            $output->writeln('<error>Markdown 文件不存在</error>');
            return 1;
        }

        $output->writeln('读取 Markdown 文件: ' . $markdownFile);
        $markdownContent = file_get_contents($markdownFile);

        $output->writeln('校验 HTML 文件: ' . $htmlFile);
        $htmlExtension = pathinfo($htmlFile, PATHINFO_EXTENSION);
        $title = pathinfo($htmlFile, PATHINFO_BASENAME);
        if (!in_array(strtolower($htmlExtension), ['html', 'htm'])) {
            $output->writeln('<error>HTML 文件格式错误，文件格式如下: html, htm</error>');
            return 1;
        }

        $output->writeln('开始转换...');
        $htmlContent = $this->toHtml($markdownContent, $title);
        $output->writeln('转换完成');

        $output->writeln('开始保存...');
        if (file_put_contents($htmlFile, $htmlContent)) {
            $output->writeln('<info>保存成功</info>');
        } else {
            $output->writeln('<error>保存失败</error>');
            return 1;
        }
    }
}
