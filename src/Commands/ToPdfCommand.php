<?php


namespace App\Commands;


use App\Converters\MarkdownToPdfConverter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ToPdfCommand extends Command
{
    public $markdownFile;
    public $pdfFile;

    public static function getDefaultName()
    {
        return 'to-pdf';
    }

    protected function configure()
    {
        $this->setDescription('将 Markdown 格式转换为 PDF 格式')
            ->addArgument('markdown-file', InputArgument::REQUIRED, 'Markdown 文件')
            ->addArgument('pdf-file', InputArgument::REQUIRED, '转换成功后的 PDF 文件');
    }

    protected function validate(InputInterface $input, OutputInterface $output)
    {
        if (!file_exists($this->markdownFile)) {
            $output->writeln('<error>Markdown 文件不存在</error>');
            return false;
        }

        $htmlExtension = pathinfo($this->pdfFile, PATHINFO_EXTENSION);
        if (!in_array(strtolower($htmlExtension), ['pdf'])) {
            $output->writeln('<error>PDF 文件格式错误，文件格式如下: pdf</error>');
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
        $this->pdfFile = $input->getArgument('pdf-file');
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

        $converter = new MarkdownToPdfConverter();
        $pdfContent = $converter->convert(file_get_contents($this->markdownFile));
        $output->writeln('<info>[完成]</info>');

        $output->write('开始保存... ...');
        if (file_put_contents($this->pdfFile, $pdfContent)) {
            $output->writeln('<info>[完成]</info>');
        } else {
            $output->writeln('<error>[失败]</error>');
            return 1;
        }
    }
}
