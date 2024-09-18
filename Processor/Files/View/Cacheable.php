<?php

namespace Crealoz\EasyAudit\Processor\Files\View;

use Crealoz\EasyAudit\Processor\Files\AbstractProcessor;
use Crealoz\EasyAudit\Processor\Files\ProcessorInterface;

class Cacheable extends AbstractProcessor implements ProcessorInterface
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

    public function run($input)
    {
        if (!$input instanceof \SimpleXMLElement) {
            throw new \InvalidArgumentException('Input is not an instance of SimpleXMLElement');
        }

        $blocksNotCached = $input->xpath('//block[@cacheable="false"]');
        if (count($blocksNotCached) > 0) {
            $this->results['hasErrors'] = true;
            foreach ($blocksNotCached as $block) {
                $blockName = (string) $block->attributes()->name;
                foreach ($this->allowedAreas as $area) {
                    if (str_contains($blockName, $area)) {
                        continue 2;
                    }
                }
                $this->results['suggestions']['useCacheable']['files'][] = $blockName;
            }
        }
    }
}