<?php


namespace App\Commands;


use App\Converters\MarkdownToHtmlConvert;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ToHtmlCommand extends Command
{
    public $theme;
    public $markdownFile;
    public $htmlFile;

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
            ->addOption('theme', null, InputOption::VALUE_REQUIRED, 'HTML 主题', MarkdownToHtmlConvert::THEME_DEFAULT);
    }

    protected function validate(InputInterface $input, OutputInterface $output)
    {
        if (!file_exists($this->markdownFile)) {
            $output->writeln('<error>Markdown 文件不存在</error>');
            return false;
        }

        $htmlExtension = pathinfo($this->htmlFile, PATHINFO_EXTENSION);
        if (!in_array(strtolower($htmlExtension), ['html', 'htm'])) {
            $output->writeln('<error>HTML 文件格式错误，文件格式如下: html, htm</error>');
            return false;
        }

        if (!in_array($this->theme, MarkdownToHtmlConvert::getThemes())) {
            $output->writeln("<error>主题 \"{$this->theme}\" 不存在</error> <comment>[可用主题: \"" . implode('", "', MarkdownToHtmlConvert::getThemes()) . "\"]</comment>");
            return false;
        }
        return true;
    }

    /**
     * 加载数据，将用户输入的数据加载到类中
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function load(InputInterface $input, OutputInterface $output)
    {
        $this->markdownFile = $input->getArgument('markdown-file');
        $this->htmlFile = $input->getArgument('html-file');
        $this->theme = $input->getOption('theme');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $style = new OutputFormatterStyle('red');
        $output->getFormatter()->setStyle('error', $style);

        $this->load($input, $output);

        if (!$this->validate($input, $output)) {
            return 1;
        }

        $output->write('开始转换... ...');

        $converter = new MarkdownToHtmlConvert();
        $converter->pageTitle = pathinfo($this->htmlFile,PATHINFO_BASENAME);
        $converter->theme = $this->theme;

        $htmlContent = $converter->convert(file_get_contents($this->markdownFile));
        $output->writeln('<info>[完成]</info>');

        $output->write('开始保存... ...');
        if (file_put_contents($this->htmlFile, $htmlContent)) {
            $output->writeln('<info>[完成]</info>');
        } else {
            $output->writeln('<error>[失败]</error>');
            return 1;
        }
    }
}
