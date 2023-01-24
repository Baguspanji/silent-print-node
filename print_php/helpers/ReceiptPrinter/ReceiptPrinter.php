<?php

require_once 'vendor/autoload.php';
require_once 'helpers/ReceiptPrinter/Item.php';
require_once 'helpers/ReceiptPrinter/Store.php';
require_once 'helpers/Helper.php';

use Mike42\Escpos\CapabilityProfile;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

class ReceiptPrinter
{
    private $printer;
    private $logo;
    private $store;
    private $items;
    private $currency = 'Rp';
    private $subtotal = 0;
    private $tax_percentage = 10;
    private $tax = 0;
    private $grandtotal = 0;
    private $request_amount = 0;
    private $amount_paid = 0;
    private $qr_code = [];
    private $transaction_id = '';
    private $buyername = '';

    public function __construct()
    {
        $this->printer = null;
        $this->items = [];
    }

    public function init($connector_type, $connector_descriptor, $connector_port = 9100)
    {
        switch (strtolower($connector_type)) {
            case 'cups':
                $connector = new CupsPrintConnector($connector_descriptor);
                break;
            case 'windows':
                $connector = new WindowsPrintConnector($connector_descriptor);
                break;
            case 'network':
                $connector = new NetworkPrintConnector($connector_descriptor);
                break;
            default:
                $connector = new FilePrintConnector("php://stdout");
                break;
        }

        if ($connector) {
            // Load simple printer profile
            $profile = CapabilityProfile::load("default");
            // Connect to printer
            $this->printer = new Printer($connector, $profile);
        } else {
            throw new Exception('Invalid printer connector type. Accepted values are: cups');
        }
    }

    public function close()
    {
        if ($this->printer) {
            $this->printer->close();
        }
    }

    public function setStore($name, $address, $phone, $email, $website)
    {
        $this->store = new Store($name, $address, $phone, $email, $website);
    }

    public function setLogo($logo)
    {
        $this->logo = $logo;
    }

    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    public function setBuyer($buyer)
    {
        $this->buyername = $buyer;
    }

    public function addItem($name, $qty, $price, $desc)
    {
        $item = new Item($name, $qty, $price, $desc);
        $item->setCurrency($this->currency);

        $this->items[] = $item;
    }

    public function setRequestAmount($amount)
    {
        $this->request_amount = $amount;
    }

    public function setAmountPaid($amount)
    {
        $this->amount_paid = $amount;
    }

    public function setTax($tax)
    {
        $this->tax_percentage = $tax;

        if ($this->subtotal == 0) {
            $this->calculateSubtotal();
        }

        $this->tax = (int) $this->tax_percentage / 100 * (int) $this->subtotal;
    }

    public function calculateSubtotal()
    {
        $this->subtotal = 0;

        foreach ($this->items as $item) {
            $this->subtotal += (int) $item->getQty() * (int) $item->getPrice();
        }
    }

    public function calculateGrandTotal()
    {
        if ($this->subtotal == 0) {
            $this->calculateSubtotal();
        }

        $this->grandtotal = (int) $this->subtotal + (int) $this->tax;
    }

    public function setTransactionID($transaction_id)
    {
        $this->transaction_id = $transaction_id;
    }

    public function setQRcode($content)
    {
        $this->qr_code = $content;
    }

    public function setTextSize($width = 1, $height = 1)
    {
        if ($this->printer) {
            $width = ($width >= 1 && $width <= 8) ? (int) $width : 1;
            $height = ($height >= 1 && $height <= 8) ? (int) $height : 1;
            $this->printer->setTextSize($width, $height);
        }
    }

    public function getPrintableQRcode()
    {
        return json_encode($this->qr_code);
    }

    public function getPrintableHeader($left_text, $right_text, $is_double_width = false)
    {
        $cols_width = $is_double_width ? 8 : 16;

        return str_pad($left_text, $cols_width) . str_pad($right_text, $cols_width, ' ', STR_PAD_LEFT);
    }

    public function getPrintableSummary($label, $value)
    {
        $left_cols = 20;
        $right_cols = 10;

        $formatted_value = $this->currency . number_format($value, 0, ',', '.');

        return str_pad($label, $left_cols) . str_pad($formatted_value, $right_cols, ' ', STR_PAD_LEFT);
    }

