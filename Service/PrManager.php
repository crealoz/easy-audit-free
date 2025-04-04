<?php
/**
 * EasyAudit Premium - Magento 2 Audit Extension
 *
 * Copyright (c) 2025 Crealoz. All rights reserved.
 * Licensed under the EasyAudit Premium EULA.
 *
 * This software is provided under a paid license and may not be redistributed,
 * modified, or reverse-engineered without explicit permission.
 * See EULA for details: https://crealoz.fr/easyaudit-eula
 */

namespace Crealoz\EasyAudit\Service;

use Crealoz\EasyAudit\Api\ResultRepositoryInterface;
use Crealoz\EasyAudit\Exception\InvalidPrTypeException;
use Crealoz\EasyAudit\Service\Config\MiddlewareHost;
use Crealoz\EasyAudit\Service\PrManager\BodyPreparerFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;

class PrManager
{
    const PATCH_TYPE_PATCH = 1;
    const PATCH_TYPE_GIT = 2;

    private array $patchTypes = [
        self::PATCH_TYPE_PATCH => 'Patch',
        self::PATCH_TYPE_GIT => 'Git',
    ];

    private array $prEnabled = [
        'aroundToBeforePlugin' => true,
        'aroundToAfterPlugin' => true,
        'noProxyUsedForHeavyClasses' => true,
        'noProxyUsedInCommands' => true,
    ];

    public function __construct(
        private readonly Curl                      $curl,
        private readonly ResultRepositoryInterface $resultRepository,
        private readonly Json                      $json,
        private readonly BodyPreparerFactory       $bodyPreparerFactory,
        private readonly MiddlewareHost            $middlewareHost
    ) {
    }

    /**
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    public function sendPrRequest(int $resultId, string $relativePath, int $patchType): array
    {
        $result = $this->resultRepository->getById($resultId);

        if (!$this->isPrEnabled($result->getProcessor())) {
            throw new LocalizedException(__('Pull request is not enabled for that type of files. No credits were used.'));
        }

        if (!isset($this->patchTypes[$patchType])) {
            throw new InvalidPrTypeException(__('Invalid patch type'));
        }

        try {
            $bodyPreparer = $this->bodyPreparerFactory->create($result->getProcessor());
        } catch (InvalidPrTypeException $e) {
            $result->setPrEnabled(false);
            $this->resultRepository->save($result);
            return ['error' => true, 'messages' => __('Pull request is not enabled for that type of files. No credits were used.')];
        }

        $body = $bodyPreparer->prepare($result, $this->patchTypes[$patchType], $relativePath);

        $body = $this->json->serialize($body);

        $hash = $this->middlewareHost->getHash();
        $key = $this->middlewareHost->getKey();
        $this->curl->addHeader('Content-Type', 'application/json');
        $this->curl->addHeader('Authorization', 'Bearer ' . $key . ':' . $hash);
        $this->curl->setOption(CURLOPT_PORT, 8443);
        if ($this->middlewareHost->isSelfSigned()) {
            $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
        }

        $this->curl->post($this->getRequestEndpoint(), $body);
        // Check if the request was successful
        if ($this->curl->getStatus() !== 200) {
            $status =  match ($this->curl->getStatus()) {
                400 => 'Bad request',
                401 => 'Unauthorized',
                403 => 'Forbidden',
                404 => 'Not found',
                422 => 'Patch type is invalid',
                428 => 'Not enough credits',
                500 => 'Internal server error',
                default => 'Unknown error',
            };
            throw new LocalizedException(__('An error occurred while sending the PR request: %1', $status));
        }

        try {
            $response = $this->json->unserialize($this->curl->getBody());
        } catch (\InvalidArgumentException $e) {
            throw new LocalizedException(__('An error occurred while sending the PR request'));
        }
        if ($response['status'] !== 'success') {
            throw new LocalizedException(__($response['message']));
        }
        if (!isset($response['queue_id'])) {
            throw new LocalizedException(__('Queue ID is missing in the response'));
        }
        $queueId = $response['queue_id'];
        $result->setQueueId($queueId);
        $this->resultRepository->save($result);
        return $response;
    }

    public function validatePrRequest($resultId, $path, $patchType)
    {
        return ['error' => false, 'messages' => ''];
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getRemoteDiff($diffId)
    {
        $result = $this->resultRepository->getByQueueId($diffId);

        $hash = $this->middlewareHost->getHash();
        $key = $this->middlewareHost->getKey();
        $this->curl->addHeader('Authorization', 'Bearer ' . $key . ':' . $hash);
        $this->curl->setOption(CURLOPT_PORT, 8443);
        if ($this->middlewareHost->isSelfSigned()) {
            $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
        }

        $this->curl->post($this->getDiffEndpoint(), $this->json->serialize(['queue_id' => $diffId]));
        if ($this->curl->getStatus() !== 200) {
            $error = match ($this->curl->getStatus()) {
                400 => 'Bad request',
                401 => 'Unauthorized',
                403 => 'Forbidden',
                404 => 'Not found',
                424 => 'Diff too complicated',
                425 => 'Diff not ready',
                500 => 'Internal server error',
                default => 'Unknown error',
            };
            throw new LocalizedException(__('An error occurred while getting the diff. %1', $error));
        }
        try {
            $response = $this->json->unserialize($this->curl->getBody());
        } catch (\InvalidArgumentException $e) {
            throw new LocalizedException(__('An error occurred while getting the diff'));
        }
        if ($response['status'] !== 'success') {
            throw new LocalizedException(__($response['message']));
        }
        $unserialized = $this->json->unserialize($response['diff']);
        $result->setDiff($unserialized['diff']);
        $result->setQueueId('');
        $this->resultRepository->save($result);
        return $unserialized['diff'];
    }

    /**
     * If an endpoint is defined in the environment, it will be used instead of the default one. It is useful for testing.
     * @return string
     */
    protected function getRequestEndpoint(): string
    {
        return $this->middlewareHost->getHost() . '/api/request-pr';
    }

    protected function getDiffEndpoint(): string
    {
        return $this->middlewareHost->getHost() . '/api/get-created-diff';
    }

    public function isPrEnabled(string $processor): bool
    {
        return isset($this->prEnabled[$processor]);
    }
}