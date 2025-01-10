<?php

namespace Crealoz\EasyAudit\Test\Unit\Block;

use PHPUnit\Framework\TestCase;
use Crealoz\EasyAudit\Block\Adminhtml\Widget\Button\Back;

class BackTest extends TestCase
{
    public function testGetBackUrl()
    {
        $button = $this->getMockBuilder(Back::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getUrl'])
            ->getMock();

        $button->expects($this->once())
            ->method('getUrl')
            ->with('*/*/')
            ->willReturn('expected-url');

        $this->assertEquals('expected-url', $button->getBackUrl());
    }

    public function testGetButtonData()
    {
        $button = $this->getMockBuilder(Back::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBackUrl'])
            ->getMock();

        $button->expects($this->once())
            ->method('getBackUrl')
            ->willReturn('expected-url');

        $expected = [
            'label' => __('Back'),
            'on_click' => "location.href = 'expected-url';",
            'sort_order' => 10,
        ];

        $this->assertEquals($expected, $button->getButtonData());
    }
}
