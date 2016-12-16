<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Utility;

class AbstractControllerTest extends WebTestCase
{
    //
    // All function(controller) test should extend this class.
    // It has very basic methods to help in making first few basic assertions:
    // - Like checking response
    // - Response headers
    //

    public function makeFirstAssertions(Response $res, $code, $expected = array())
    {
        //
        // Asserts:
        // - Respons content type
        // - Response status code
        // - Response body
        //

        $isJson = $res->headers->contains('content-type', 'application/json');
        $this->assertTrue($isJson, 'Response content type is not json.');

        $statusCode = $res->getStatusCode();
        $this->assertEquals($code, $statusCode, 'Response status code mismatch.');

        $content = json_decode($res->getContent(), true);

        $this->doMakeFirstAssertions($content, $expected);

        return $content;
    }

    protected function doMakeFirstAssertions(& $content, array $expected)
    {
        //
        // Helper method to compare http response object & expected response array
        // and makes assertions accordingly.
        //
        // *Recursive
        //

        foreach ($expected as $key => $value)
        {
            $this->assertTrue(array_key_exists($key, $content), "'$key' key does not exists in response.");

            if (Utility::isAssocArray($value))
            {
                $this->doMakeFirstAssertions($content[$key], $value);
            }
            else
            {
                if (is_string($value) && TestFlag::isDefined($value))
                {
                    //
                }
                else
                {
                    $this->assertEquals($value, $content[$key], "Value of '$key' key not matching.");
                }
            }
        }
    }
}
