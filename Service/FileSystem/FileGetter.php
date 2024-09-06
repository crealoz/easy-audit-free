<?php

namespace Crealoz\EasyAudit\Service\FileSystem;

use RegexIterator;

/**
 * This class is responsible for getting files from a directory but is not intended to be used directly. Usage is to
 * create a virtual class using di.xml with the path and pattern as arguments.
 *
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 */
class FileGetter implements FileGetterInterface
{
    protected array $ignoredFolders;

    public function __construct(
        protected string $path,
        protected string $pattern,
        private readonly FilterGetter $filterGetter,
        protected string $filter = '',
    )
    {
    }

    public function execute(): array
    {
        $files = [];
        $directory = new \RecursiveDirectoryIterator($this->path);
        $iterator = new \RecursiveIteratorIterator($directory);
        $regex = new RegexIterator($iterator, $this->pattern, RegexIterator::GET_MATCH);
        if ($this->filter !== '') {
            $this->ignoredFolders = $this->filterGetter->getFilter($this->filter);
            $progressBar = null;
            if (php_sapi_name() === 'cli') {
                $output = new \Symfony\Component\Console\Output\ConsoleOutput();
                $output->writeln(PHP_EOL.'Filtering files...');
                $progressBar = new \Symfony\Component\Console\Helper\ProgressBar($output, iterator_count($regex));
            }
            foreach ($regex as $file) {
                $progressBar?->advance();
                $file = $file[0];
                $isIgnored = false;
                foreach ($this->ignoredFolders as $ignoredFolder) {
                    if (str_contains($file, $ignoredFolder)) {
                        $isIgnored = true;
                        break;
                    }
                }
                if (!$isIgnored) {
                    $files[] = $file;
                }
            }
            $progressBar?->finish();
        } else {
            foreach ($regex as $file) {
                $files[] = $file[0];
            }
        }
        return $files;
    }


}