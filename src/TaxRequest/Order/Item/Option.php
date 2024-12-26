<?php
    namespace AccurateTax\TaxRequest\Order\Item;

    class Option {
        /**
         * Item Option SKU
         * @var string
         */
        private $sku;
        
        /**
         * Item Option Tax Class if no Matrix SKU is provided
         * @var string
         */
        private $taxClass = null;

        /**
         * Item Option Matrix Sku if no Tax Class is provided
         * @var string
         */
        private $matrixSku = null;

        /**
         * Item Option Matrix Override
         * @var string
         */
        private $matrixOverride = null;
        private $taxable = 'Y';

        /**
         * Item Option Name
         * @var string
         */
        private $name = '';

        /**
         * Item Option Price
         * @var float
         */
        private $price = 0.00;

        /**
         * Item Option Quantity
         * @var double|int|float
         */
        private $qty = 1;

        /**
         * Create a new Item Option
         *
         * @param string $sku
         * @param string $name
         * @param float|double $price
         * @param int|float|double $qty
         * @param string $taxClass
         * @param string $matrixSku
         * @param string $matrixOverride
         * @param string $taxable
         */
        public function __construct($sku, $name, $price, $qty, $taxClass=null, $matrixSku=null, $matrixOverride=null, $taxable='Y')
        {
            $this->sku = $sku;
            $this->name = $name;
            $this->price = $price;
            $this->qty = $qty;
            $this->taxClass = $taxClass;
            $this->matrixSku = $matrixSku;
            $this->matrixOverride = $matrixOverride;
            $this->taxable = $taxable;
        }

        /**
         * Returns Item Option XML
         *
         * @return string
         */
        public function getXml() {
            $xml = '<option>';
            $xml .= '<sku>' . $this->sku . '</sku>';
            $xml .= '<taxType>';
            if (!is_null($this->taxClass) && !empty($this->taxClass)) {
                $xml .= '<tax_class>' . $this->taxClass .'</tax_class>';
            } else {
                $xml .= '<matrix_sku>' . $this->matrixSku .'</matrix_sku>';
            }
            $xml .= '</taxType>';
            if (!is_null($this->matrixOverride)) {
                $xml .= '<matrix_override>' . $this->matrixOverride . '</matrix_override>';
            }
            $xml .= '<taxable>' . $this->taxable .'</taxable>';
            $xml .= '<name>' . $this->name . '</name>';
            $xml .= '<price>' . $this->price . '</price>';
            $xml .= '<qty>' . $this->qty . '</qty>';
            $xml .= '</option>';
            return $xml;
        }
    }