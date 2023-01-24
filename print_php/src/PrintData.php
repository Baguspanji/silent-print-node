<?php

require_once 'vendor/autoload.php';
require_once 'helpers/ReceiptPrinter/ReceiptPrinter.php';

class PrintData
{
    public $dotenv;

    public function __construct()
    {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();
    }

    public function get_data()
    {
        $response = array(
            'status' => true,
            'message' => 'Success Get Data.',
        );

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function post_data()
    {
        $request = json_decode(file_get_contents('php://input'), true);

        $this->print($request);

        $response = array(
            'status' => true,
            'message' => 'Success Post Data.',
        );

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    function print($request) {
        $store_name = $request['store_name'];
        $store_address = $request['store_address'];
        $store_phone = $request['store_phone'];
        $store_email = $request['store_email'];
        $store_website = $request['store_website'];
        $buyer_name = $request['buyer_name'];
        $currency = '';
        $tax_percentage = $request['tax_percentage'];
        $image_path = $request['image_path'];
        $amount_paid = $request['amount_paid'];
        $total_price = $request['total_price'];
        $items = $request['items'];

        // Init printer
        $printer = new ReceiptPrinter;
        $printer->init(
            $_ENV['PRINTER_CONNECTOR_TYPE'],
            $_ENV['PRINTER_CONNECTOR_DESCRIPTOR'],
        );

        // Set store info
        $printer->setStore($store_name, $store_address, $store_phone, $store_email, $store_website);
        $printer->setBuyer($buyer_name);
        $printer->setCurrency($currency);
        $printer->setRequestAmount($total_price);
        $printer->setAmountPaid($amount_paid);
        $printer->setTax($tax_percentage);

        // Add items
        foreach ($items as $detail) {
            $explode = explode('/', $detail['unit']);

            $per_unit = $explode[0];
            $unit = $explode[1];

            $printer->addItem(
                $detail['name'],
                $detail['quantity'],
                $detail['price'],
                ((int) $detail['quantity'] * (int) $per_unit) . ' ' . $unit,
            );
        }

        $printer->calculateGrandTotal();

        // Print receipt
        $printer->printReceipt();
    }
}
