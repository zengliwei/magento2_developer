<?php
/**
 * Copyright (c) Zengliwei. All rights reserved.
 * Each source file in this distribution is licensed under OSL 3.0, see LICENSE for details.
 */

namespace CrazyCat\Developer\Controller\Index;

use CrazyCat\Aftership\Model\WebhookEventFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * @author  Zengliwei <zengliwei@163.com>
 * @url https://github.com/zengliwei/magento2_aftership
 */
class Csrf implements HttpPostActionInterface, CsrfAwareActionInterface
{
    /**
     * @var HttpRequest
     */
    private $request;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @param RequestInterface $request
     * @param ResultFactory    $resultFactory
     */
    public function __construct(
        RequestInterface $request,
        ResultFactory $resultFactory
    ) {
        $this->request = $request;
        $this->resultFactory = $resultFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $requestData = json_decode($this->request->getContent(), true);

        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        return $result->setData($requestData);
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(
        RequestInterface $request
    ): ?bool {
        return true;
    }
}
