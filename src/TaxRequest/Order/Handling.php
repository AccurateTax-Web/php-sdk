<?php
    namespace AccurateTax\TaxRequest\Order;

    class Handling {
        public $price = 0.00;
        private $taxClass = null;
        private $matrixSku = '11000';
        private $name = 'handling';

        /**
         * Create a new Handling Charge
         *
         * @param float|double $price
         * @param string $taxClass
         * @param string $matrixSku
         * @param string $name
         */
        public function __construct($price, $taxClass=null, $matrixSku=null, $name=null) {
            $this->price = $price;
            $this->taxClass = $taxClass;
            $this->matrixSku = $matrixSku;
            if (!is_null($name)) {
                $this->name = $name;
            }
        }

        /**
         * Get the XML for the Handling Charge
         *
         * @return string
         */
        public function getXml() {
            $xml = '<handling>';
            $xml .= '<price>' . $this->price . '</price>';
            $xml .= '<taxType>';
            if (!is_null($this->taxClass) && !empty($this->taxClass)) {
                $xml .= '<tax_class><![CDATA[' . $this->taxClass .']]></tax_class>';
            } else {
                $xml .= '<matrix_sku>' . $this->matrixSku .'</matrix_sku>';
            }
            $xml .= '</taxType>';
            $xml .= '<name><![CDATA[' . $this->name . ']]></name>';
            $xml .= '</handling>';

            return $xml;
        }
    }