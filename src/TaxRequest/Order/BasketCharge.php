<?php
    namespace AccurateTax\TaxRequest\Order;

    class BasketCharge {
        private $type;
        private $name;
        private $price = 0.00;
        private $when = null;

        /**
         * Create a new Basket Charge
         *
         * @param string $name
         * @param float|double $price
         * @param string $when
         * @param string $type
         */
        public function __construct($name, $price=0.00, $when=null, $type='99999') {
            $this->type = $type;
            $this->name = $name;
            $this->price = $price;
            $this->when = $when;
        }

        /**
         * Get the XML for the Basket Charge
         *
         * @return string
         */
        public function getXml() {
            $xml = '<charge>';
            $xml .= '<type>' . $this->type . '</type>';
            $xml .= '<name>' . $this->name . '</name>';
            $xml .= '<price>' . $this->price . '</price>';
            if (!is_null($this->when)) {
                $xml .= '<when>' . $this->when . '</when>';
            }
            $xml .= '</charge>';

            return $xml;
        }
    }