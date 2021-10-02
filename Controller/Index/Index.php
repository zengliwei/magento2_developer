<?php
/**
 * Copyright (c) Zengliwei. All rights reserved.
 * Each source file in this distribution is licensed under OSL 3.0, see LICENSE for details.
 */

namespace CrazyCat\Developer\Controller\Index;

use CrazyCat\Aftership\Model\WebhookEventFactory;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * @author  Zengliwei <zengliwei@163.com>
 * @url https://github.com/zengliwei/magento2_aftership
 */
class Index implements HttpGetActionInterface
{
    /**
     * @var RequestInterface
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
        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
}
