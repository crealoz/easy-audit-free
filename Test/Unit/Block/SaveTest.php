<?php

namespace Crealoz\EasyAudit\Test\Unit\Block;

use PHPUnit\Framework\TestCase;
use Crealoz\EasyAudit\Block\Adminhtml\Widget\Button\Save;

class SaveTest extends TestCase
{
    public function testGetButtonData()
    {
        $saveButton = $this->getMockBuilder(Save::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();
        $expected = [
            'label' => __('Request Audit'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'sort_order' => 90,
        ];

        $actual = $saveButton->getButtonData();

        $this->assertEquals($expected, $actual);
    }
}
