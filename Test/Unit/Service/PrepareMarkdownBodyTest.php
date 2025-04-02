<?php
namespace Crealoz\EasyAudit\Test\Unit\Service;

use Crealoz\EasyAudit\Model\Result;
use Crealoz\EasyAudit\Service\PrepareMarkdownBody;
use PHPUnit\Framework\TestCase;

class PrepareMarkdownBodyTest extends TestCase
{
    private $result;
    
    protected function setUp(): void
    {
        $this->result = $this->createMock(Result::class);
        $this->result->method('getSummary')->willReturn('Test Summary');
    }
    
    /**
     * @test
     */
    public function testExecuteWithEmptyResult()
    {

        $prepareMarkdownBody = new PrepareMarkdownBody();
        $body = $prepareMarkdownBody->execute($this->result);

        $this->assertEquals("Test Summary\n\n### Entries\n", $body);
    }

    /**
     * @test
     */
    public function testExecuteWithSingleEntryAndNoSubEntries()
    {
        $entryMock = $this->createMock(Result\Entry::class);
        $entryMock->method('getEntry')->willReturn('First Entry');

        $this->result
            ->method('getEntries')
            ->willReturn([$entryMock]);

        $prepareMarkdownBody = new PrepareMarkdownBody();
        $body = $prepareMarkdownBody->execute($this->result);

        $expectedBody = "Test Summary\n\n### Entries\n\n\nFirst Entry";
        $this->assertEquals($expectedBody, $body);
    }

    /**
     * @test
     */
    public function testExecuteWithMultipleEntriesAndSubEntries()
    {
        $entryMock1 = $this->createMock(Result\Entry::class);
        $entryMock2 = $this->createMock(Result\Entry::class);
        $subEntryMock1 = $this->createMock(Result\Entry\SubEntry::class);
        $subEntryMock2 = $this->createMock(Result\Entry\SubEntry::class);
        $entryMock1->method('getEntry')->willReturn('First Entry');
        $subEntryMock1->method('getSubentry')->willReturn('First SubEntry');
        $entryMock2->method('getEntry')->willReturn('Second Entry');
        $subEntryMock2->method('getSubentry')->willReturn('Second SubEntry');
        $entryMock1->method('getSubEntries')->willReturn([$subEntryMock1]);
        $entryMock2->method('getSubEntries')->willReturn([$subEntryMock2]);
        $this->result
            ->method('getEntries')
            ->willReturn([$entryMock1, $entryMock2]);

        $prepareMarkdownBody = new PrepareMarkdownBody();
        $body = $prepareMarkdownBody->execute($this->result);

        $expectedBody = "Test Summary\n\n### Entries\n\n\nFirst Entry\n- First SubEntry\n\nSecond Entry\n- Second SubEntry";

        $this->assertEquals($expectedBody, $body);
    }

    protected function tearDown(): void
    {
        unset($this->result);
    }
}