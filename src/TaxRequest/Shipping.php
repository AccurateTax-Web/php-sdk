<?php
    namespace AccurateTax\TaxRequest;
    use AccurateTax\TaxRequest\Address;

    class Shipping {
        private $address;

        /**
         * Create a new Shipping Address
         *
         * @param string $address1
         * @param string $address2
         * @param string $city
         * @param string $state
         * @param string $zip
         * @param string $plus4
         */
        public function __construct(string $address1, string $address2, string $city, string $state, string $zip, string $plus4) {
            $this->address = new Address($address1, $address2, $city, $state, $zip, $plus4);
        }

        public function getAddress(): Address {
            return $this->address;
        }

        /**
         * Get the XML for the Shipping Address
         *
         * @return string
         */
        public function getXml(): string {
            return $this->address->getXml('shipto');
        }
    }