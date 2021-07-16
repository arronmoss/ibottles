<?php
namespace Zero1\AbnValidation\Model;

class AbnValidator
{
    const GUID = '03b1ec54-da4e-48f8-8f0b-6e26cef0b315';

    /**
     * @param string $abn
     * @return []
     */
    protected function searchByABN($abn, $debug = false)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL,"https://abr.business.gov.au/abrxmlsearch/ABRXMLSearch.asmx");
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'SOAPAction: "http://abr.business.gov.au/ABRXMLSearch/ABRSearchByABN"',
            'Content-Type: application/soap+xml; charset="utf-8"'
        ));
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, '<Envelope xmlns="http://www.w3.org/2003/05/soap-envelope">
        <Body>
            <ABRSearchByABN xmlns="http://abr.business.gov.au/ABRXMLSearch/">
                <searchString>'.$abn.'</searchString>
                <includeHistoricalDetails>Y</includeHistoricalDetails>
                <authenticationGuid>'.self::GUID.'</authenticationGuid>
            </ABRSearchByABN>
        </Body>
    </Envelope>');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        if($debug){
            echo 'raw response (1)'.PHP_EOL;
            echo $response.PHP_EOL;
            echo PHP_EOL;
        }
        curl_close ($curl);
        $xml = $response;
        $xml = str_replace('xmlns=', 'ns=', $response);
        $xml = substr($xml, (strpos($xml, '<ABRPayloadSearchResults')));
        $xml = substr($xml, 0, (strpos($xml, '</ABRPayloadSearchResults>')+26));
        if($debug){
            echo 'raw response (2)'.PHP_EOL;
            echo $xml.PHP_EOL;
            echo PHP_EOL;
        }
        $xml = simplexml_load_string($xml);

        return json_decode(json_encode($xml), true);
    }


    public function isValid($abn, $debug = false)
    {
        $response = $this->searchByAbn($abn, $debug);
        if($debug){
            echo 'parsed response'.PHP_EOL;
            print_r($response);
            echo PHP_EOL;
        }

        if(!isset($response['response'])){
            if($debug){
                echo 'unable to find: response'.PHP_EOL;
                echo PHP_EOL;
            }
            return false;
        }

        if(isset($response['response']['exception'])){
            if($debug){
                echo 'found: exception'.PHP_EOL;
                echo 'message: '.json_encode($response['response']['exception']).PHP_EOL;
                echo PHP_EOL;
            }
            return false;
        }

        if(isset(
            $response['response'],
            $response['response']['businessEntity'],
            $response['response']['businessEntity']['goodsAndServicesTax']
        )){
            $goodsAndServicesTax = $response['response']['businessEntity']['goodsAndServicesTax'];
            // single GST Status Value
            if(isset($goodsAndServicesTax['effectiveFrom'])){
                $gstStatuses = [
                    $goodsAndServicesTax
                ];
            }else{
                $gstStatuses = $goodsAndServicesTax;
            }

            $hasGSt = false;
            foreach($gstStatuses as $gstStatus){
                if($debug){
                    echo 'processing: '.json_encode($gstStatus).PHP_EOL;
                }
                $effectiveFrom = strtotime($gstStatus['effectiveFrom']);
                $effectiveTo = strtotime($gstStatus['effectiveTo']);
                $currentTime = time();

                if($currentTime > $effectiveFrom && (
                        $effectiveTo == -62135596800 ||
                        $currentTime < $effectiveTo
                    )){
                    $hasGSt = true;
                    break;
                }
            }

            if($hasGSt){
                return true;
            }else{
                return false;
            }
        }

        if($debug){
            echo 'unable to find: response.businessEntity.goodsAndServicesTax'.PHP_EOL;
            echo PHP_EOL;
        }
        return false;
    }
}
