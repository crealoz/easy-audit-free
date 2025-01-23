<?php
namespace Crealoz\EasyAudit\Test\Unit\Ui;

use Crealoz\EasyAudit\Ui\Audit\Listing\FileColumn;
use Crealoz\EasyAudit\Api\FileRepositoryInterface;
use Magento\Framework\Url;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use PHPUnit\Framework\TestCase;

class FileColumnTest extends TestCase
{
    private $contextMock;
    private $uiComponentFactoryMock;
    private $backendUrlMock;
    private $fileRepositoryMock;
    private $fileColumn;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(ContextInterface::class);
        $this->uiComponentFactoryMock = $this->createMock(UiComponentFactory::class);
        $this->backendUrlMock = $this->createMock(\Magento\Backend\Model\UrlInterface::class);
        $this->fileRepositoryMock = $this->createMock(FileRepositoryInterface::class);

        $this->fileColumn = new FileColumn(
            $this->contextMock,
            $this->uiComponentFactoryMock,
            $this->backendUrlMock,
            $this->fileRepositoryMock
        );
    }

    public function testPrepareDataSourceAddsFilesAndLink()
    {
        $dataSource = [
            'data' => [
                'items' => [
                    ['request_id' => 1],
                    ['request_id' => 2]
                ]
            ]
        ];

        $this->fileRepositoryMock->method('hasFiles')
            ->willReturnMap([
                [1, true],
                [2, false]
            ]);

        $this->backendUrlMock->method('getUrl')
            ->with('easyaudit/request/download', ['request_id' => 1, '_secure' => true])
            ->willReturn('http://example.com/download/1');

        $result = $this->fileColumn->prepareDataSource($dataSource);

        $this->assertEquals('Download files(s)', $result['data']['items'][0]['files']);
        $this->assertEquals('http://example.com/download/1', $result['data']['items'][0]['link']);
        $this->assertArrayNotHasKey('files', $result['data']['items'][1]);
        $this->assertArrayNotHasKey('link', $result['data']['items'][1]);
    }
}