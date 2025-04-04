<?php

namespace Crealoz\EasyAudit\Test\Unit\Ui\Form;

use Codeception\PHPUnit\TestCase;
use Crealoz\EasyAudit\Service\PrManager;
use Crealoz\EasyAudit\Ui\Component\Form\Field\PatchType;

class PatchTypeTest extends TestCase
{
    public function testToOptionArray()
    {
        $patchType = new PatchType();
        $this->assertIsArray($patchType->toOptionArray());
        $this->assertEquals([
            ['label' => 'Patch', 'value' => PrManager::PATCH_TYPE_PATCH],
            ['label' => 'Git', 'value' => PrManager::PATCH_TYPE_GIT],
        ], $patchType->toOptionArray());
    }
}