<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Utility;

/**
 * All function(controller) test should extend this class.
 * It has very basic methods to help in making first few basic assertions:
 * - Like checking response
 * - Response headers
 *
 */
class AbstractControllerTest extends WebTestCase
{

    /**
     * TF: Test flags
     * It helps with a feature (Will explain later :) )
     */
    const TF_SOMETHING = "TF_SOMETHING";

    public static $allTFs = [
        self::TF_SOMETHING,
    ];


    /**
     * @param Response $res      - The Symfony's Http 
     * @param integer  $code     - Expected http response code
     * @param array    $expected - Expected response array
     *
     * @return object
     */
    public function makeFirstAssertions(
        Response $res,
        $code,
        $expected = array())
    {
        $resBody = json_decode($res->getContent());

        // Asserts response status code and header
        $this->assertEquals($code, $res->getStatusCode());
        $this->assertTrue($res->headers->contains("content-type", "application/json"));

        // Asserts "expected"
        $this->doMakeFirstAssertions($resBody, $expected);

        return $resBody;
    }

    /**
     * Helper method to compare http response object & expected response array
     *     and makes assertions accordingly.
     * *Recursive
     *
     * @param object $resBody
     * @param array  $expected
     *
     * @return
     */
    protected function doMakeFirstAssertions(
        $resBody,
        array $expected)
    {
        foreach ($expected as $key => $value) {

            $this->assertTrue(property_exists($resBody, $key));

            if (Utility::isAssocArray($value)) {

                $this->doMakeFirstAssertions($resBody->$key, $value);
            } else {

                if (in_array($value, self::$allTFs, true) === true) {

                    // TODO: Implement this
                    // - For now it's okay
                } else {
                    $this->assertEquals($value, $resBody->$key);
                }
            }
        }
    }
}
