<?php
/**
 * Created by Boro on 18-Oct-16.
 */

require_once('BaseTest.class.php');

class GootenWCErrorReportTest extends BaseTest
{

    public function setUp()
    {
        parent::setUp();
        $this->setRecipeId($this->VALID_RECIPE_ID);
    }

    public function testGetBasicInfo()
    {
        $method = $this->getMethodByReflection('GootenWCErrorReport', 'getBasicInfo');
        $response = $method->invokeArgs(null, array());

        $this->assertNotEmpty($response);
    }

    public function testPostReport()
    {
        $method = $this->getMethodByReflection('GootenWCErrorReport', 'postReport');
        $response = $method->invokeArgs(null, array('TestErrorReport', '{}', true));

        $this->assertTrue($response);
    }

    public function testFlattenData()
    {
        $method = $this->getMethodByReflection('GootenWCErrorReport', 'flattenData');

        $result = $method->invokeArgs(null, array(array('test_key' => '')));
        $this->assertEquals(preg_match('[^(?=.*test_key:)(?=.*<empty>).*$]', $result), 1);

        $result = $method->invokeArgs(null, array(array('test_key' => array('test_value'))));
        $this->assertEquals(strpos($result, 'test_key: Array'), 0);
    }

    public function testPostAPIIssue()
    {
        $curlErr = 'test_curl_err';
        $response = 'test_response';
        $reqUrl = 'test_request_url';
        $reqData = 'test_req_data';

        $result = GootenWCErrorReport::postAPIIssue(null, $response, $reqUrl, $reqData);
        $this->assertEquals($result, 1);

        $result = GootenWCErrorReport::postAPIIssue($curlErr, $response, $reqUrl, $reqData);
        $this->assertEquals($result, 1);
    }

    public function testPostUnhandledException()
    {
        $exception = new Exception('test_message');
        $result = GootenWCErrorReport::postUnhandledException($exception);
        $this->assertTrue($result);
    }

}