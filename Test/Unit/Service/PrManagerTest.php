<?php

namespace Crealoz\EasyAudit\Test\Unit\Service;

use Crealoz\EasyAudit\Api\ResultRepositoryInterface;
use Crealoz\EasyAudit\Exception\InvalidPrTypeException;
use Crealoz\EasyAudit\Service\Config\MiddlewareHost;
use Crealoz\EasyAudit\Service\PrManager;
use Crealoz\EasyAudit\Service\PrManager\BodyPreparerFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\TestCase;

class PrManagerTest extends TestCase
{
    private $curl;
    private $result;
    private $resultRepository;
    private $json;
    private $bodyPreparerFactory;
    private $middlewareHost;
    private $prManager;

    protected function setUp(): void
    {
        $this->curl = $this->createMock(Curl::class);
        $this->result = $this->getMockForAbstractClass(\Crealoz\EasyAudit\Api\Data\ResultInterface::class);
        $this->resultRepository = $this->getMockForAbstractClass(ResultRepositoryInterface::class);
        $this->resultRepository->method('getById')->willReturn($this->result);
        $this->json = $this->createMock(Json::class);
        $this->json->method('unserialize')->willReturnCallback(function ($data) {
            return match ($data) {
                'error' => ['status' => 'error', 'message' => 'Invalid data'],
                'noQueueId' => ['status' => 'success'],
                'success' => ['status' => 'success', 'queue_id' => 123],
                'diff' => ['status' => 'success', 'diff' => 'diff'],
                default => throw new \InvalidArgumentException('Invalid data')
            };
        });
        $this->bodyPreparerFactory = $this->createMock(BodyPreparerFactory::class);
        $this->middlewareHost = $this->createMock(MiddlewareHost::class);
        $this->middlewareHost->method('getHash')->willReturn('1234567890');
        $this->middlewareHost->method('getKey')->willReturn('1234567890');
        $this->middlewareHost->method('isSelfSigned')->willReturn(true);
        $this->middlewareHost->method('getHost')->willReturn('https://example.com');
        $this->prManager = new PrManager(
            $this->curl,
            $this->resultRepository,
            $this->json,
            $this->bodyPreparerFactory,
            $this->middlewareHost
        );
    }

    public function testSendValidPrRequest()
    {
        $this->resultRepository->expects($this->once())->method('save');
        $this->curl
            ->method('post')
            ->willReturn('{"id":1}');
        $this->curl
            ->method('getStatus')
            ->willReturn(200);
        $this->curl
            ->method('getBody')
            ->willReturn('success');
        $this->result->method('getProcessor')->willReturn('aroundToBeforePlugin');
        $response = $this->prManager->sendPrRequest(123, '/path/to/file.php', PrManager::PATCH_TYPE_PATCH);
        $this->assertIsArray($response);
    }

    public function testSendInvalidPrType()
    {
        $this->result->method('getProcessor')->willReturn('aroundToBeforePlugin');
        $this->expectException(InvalidPrTypeException::class);
        $this->prManager->sendPrRequest(123, '/path/to/file.php', 25);
    }

    public function testSendInvalidPrRequest()
    {
        $this->result->method('getProcessor')->willReturn('beforeToBeforePlugin');
        $this->expectException(LocalizedException::class);
        $this->prManager->sendPrRequest(123, '/path/to/file.php', PrManager::PATCH_TYPE_PATCH);
    }

    public function testErroneousResponse()
    {
        $this->result->method('getProcessor')->willReturn('aroundToBeforePlugin');
        $this->expectException(LocalizedException::class);
        $this->curl
            ->method('post')
            ->willReturn('{"id":1}');
        $this->curl
            ->method('getStatus')
            ->willReturn(400);
        $this->prManager->sendPrRequest(123, '/path/to/file.php', PrManager::PATCH_TYPE_PATCH);
    }

    public function testInvalidDataResponse()
    {
        $this->result->method('getProcessor')->willReturn('aroundToBeforePlugin');
        $this->expectException(LocalizedException::class);
        $this->curl
            ->method('post')
            ->willReturn('{"id":1}');
        $this->curl
            ->method('getStatus')
            ->willReturn(200);
        $this->curl
            ->method('getBody')
            ->willReturn('unknown');
        $this->prManager->sendPrRequest(123, '/path/to/file.php', PrManager::PATCH_TYPE_PATCH);
    }

    public function testNoQueueId()
    {
        $this->result->method('getProcessor')->willReturn('aroundToBeforePlugin');
        $this->expectException(LocalizedException::class);
        $this->curl
            ->method('post')
            ->willReturn('{"id":1}');
        $this->curl
            ->method('getStatus')
            ->willReturn(200);
        $this->curl
            ->method('getBody')
            ->willReturn('noQueueId');
        $this->prManager->sendPrRequest(123, '/path/to/file.php', PrManager::PATCH_TYPE_PATCH);
    }

    public function testErroneousBody()
    {
        $this->result->method('getProcessor')->willReturn('aroundToBeforePlugin');
        $this->expectException(LocalizedException::class);
        $this->curl
            ->method('post')
            ->willReturn('{"id":1}');
        $this->curl
            ->method('getStatus')
            ->willReturn(200);
        $this->curl
            ->method('getBody')
            ->willReturn('error');
        $this->prManager->sendPrRequest(123, '/path/to/file.php', PrManager::PATCH_TYPE_PATCH);
    }

    public function testBodyPreparerFactory()
    {
        $this->bodyPreparerFactory
            ->method('create')
            ->willThrowException(new InvalidPrTypeException(__('Invalid type')));
        $this->result->method('getProcessor')->willReturn('aroundToBeforePlugin');
        $this->result->expects($this->once())->method('setPrEnabled')->with(false);
        $this->resultRepository->method('save');
        $this->prManager->sendPrRequest(123, '/path/to/file.php', PrManager::PATCH_TYPE_PATCH);
    }

    public function testValidatePrRequest()
    {
        $valid = $this->prManager->validatePrRequest(123, '/path/to/file.php', PrManager::PATCH_TYPE_PATCH);
        $this->assertIsArray($valid);
    }

    public function testGetRemoteDiff()
    {
        $this->resultRepository->expects($this->once())->method('getByQueueId')->with(123)->willReturn($this->result);
        $this->curl
            ->method('getStatus')
            ->willReturn(200);
        $this->curl
            ->method('getBody')
            ->willReturn('diff');
        $this->result->expects($this->once())->method('setDiff');
        $this->prManager->getRemoteDiff(123);
    }

    public function testGetRemoteDiffBadStatus()
    {
        $this->resultRepository->expects($this->once())->method('getByQueueId')->with(123)->willReturn($this->result);
        $this->curl
            ->method('getStatus')
            ->willReturn(400);
        $this->expectException(LocalizedException::class);
        $this->prManager->getRemoteDiff(123);
    }

    public function testGetRemoteDiffBadBody()
    {
        $this->resultRepository->expects($this->once())->method('getByQueueId')->with(123)->willReturn($this->result);
        $this->curl
            ->method('getStatus')
            ->willReturn(200);
        $this->curl
            ->method('getBody')
            ->willReturn('error');
        $this->expectException(LocalizedException::class);
        $this->prManager->getRemoteDiff(123);
    }

    public function testGetRemoteDiffBadJson()
    {
        $this->resultRepository->expects($this->once())->method('getByQueueId')->with(123)->willReturn($this->result);
        $this->curl
            ->method('getStatus')
            ->willReturn(200);
        $this->curl
            ->method('getBody')
            ->willReturn('invalid');
        $this->expectException(LocalizedException::class);
        $this->prManager->getRemoteDiff(123);
    }
}