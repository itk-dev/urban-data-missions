<?php

namespace App\Faker\Provider;

use Faker\Generator;
use Faker\Provider\Base;
use InvalidArgumentException;
use Symfony\Component\Filesystem\Filesystem;

class AppProvider extends Base
{
    /** @var array */
    private $options;

    /** @var Filesystem */
    private $filesystem;

    public function __construct(Generator $generator, Filesystem $filesystem, array $appFakerProviderOptions)
    {
        parent::__construct($generator);
        $this->filesystem = $filesystem;
        $this->options = $appFakerProviderOptions;
    }

    public function uploadFile(string $type, string $source)
    {
        $sourceFilename = $this->getSourceFilename($source);
        if (null === $sourceFilename) {
            throw new InvalidArgumentException(sprintf('Cannot find source file: %s', $source));
        }
        $targetFilename = $this->getTargetFilename($type, $source);
        $this->filesystem->mkdir(dirname($targetFilename));

        $this->filesystem->copy($sourceFilename, $targetFilename);

        return $source;
    }

    private function getSourceFilename(string $path)
    {
        foreach ($this->options['source_dirs'] as $dir) {
            $filename = $this->buildPath($this->options['base_dir'], $dir, $path);
            if (file_exists($filename)) {
                return $filename;
            }
        }

        return null;
    }

    private function getTargetFilename(string $type, string $source)
    {
        if (!isset($this->options['target_dirs'][$type])) {
            throw new InvalidArgumentException(sprintf('Invalid file type: %s', $type));
        }
        $targetDir = $this->options['target_dirs'][$type];

        return $this->buildPath($this->options['base_dir'], $targetDir, $source);
    }

    private function buildPath()
    {
        $parts = [];
        foreach (func_get_args() as $index => $part) {
            $parts[] = 0 === $index ? rtrim($part, '/') : trim($part, '/');
        }

        return implode('/', $parts);
    }
}
