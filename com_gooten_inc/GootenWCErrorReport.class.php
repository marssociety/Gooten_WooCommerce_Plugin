<?php
/**
 * Created by Vlado on 19-Sep-16.
 */


/**
 * Encapsulates error reporting functionalities.
 */
class GootenWCErrorReport
{

    const SEPARATOR = '-------------------------';

    /**
     * Posts report to Gooten API.
     *
     * @param string $title The title of the report.
     * @param string $data The report data.
     * @param bool $isTest Flag telling if this report was made for testing purpose.
     * @return bool Returns true if report was successfully posted.
     */
    private static function postReport($title, $data, $isTest)
    {
        $postData = array(
            'app' => 'woocommerce-plugin' . ($isTest ? '-test' : '') . ':',
            'title' => $title,
            'data' => $data
        );
        return GootenWCAPI::postReport(json_encode($postData));
    }

    /**
     * Flattens report data array.
     *
     * @param $dataArr Report data array.
     * @return string Returns flattened data.
     */
    private static function flattenData($dataArr)
    {
        $flattenedData = '';
        foreach ($dataArr as $key => $value) {
            if ($value === self::SEPARATOR) {
                $flattenedData .= PHP_EOL . PHP_EOL . $key . PHP_EOL . self::SEPARATOR;
            } else {
                $v = '';
                if (isset($value)) {
                    if (!is_string($value)) {
                        ob_start();
                        print_r($value);
                        $v = ob_get_clean();
                    } else {
                        $v = $value;
                    }
                    if (strlen($v) === 0) {
                        $v = '<empty>';
                    } else {
                        if (strlen($v) > 50) {
                            $v = PHP_EOL . $v;
                        }
                    }
                } else {
                    $v = '<not set>';
                }
                $flattenedData .= PHP_EOL . $key . ': ' . $v;
            }
        }
        return trim($flattenedData);
    }

    /**
     * Creates data with basic info that every report should have.
     *
     * @return array Holding basic data.
     */
    private static function getBasicInfo()
    {
        return array(
            'Basic Info' => self::SEPARATOR,
            'Gooten Plugin Version' => GootenWCUtils::getPluginVersion(),
            'Site URL' => site_url(),
            'Server time' => date('H:i:s Y-m-d')
        );
    }

    /**
     * Creates report about API issue and posts it to Gooten.
     *
     * @param string $curlErr Error captured by curl.
     * @param string $response The response returned by API (if any).
     * @param string $reqUrl The request URL of API.
     * @param string $reqData The data sent to API (if any).
     * @return bool Returns true if report was successfully posted.
     */
    public static function postAPIIssue($curlErr, $response, $reqUrl, $reqData)
    {
        $data = self::getBasicInfo();
        $data = array_merge($data, array(
            'API Info' => self::SEPARATOR,
            'Curl error' => $curlErr,
            'Request URL' => $reqUrl,
            'Request Data' => $reqData,
            'Response' => $response
        ));

        $title = '[API Issue]';
        if (isset($curlErr)) {
            $title .= '[CurlError][' . $curlErr . ']';
        } else {
            $title .= '[HadError]';
            $arr = json_decode($response, true);
            if (isset($arr['Errors'])) {
                $title .= '[' . md5(serialize($arr['Errors'])) . ']';
            }
        }

        $isTest = strpos($curlErr, 'test') !== false;
        $isTest = $isTest || strpos($reqUrl, 'test') !== false;
        $isTest = $isTest || strpos($response, 'test') !== false;
        return self::postReport($title, self::flattenData($data), $isTest);
    }

    /**
     * Creates report for unhandled exception and posts it to Gooten.
     *
     * @param $e The exception.
     * @return bool Returns true if report was successfully posted.
     */
    public static function postUnhandledException($e)
    {
        if (strpos(strtolower($e->getFile()), 'gooten') !== false) {
            $isTest = strpos(strtolower($e->getFile()), 'GootenWCErrorReportTest') !== false;
            $data = self::getBasicInfo();
            $data = array_merge($data, array(
                'Exception Info' => self::SEPARATOR,
                'Message' => $e->getMessage(),
                'Line' => $e->getFile() . ':' . $e->getLine(),
                'Stack trace' => $e->getTraceAsString()
            ));

            $title = '[CRASH][' . md5($e->getFile() . ':' . $e->getLine() . ':' . $e->getMessage()) . ']';
            return self::postReport($title, self::flattenData($data), $isTest);
        }
    }
}

function gooten_exception_handler($exception)
{
    GootenWCErrorReport::postUnhandledException($exception);
    throw $exception;
}

set_exception_handler('gooten_exception_handler');
