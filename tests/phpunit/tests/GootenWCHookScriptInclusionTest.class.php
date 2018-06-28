<?php
/**
 * Created by Vlado on 3-Jan-17.
 */

require_once('BaseTest.class.php');

class GootenWCHookScriptInclusionTest extends BaseTest
{
    private $hook;

    public function setUp()
    {
        parent::setUp();
        $this->hook = new GootenWCHookScriptInclusion($this->GootenWC);
    }

    public function testMethodsHaveOutput()
    {
        $this->assertMethodsHaveOutput($this->hook, array('print_gooten_config'));
    }
}