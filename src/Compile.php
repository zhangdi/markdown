<?php


namespace App;


use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Compile
{
    public function compile($pharFile)
    {
        if (file_exists($pharFile)) {
            unlink($pharFile);
        }

        $phar = new \Phar($pharFile, 0, 'markdown.phar');
        $phar->setSignatureAlgorithm(\Phar::SHA1);

        $phar->startBuffering();

        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->notName('build')
            ->notName('Compile.php')
            ->in(__DIR__ . '/..');

        foreach ($finder as $file) {
            $this->addFile($phar, $file);
        }

        $this->addAgentBin($phar);

        $phar->setStub($this->getStub());

        $phar->compressFiles(\Phar::GZ);

        $phar->stopBuffering();

        unset($phar);

    }

    private function getRelativePath(SplFileInfo $file): string
    {
        $realPath = $file->getRealPath();
        $pathPrefix = dirname(__DIR__) . DIRECTORY_SEPARATOR;

        $position = strpos($realPath, $pathPrefix);
        if ($position === false) {
            $relativePath = $realPath;
        } else {
            $relativePath = substr_replace($realPath, '', $position, strlen($pathPrefix));
        }
        return $relativePath;
    }

    private function addFile(\Phar $phar, SplFileInfo $file)
    {
        $path = $this->getRelativePath($file);
        $content = file_get_contents($file);
        $phar->addFromString($path, $content);
    }

    private function addAgentBin(\Phar $phar)
    {
        $content = file_get_contents(dirname(__DIR__) . '/bin/markdown');
        $content = preg_replace('{^#!/usr/bin/env php\s*}', '', $content);
        $phar->addFromString('bin/markdown', $content);
    }

    private function getStub()
    {
        return <<<'EOF'
#!/usr/bin/env php
<?php
/*
 * This file is part of markdown.
 *
 * (c) Zhang Di <zhangdi_me@163.com>
 *
 */
Phar::mapPhar('markdown.phar');

require 'phar://markdown.phar/bin/markdown';

__HALT_COMPILER();
EOF;

    }
}
