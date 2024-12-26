<?php
    namespace AccurateTax\TaxRequest\Order;

    class Shipping {
        public $price = 0.00;
        private $taxClass = null;
        private $matrixSku = '11010';

        public function __construct($price, $taxClass=null, $matrixSku=null) {
            $this->price = $price;
            $this->taxClass = $taxClass;
            $this->matrixSku = $matrixSku;
        }

        public function getXml() {
            $xml = '<shipping>';
            $xml .= '<price>' . $this->price . '</price>';
            $xml .= '<includes_handling>N</includes_handling>';
            $xml .= '<taxType>';
            if (!is_null($this->taxClass) && !empty($this->taxClass)) {
                $xml .= '<tax_class>' . $this->taxClass .'</tax_class>';
            } else {
                $xml .= '<matrix_sku>' . $this->matrixSku .'</matrix_sku>';
            }
            $xml .= '</taxType>';
            $xml .= '</shipping>';

            return $xml;
        }
    }