<?php

namespace Crealoz\EasyAudit\Service\Processor\View;

use Crealoz\EasyAudit\Service\Processor\AbstractProcessor;
use Crealoz\EasyAudit\Service\Processor\ProcessorInterface;

/**
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 */
class Cacheable extends AbstractProcessor implements ProcessorInterface
{
    protected string $processorName = 'Cacheable';

    protected string $auditSection = 'Views';

    protected array $results = [
        'hasErrors' => false,
        'errors' => [],
        'warnings' => [
            'useCacheable' => [
                'title' => 'Use of cacheable="false" in layout XML',
                'explanation' => 'Cacheable="false" is not recommended for dynamic blocks and should be avoided. If you
                need to use cacheable="false" for a block, make sure it is necessary and that the block should not be
                 cached (e.g. : Customer Section, sales...).',
                'files' => []
            ]
        ],
        'suggestions' => []
    ];

    protected array $allowedAreas = ['sales', 'customer', 'gift', 'message'];

    public function run($input): array
    {
        if (!$input instanceof \SimpleXMLElement) {
            throw new \InvalidArgumentException('Input is not an instance of SimpleXMLElement');
        }

        $blocksNotCached = $input->xpath('//block[@cacheable="false"]');
        if (count($blocksNotCached) > 0) {
            $this->results['hasErrors'] = true;
            foreach ($blocksNotCached as $block) {
                // If one of the allowed areas is found in the name of the block, we skip it
                $blockName = (string) $block->attributes()->name;
                foreach ($this->allowedAreas as $area) {
                    if (str_contains($blockName, $area)) {
                        continue 2;
                    }
                }
                $this->results['warnings']['useCacheable']['files'][] = (string) $block->attributes()->name;
            }
        }
        return $this->results;
    }
}