    public function feed($feed = null)
    {
        $this->printer->feed($feed);
    }

    public function cut()
    {
        $this->printer->cut();
    }

    public function printDashedLine()
    {
        $line = '';

        for ($i = 0; $i < 32; $i++) {
            $line .= '-';
        }

        $this->printer->text($line);
    }

    public function printLogo()
    {
        if ($this->logo) {
            $image = EscposImage::load($this->logo, false);

            //$this->printer->feed();
            //$this->printer->bitImage($image);
            //$this->printer->feed();
        }
    }

    public function printQRcode()
    {
        if (!empty($this->qr_code)) {
            $this->printer->qrCode($this->getPrintableQRcode(), Printer::QR_ECLEVEL_L, 8);
        }
    }

    public function printReceipt($with_items = true)
    {
        if ($this->printer) {
            // Get total, subtotal, etc
            // $subtotal = $this->getPrintableSummary('Total Harga', $this->subtotal);
            // $tax = $this->getPrintableSummary('Tax', $this->tax);
            $total = $this->getPrintableSummary('Total Harga', $this->grandtotal);
            $amountPaid = $this->getPrintableSummary('Total Bayar', $this->amount_paid);
            $amountBack = $this->getPrintableSummary('Total Kembali', $this->amount_paid - $this->request_amount);
            $footer = "Barang yang sudah dibeli tidak dapat ditukat / dikembalikan.!\n";
            // Init printer settings
            $this->printer->initialize();
            $this->printer->selectPrintMode();
            // Set margins
            $this->printer->setPrintLeftMargin(1);
            // Print receipt headers
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            // Print logo
            $this->printLogo();
            $this->printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $this->printer->feed(2);
            $this->printer->text("{$this->store->getName()}\n");
            $this->printer->selectPrintMode();
            $this->printer->text("{$this->store->getAddress()}\n");
            $this->printer->text("Telp : {$this->store->getPhone()}\n");
            $this->printer->feed();
            $this->printer->text(str_pad('Pembeli :', 20) . str_pad($this->buyername ?? '-', 10, ' ', STR_PAD_LEFT) . "\n");
            $this->printer->setEmphasis(true);
            $this->printer->text("-----------------------------\n");
            $this->printer->setEmphasis(false);
            $this->printer->feed();
            // Print items
            if ($with_items) {
                $this->printer->setJustification(Printer::JUSTIFY_LEFT);
                foreach ($this->items as $item) {
                    $this->printer->text($item);
                }
                $this->printer->feed();
            }
            // Print subtotal
            $this->printer->setEmphasis(true);

            $this->printer->text("-----------------------------\n");
            $this->printer->setEmphasis(false);
            $this->printer->feed();

            $this->printer->selectPrintMode(Printer::JUSTIFY_LEFT);
            $this->printer->text($total);
            $this->printer->feed();
            if ($this->amount_paid != 0) {
                $this->printer->text($amountPaid);
                $this->printer->feed();
                $this->printer->text($amountBack);
                $this->printer->feed();
            }
            $this->printer->selectPrintMode();
            // Print qr code
            $this->printQRcode();
            // Print receipt footer
            $this->printer->feed();
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->text($footer);
            $this->printer->feed();
            // Print receipt date
            $this->printer->text(tanggal(date('Y-m-d H:i:s')));
            $this->printer->feed(2);
            // Cut the receipt
            $this->printer->cut();
            $this->printer->close();
        } else {
            throw new Exception('Printer has not been initialized.');
        }
    }

    public function printRequest()
    {
        if ($this->printer) {
            // Get request amount
            $this->printer->initialize();
            $this->printer->selectPrintMode();
            // Set margins
            $this->printer->setPrintLeftMargin(1);
            // Print receipt headers
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            // Print logo
            $this->printer->text(str_pad('Pembeli :', 10) . str_pad($this->buyername, 20, ' ', STR_PAD_LEFT) . "\n");
            $this->printer->setEmphasis(true);
            $this->printer->text("-----------------------------\n");
            $this->printer->setEmphasis(false);
            $this->printer->feed();
            // Cut the receipt
            $this->printer->cut();
            $this->printer->close();
        } else {
            throw new Exception('Printer has not been initialized.');
        }
    }
}
