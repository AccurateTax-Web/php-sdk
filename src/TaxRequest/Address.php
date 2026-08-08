<?php
namespace AccurateTax\TaxRequest;

class Address
{
    private $address1;
    private $address2;
    private $city;
    private $state;
    private $zip;
    private $plus4;
    private $country;

    /**
     * Create a new Address
     *
     * @param string $address1
     * @param string $address2
     * @param string $city
     * @param string $state
     * @param string $zip
     * @param string $plus4
     * @param string $country
     */
    public function __construct($address1, $address2, $city, $state, $zip, $plus4 = '', $country = 'US')
    {
        $this->address1 = $address1;
        $this->address2 = $address2;
        $this->city     = $city;
        $this->state    = $state;
        $this->zip      = $zip;
        $this->plus4    = $plus4;

        if (strtoupper($country) == 'USA' || strtoupper($country) == 'US' || strtoupper($country) == 'UNITED STATES') {
            $country = 'US';
        }
        if (strtoupper($country) == 'CA' || strtoupper($country) == 'CAN' || strtoupper($country) == 'CANADA') {
            $country = 'CA';
        }
        if (! in_array(strtoupper($country), ['US', 'CA'])) {
            throw new \Exception('Invalid country. Only US and CA are accepted.');
        }

        $this->country = $country;
    }

    /**
     * Get the State
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Get the Country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * get the XML for the Address
     *
     * @return string
     */
    public function getXml($wrapper)
    {
        $xml  = '<' . $wrapper . '>';
        $xml .= '<address_line1><![CDATA[' . $this->address1 . ']]></address_line1>';
        $xml .= '<address_line2>' . (! empty($this->address2) ? '<![CDATA[' : '') . $this->address2 . (! empty($this->address2) ? ']]>' : '') . '</address_line2>';
        $xml .= '<city>' . $this->city . '</city>';
        $xml .= '<state>' . $this->state . '</state>';
        if ($this->country == 'US' && $wrapper == 'shipto') {
            $xml .= '<zip>' . $this->zip . '</zip>';
            if (! empty($this->plus4) && ! is_null($this->plus4)) {
                $xml .= '<plus4>' . $this->plus4 . '</plus4>';
            }
        } else if ($this->country == 'CA' && $wrapper == 'shipto') {
            $xml .= '<postal_code>' . $this->zip . '</postal_code>';
            $xml .= '<country>' . $this->country . '</country>';
        }
        $xml .= '</' . $wrapper . '>';

        return $xml;
    }

}