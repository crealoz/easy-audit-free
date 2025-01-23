<?php

namespace Crealoz\EasyAudit\Processor\Files\View;

use Crealoz\EasyAudit\Api\Processor\Audit\FileProcessorInterface;
use Crealoz\EasyAudit\Processor\Files\AbstractXmlProcessor;

class Cacheable extends AbstractXmlProcessor implements FileProcessorInterface
{
    protected array $allowedAreas = ['sales', 'customer', 'gift', 'message'];

    public function getProcessorName(): string
    {
        return __('Cacheable');
    }

    public function getAuditSection(): string
    {
        return __('Views');
    }

    public function prepopulateResults(): void
    {
        parent::prepopulateResults();
        $this->results = [
            'hasErrors' => false,
            'errors' => [],
            'warnings' => [],
            'suggestions' => [
                'useCacheable' => $this->getUseCacheableEntry()
            ]
        ];
    }

    private function getUseCacheableEntry(): array
    {
        $title = __('Use of cacheable="false" in layout XML');
        $explanation = __('Cacheable="false" is not recommended for dynamic blocks and should be avoided. If you need to use cacheable="false" for a block, make sure it is necessary and that the block should not be cached (e.g. : Customer Section, sales...).');
        return [
            'title' => $title,
            'explanation' => $explanation,
            'files' => []
        ];
    }

    public function run(): void
    {
        $blocksNotCached = $this->getContent()->xpath('//block[@cacheable="false"]');
        if (count($blocksNotCached) > 0) {
            $this->results['hasErrors'] = true;
            foreach ($blocksNotCached as $block) {
                $blockName = (string) $block->attributes()->name;
                foreach ($this->allowedAreas as $area) {
                    if (strpos($blockName, $area) !== false) {
                        continue 2;
                    }
                }
                $this->results['suggestions']['useCacheable']['files'][] = $blockName;
            }
        }
    }

    public function getProcessorTag(): string
    {
        return 'cacheable';
    }
}