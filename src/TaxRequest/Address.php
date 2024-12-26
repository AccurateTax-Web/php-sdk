<?php
    namespace AccurateTax\TaxRequest;

    class Address {
        private $address1;
        private $address2;
        private $city;
        private $state;
        private $zip;
        private $plus4;

        /**
         * Create a new Address
         *
         * @param string $address1
         * @param string $address2
         * @param string $city
         * @param string $state
         * @param string $zip
         * @param string $plus4
         */
        public function __construct($address1, $address2, $city, $state, $zip, $plus4='') {
            $this->address1 = $address1;
            $this->address2 = $address2;
            $this->city = $city;
            $this->state = $state;
            $this->zip = $zip;
            $this->plus4 = $plus4;
        }

        /**
         * Get the State
         *
         * @return string
         */
        public function getState() {
            return $this->state;
        }

        /**
         * get the XML for the Address
         *
         * @return string
         */
        public function getXml($wrapper) {
            $xml = '<' . $wrapper . '>';
            $xml .= '<address_line1><![CDATA[' . $this->address1 .']]></address_line1>';
            $xml .= '<address_line2>' . (!empty($this->address2) ? '<![CDATA[' : '' ) . $this->address2 . (!empty($this->address2) ? ']]>' : '' ) .'</address_line2>';
            $xml .= '<city>' . $this->city .'</city>';
            $xml .= '<state>' . $this->state .'</state>';
            $xml .= '<zip>' . $this->zip .'</zip>';
            if (!empty($this->plus4) && !is_null($this->plus4)) {
                $xml .= '<plus4>' . $this->plus4 .'</plus4>';
            }
            $xml .= '</' . $wrapper . '>';

            return $xml;
        }
    }