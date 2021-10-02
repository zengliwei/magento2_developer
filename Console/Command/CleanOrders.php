<?php
/**
 * Copyright (c) Zengliwei. All rights reserved.
 * Each source file in this distribution is licensed under OSL 3.0, see LICENSE for details.
 */

namespace CrazyCat\Developer\Console\Command;

use Exception;
use Magento\Framework\App\ResourceConnection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author  Zengliwei <zengliwei@163.com>
 * @url https://github.com/zengliwei/magento2_developer
 */
class CleanOrders extends Command
{
    private const OPT_CUSTOMER_ID = 'customer_id';

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @param string|null $name
     */
    public function __construct(
        ResourceConnection $resource,
        string $name = null
    ) {
        $this->resource = $resource;
        parent::__construct($name);
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('dev:clean-orders');
        $this->setDescription('Clean orders');
        $this->setDefinition(
            [
                new InputOption(
                    self::OPT_CUSTOMER_ID,
                    'c',
                    InputOption::VALUE_OPTIONAL,
                    'Customer ID, separated by comma'
                )
            ]
        );
    }

    /**
     * Clean all orders
     */
    private function cleanAll()
    {
        $conn = $this->resource->getConnection('sales');

        $conn->delete($conn->getTableName('sales_invoice'));
        $conn->delete($conn->getTableName('sales_invoice_comment'));
        $conn->delete($conn->getTableName('sales_invoice_grid'));
        $conn->delete($conn->getTableName('sales_invoice_item'));

        $conn->delete($conn->getTableName('sales_shipment'));
        $conn->delete($conn->getTableName('sales_shipment_comment'));
        $conn->delete($conn->getTableName('sales_shipment_grid'));
        $conn->delete($conn->getTableName('sales_shipment_item'));
        $conn->delete($conn->getTableName('sales_shipment_track'));

        $conn->delete($conn->getTableName('sales_creditmemo'));
        $conn->delete($conn->getTableName('sales_creditmemo_comment'));
        $conn->delete($conn->getTableName('sales_creditmemo_grid'));
        $conn->delete($conn->getTableName('sales_creditmemo_item'));

        $conn->delete($conn->getTableName('sales_payment_transaction'));

        $conn->delete($conn->getTableName('sales_order'));
        $conn->delete($conn->getTableName('sales_order_address'));
        $conn->delete($conn->getTableName('sales_order_grid'));
        $conn->delete($conn->getTableName('sales_order_item'));
        $conn->delete($conn->getTableName('sales_order_payment'));
        $conn->delete($conn->getTableName('sales_order_status_history'));
        $conn->delete($conn->getTableName('sales_order_tax'));
        $conn->delete($conn->getTableName('sales_order_tax_item'));
    }

    /**
     * Clean orders by given customer IDs
     */
    private function cleanByCustomer($customerIds)
    {
        $conn = $this->resource->getConnection('sales');

        $orderIds = $conn->fetchCol(
            $conn->select()
                ->from($conn->getTableName('sales_order'), ['entity_id'])
                ->where('customer_id IN (?)', $customerIds)
        );
        if (empty($orderIds)) {
            return;
        }

        $invoiceIds = $conn->fetchCol(
            $conn->select()
                ->from($conn->getTableName('sales_invoice'), ['entity_id'])
                ->where('order_id IN (?)', $orderIds)
        );
        if (!empty($invoiceIds)) {
            $conn->delete($conn->getTableName('sales_invoice'), ['entity_id IN (?)' => $invoiceIds]);
            $conn->delete($conn->getTableName('sales_invoice_comment'), ['parent_id IN (?)' => $invoiceIds]);
            $conn->delete($conn->getTableName('sales_invoice_grid'), ['entity_id IN (?)' => $invoiceIds]);
            $conn->delete($conn->getTableName('sales_invoice_item'), ['parent_id IN (?)' => $invoiceIds]);
        }

        $shipmentIds = $conn->fetchCol(
            $conn->select()
                ->from($conn->getTableName('sales_shipment'), ['entity_id'])
                ->where('order_id IN (?)', $orderIds)
        );
        if (!empty($shipmentIds)) {
            $conn->delete($conn->getTableName('sales_shipment'), ['entity_id IN (?)' => $shipmentIds]);
            $conn->delete($conn->getTableName('sales_shipment_comment'), ['parent_id IN (?)' => $shipmentIds]);
            $conn->delete($conn->getTableName('sales_shipment_grid'), ['entity_id IN (?)' => $shipmentIds]);
            $conn->delete($conn->getTableName('sales_shipment_item'), ['parent_id IN (?)' => $shipmentIds]);
            $conn->delete($conn->getTableName('sales_shipment_track'), ['parent_id IN (?)' => $shipmentIds]);
        }

        $creditMemoIds = $conn->fetchCol(
            $conn->select()
                ->from($conn->getTableName('sales_creditmemo'), ['entity_id'])
                ->where('order_id IN (?)', $orderIds)
        );
        if (!empty($creditMemoIds)) {
            $conn->delete($conn->getTableName('sales_creditmemo'), ['entity_id IN (?)' => $creditMemoIds]);
            $conn->delete($conn->getTableName('sales_creditmemo_comment'), ['parent_id IN (?)' => $creditMemoIds]);
            $conn->delete($conn->getTableName('sales_creditmemo_grid'), ['entity_id IN (?)' => $creditMemoIds]);
            $conn->delete($conn->getTableName('sales_creditmemo_item'), ['parent_id IN (?)' => $creditMemoIds]);
        }

        $conn->delete($conn->getTableName('sales_payment_transaction'), ['order_id IN (?)' => $orderIds]);

        $conn->delete($conn->getTableName('sales_order'), ['entity_id IN (?)' => $orderIds]);
        $conn->delete($conn->getTableName('sales_order_address'), ['parent_id IN (?)' => $orderIds]);
        $conn->delete($conn->getTableName('sales_order_grid'), ['entity_id IN (?)' => $orderIds]);
        $conn->delete($conn->getTableName('sales_order_item'), ['order_id IN (?)' => $orderIds]);
        $conn->delete($conn->getTableName('sales_order_payment'), ['parent_id IN (?)' => $orderIds]);
        $conn->delete($conn->getTableName('sales_order_status_history'), ['parent_id IN (?)' => $orderIds]);

        $orderTaxIds = $conn->fetchCol(
            $conn->select()
                ->from($conn->getTableName('sales_order_tax'), ['tax_id'])
                ->where('order_id IN (?)', $orderIds)
        );
        if (!empty($orderTaxIds)) {
            $conn->delete($conn->getTableName('sales_order_tax'), ['tax_id IN (?)' => $orderTaxIds]);
            $conn->delete($conn->getTableName('sales_order_tax_item'), ['tax_id IN (?)' => $orderTaxIds]);
        }
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            if (($customerId = $input->getOption(self::OPT_CUSTOMER_ID))) {
                $this->cleanByCustomer(explode(',', $customerId));
            } else {
                $this->cleanAll();
            }
        } catch (Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }
}
