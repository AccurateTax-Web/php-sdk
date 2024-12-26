<?php
    namespace AccurateTax\TaxRequest\Order;
    use AccurateTax\TaxRequest\Order\Item\Option;

    class Item {
        private $lineno;
        private $sku;
        private $price = 0.00;
        private $qty = 1;
        private $taxClass;
        private $matrixSku;
        private $lineTotal = 0.00;
        private $options = [];
        private $name;

        /**
         * Create a new Item
         *
         * @param int $lineno
         * @param string $sku
         * @param float|double $price
         * @param int|float|double $qty
         * @param string $taxClass
         * @param string $matrixSku
         * @param string $name
         */
        public function __construct($lineno, $sku, $price, $qty, $taxClass = null, $matrixSku = null, $name = null) {
            $this->lineno = $lineno;
            $this->sku = $sku;
            $this->price = $price;
            $this->lineTotal = (float)$price * (float)$qty;
            $this->qty = $qty;
            $this->taxClass = $taxClass;
            $this->matrixSku = $matrixSku;
            $this->name = $name;
        }

        /**
         * Add an Option to the Item
         *
         * @param Option $option
         */
        public function addOption(Option $option): void {
            array_push($this->options, $option);
        }

        /**
         * Get the Line Total
         *
         * @return float
         */
        public function getLineTotal(): float {
            return $this->lineTotal;
        }

        /**
         * Get the XML for the Item
         *
         * @return string
         */
        public function getXml() {
            $xml = '<item>';
            $xml .= '<sku><![CDATA[' . $this->sku . ']]></sku>';
            if (!is_null($this->name) && !empty($this->name)) {
                $xml .= '<name><![CDATA[' . substr($this->name, 0, 150) . ']]></name>';
            }
            $xml .= '<lineno>' . $this->lineno . '</lineno>';
            $xml .= '<taxType>';
            if (!is_null($this->taxClass) && !empty($this->taxClass)) {
                $xml .= '<tax_class><![CDATA[' . $this->taxClass .']]></tax_class>';
            } else {
                $xml .= '<matrix_sku>' . $this->matrixSku .'</matrix_sku>';
            }
            $xml .= '</taxType>';
            $xml .= '<price>' . (float)$this->price . '</price>';
            $xml .= '<qty>' . (string)$this->qty . '</qty>';
            $xml .= '<line_total>' . $this->lineTotal . '</line_total>';
            if (count($this->options) > 0) {
                $xml .= '<options>';
                foreach($this->options as $option) {
                    $xml .= $option->getXml();
                }
                $xml .= '</options>';
            }
            $xml .= '</item>';
            return $xml;
        }
    }