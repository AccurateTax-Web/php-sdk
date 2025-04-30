<?php
    namespace AccurateTax\TaxRequest;
    use AccurateTax\TaxRequest\Order\Shipping;
    use AccurateTax\TaxRequest\Order\Handling;

    class Order {
        /**
         * @var string Order Number
         */
        private $ordernum;

        /**
         * @var string Order Create Date
         */
        private $createDateTime;

        /**
         * @var array[Order\Item] Items in the Order
         */
        private $items = array();

        /**
         * @var array[Order\BasketCharge]   Charges in the Order (discounts, etc)
         */
        private $charges = array();

        /**
         * @var Order\Shipping Shipping Information
         */
        private $shipping;

        /**
         * @var Order\Handling Handling Information
         */
        private $handling;

        /**
         * @var string Customer ID
         */
        private $customerId;

        /**
         * @var Address Ship To Address
         */
        private $shipTo;

        /**
         * @var float|double Order Subtotal
         */
        private $subTotal;

        /**
         * @var float|double External Tax Collected
         */
        private $externalTaxCollected = 0.00;

        /**
         * Create an Order for Tax Calculation
         *
         * @param string $ordernum
         * @param mixed $createDateTime
         * @param string $customerId
         */
        public function __construct(string $ordernum,  $createDateTime, string $customerId='') {
            $this->ordernum = $ordernum;
            $this->createDateTime = $createDateTime;
            $this->customerId = $customerId;
        }

        /**
         * Get the Order Number
         *
         * @return string
         */
        public function getOrdernum():string {
            return $this->ordernum;
        }

        /**
         * Set the Subtotal of the Order
         *
         * @param float|double $subTotal
         *
         * @return void
         */
        public function setSubTotal($subTotal):void {
            $this->subTotal = $subTotal;
        }

        /**
         * Set External Tax Collected Amount
         *
         * @param float|double $amount
         *
         * @return void
         */
        public function setExternalTaxCollected($amount):void {
            $this->externalTaxCollected = $amount;
        }

        /**
         * Add Ship To Address
         *
         * @param \AccurateTax\TaxRequest\Address $address
         *
         * @return void
         */
        public function addShipTo(\AccurateTax\TaxRequest\Address $address):void {
            $this->shipTo = $address;
        }

        /**
         * Add Item to Order
         *
         * @param \AccurateTax\TaxRequest\Order\Item $item
         *
         * @return void
         */
        public function addItem(\AccurateTax\TaxRequest\Order\Item $item): void {
            array_push($this->items, $item);
        }

        /**
         * Add Shipping to the Order
         *
         * @param \AccurateTax\TaxRequest\Order\Shipping $shipping
         *
         * @return void
         */
        public function addShipping(Shipping $shipping): void {
            $this->shipping = $shipping;
        }

        /**
         * Returns Shipping Object
         * @return Shipping
         */
        public function getShipping(): ?Shipping {
            return $this->shipping;
        }

        /**
         * Add Handling to the Order
         *
         * @param Handling $handling
         *
         * @return void
         */
        public function addHandling(Handling $handling): void {
            $this->handling = $handling;
        }

        /**
         * Add Charge to the Order
         *
         * @param \AccurateTax\TaxRequest\Order\BasketCharge $charge
         *
         * @return void
         */
        public function addCharge(\AccurateTax\TaxRequest\Order\BasketCharge $charge): void {
            array_push($this->charges, $charge);
        }

        /**
         * Get the State of the Ship To Address
         *
         * @return string
         */
        public function getState(): string {
            return $this->shipTo->getState();
        }

        /**
         * Get the XML for the Order
         *
         * @return string
         */
        public function getXml(): string {
            $xml = '<order>';
            $xml .= '<ordernum>' . $this->ordernum . '</ordernum>';
            $xml .= '<create_date>' . date('c', strtotime($this->createDateTime)) . '</create_date>';
            if (!empty($this->customerId)) {
                $xml .= '<customer_id>' . $this->customerId .'</customer_id>';
            }
            $xml .= '<subtotal>' . $this->subTotal . '</subtotal>';
            if ($this->externalTaxCollected > 0) {
                $xml .= '<external_tax_collected>' . $this->externalTaxCollected . '</external_tax_collected>';
            }
            $xml .= $this->shipTo->getXml('shipto');
            $xml .= '<items>';
            $subtotal = 0.00;
            foreach($this->items as $item) {
                $xml .= $item->getXml();
                $subtotal += $item->getLineTotal();
            }
            $xml .= '</items>';
            if (count($this->charges) > 0) {
                $xml .= '<basketCharges>';
                foreach($this->charges as $charge) {
                    $xml .= $charge->getXml();
                }
                $xml .= '</basketCharges>';
            }
            if (isset($this->shipping)) {
                $xml .= $this->shipping->getXml();
            } else {
                $xml .= '<shipping><price>0.00</price><includes_handling>N</includes_handling><taxType><matrix_sku></matrix_sku></taxType></shipping>';
            }
            if (isset($this->handling) && $this->handling->price !== 0.00) {
                $xml .= $this->handling->getXml();
            }
            $xml .= '</order>';

            return $xml;
        }
    }
