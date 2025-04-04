<?php

namespace Crealoz\EasyAudit\Test\Unit\Block;

use PHPUnit\Framework\TestCase;
use Crealoz\EasyAudit\Block\Adminhtml\Widget\Button\Result\Patch;

class PatchTest extends TestCase
{
    /**
     * @var Patch
     */
    private $patch;

    protected function setUp(): void
    {
        $this->patch = new Patch();
    }

    public function testGetButtonData()
    {
        // Execute the method
        $result = $this->patch->getButtonData();

        // ASSERTIONS
        // Check that the result is an array
        $this->assertIsArray($result);

        // Check that the label is as expected
        $this->assertArrayHasKey('label', $result);
        $this->assertEquals('Request Audit', $result['label']); // Label should match

        // Check that the class is as expected
        $this->assertArrayHasKey('class', $result);
        $this->assertEquals('save primary', $result['class']); // Class should match
        
        // Check that the data_attribute structure is correct
        $this->assertArrayHasKey('data_attribute', $result);
        $this->assertIsArray($result['data_attribute']);
        $this->assertArrayHasKey('mage-init', $result['data_attribute']);
        $this->assertArrayHasKey('form-role', $result['data_attribute']);
        $this->assertEquals(['button' => ['event' => 'save']], $result['data_attribute']['mage-init']);
        $this->assertEquals('save', $result['data_attribute']['form-role']);
        
        // Check the sort_order key and value
        $this->assertArrayHasKey('sort_order', $result);
        $this->assertEquals(90, $result['sort_order']);
    }
}