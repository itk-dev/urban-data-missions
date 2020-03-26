<?php

namespace App\Faker\Provider;

use Faker\Generator;
use Faker\Provider\Base;
use InvalidArgumentException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppProvider extends Base
{
    /** @var array */
    private $options;

    /** @var Filesystem */
    private $filesystem;

    public function __construct(Generator $generator, Filesystem $filesystem, array $options)
    {
        parent::__construct($generator);
        $this->filesystem = $filesystem;
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['base_dir', 'source_dirs', 'target_dirs'])
            ->setAllowedTypes('base_dir', 'string')
            ->setAllowedTypes('source_dirs', 'string[]')
            ->setAllowedTypes('target_dirs', 'string[]');
    }

    /**
     * Fake upload of a file.
     *
     * @param string $type   The upload type
     * @param string $source The source path relative to a path in source_dir
     *
     * @return string The path to the uploaded file; relative to the target dir
     */
    public function uploadFile(string $type, string $source): string
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
