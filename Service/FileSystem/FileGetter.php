<?php

namespace Crealoz\EasyAudit\Service\FileSystem;

use Crealoz\EasyAudit\Api\FileSystem\FileGetterInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
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
        private readonly DirectoryList $directoryList,
        protected string $filter = '',
    )
    {
    }

    /**
     * This function will return an array of files from the directory. If a filter is set, it will be used to filter the
     * files. Time of execution is displayed in the console.
     * @return array
     */
    public function execute(): array
    {
        $files = [];
        $magePath = $this->directoryList->getRoot();
        $absolutePath = $magePath.'/'.$this->path;
        $directory = new \RecursiveDirectoryIterator($absolutePath);
        $iterator = new \RecursiveIteratorIterator($directory);
        $regex = new RegexIterator($iterator, $this->pattern, RegexIterator::GET_MATCH);
        if ($this->filter !== '') {
            $files = $this->applyFilter($regex);
        } else {
            foreach ($regex as $file) {
                if (str_contains($file[0], 'Test')) {
                    continue;
                }
                $files[] = $file[0];
            }
        }
        return $files;
    }

    private function applyFilter($regex): array
    {
        $files = [];
        $this->ignoredFolders = $this->filterGetter->getFilter($this->filter);
        foreach ($regex as $file) {
            $file = $file[0];
            $isIgnored = false;
            foreach ($this->ignoredFolders as $ignoredFolder) {
                if (str_contains((string) $file, (string) $ignoredFolder)) {
                    $isIgnored = true;
                    break;
                }
            }
            if (!$isIgnored) {
                $files[] = $file;
            }
        }
        return $files;
    }

}