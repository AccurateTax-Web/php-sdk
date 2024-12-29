<?php
namespace AccurateTax;

class TaxClassAPI {
    public function checkForStoreTaxClass($storeId, $taxClass)
    {
        $url = 'https://' . getenv('AT_CLUSTER') . '.accuratetax.com' . '/checkStoreTaxClass.php';
        $resp = $this->sendToHost($url, [
            'storeId'  => $storeId,
            'taxClass' => $taxClass,
        ]);
        if ($resp === false) {
            return false;
        } else {
            return $resp['result'];
        }
    }

    public function notifyMissingTaxClass($storeId, array $taxClasses, array $additionalParams = [])
    {
        $url = 'https://' . getenv('AT_CLUSTER') . '.accuratetax.com' . '/notifyMissingTaxClass.php';

        $resp = $this->sendToHost($url, [
            'storeId'  => $storeId,
            'taxClasses' => $taxClasses,
            'additionalParams' => $additionalParams,
        ]);
        if ($resp === false) {
            return false;
        } else {
            return true;
        }
    }

    private function sendToHost($url, $data)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_URL, $url );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $headers = [
            'Content-Type: application/json',
            'X-AT: 1'
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec( $ch );
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode == '404') {
            return false;
        } else {
            $response = trim( $response );
            if (empty($response)) {
                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if ($httpcode == 200 || $httpcode == 202) {
                    return true;
                } else {
                    return false;
                }
            } else {
                $json = json_decode($response, true);

                return $json;
            }
        }
    }

}