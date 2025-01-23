<?php

namespace Crealoz\EasyAudit\Service\FileSystem;

use Crealoz\EasyAudit\Api\FileSystem\FileGetterInterface;
use RegexIterator;

/**
 * This class is responsible for getting files from a directory but is not intended to be used directly. Usage is to
 * create a virtual class using di.xml with the path and pattern as arguments.
 *
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 */
class FileGetter implements FileGetterInterface
{
    protected string $path;
    protected string $pattern;
    /**
     * @readonly
     */
    private FilterGetter $filterGetter;
    protected string $filter = '';
    protected array $ignoredFolders;

    public function __construct(string $path, string $pattern, FilterGetter $filterGetter, string $filter = '')
    {
        $this->path = $path;
        $this->pattern = $pattern;
        $this->filterGetter = $filterGetter;
        $this->filter = $filter;
    }

    /**
     * This function will return an array of files from the directory. If a filter is set, it will be used to filter the
     * files. Time of execution is displayed in the console.
     * @return array
     */
    public function execute(): array
    {
        $files = [];
        $directory = new \RecursiveDirectoryIterator($this->path);
        $iterator = new \RecursiveIteratorIterator($directory);
        $regex = new RegexIterator($iterator, $this->pattern, RegexIterator::GET_MATCH);
        if ($this->filter !== '') {
            $files = $this->applyFilter($regex);
        } else {
            foreach ($regex as $file) {
                $files[] = $file[0];
            }
        }
        return $files;
    }

    private function applyFilter($regex): array
    {
        $files = [];
        $progressBar = null;
        if (php_sapi_name() === 'cli') {
            $output = new \Symfony\Component\Console\Output\ConsoleOutput();
            $output->writeln(PHP_EOL.'Filtering files...');
            $start = microtime(true);
            $progressBar = new \Symfony\Component\Console\Helper\ProgressBar($output, iterator_count($regex));
        }
        $this->ignoredFolders = $this->filterGetter->getFilter($this->filter);
        if (isset($start)) {
            $output->writeln('It took '.round(microtime(true) - $start, 2).'s to get the filters');
        }
        foreach ($regex as $file) {
            ($nullsafeVariable1 = $progressBar) ? $nullsafeVariable1->advance() : null;
            $file = $file[0];
            $isIgnored = false;
            foreach ($this->ignoredFolders as $ignoredFolder) {
                if (strpos($file, $ignoredFolder) !== false) {
                    $isIgnored = true;
                    break;
                }
            }
            if (!$isIgnored) {
                $files[] = $file;
            }
        }
        if (isset($start)) {
            $output->writeln(PHP_EOL.'Files filtered in '.round(microtime(true) - $start, 2).'s');
        }
        ($nullsafeVariable2 = $progressBar) ? $nullsafeVariable2->finish() : null;
        return $files;
    }

}