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
    public function makeFirstAssertions(
        Response $res,
        $code,
        $expected = array()
    )
    {

        // Asserts:
        // - Respons content type
        // - Response status code
        // - Response body

        $isJson = $res->headers->contains('content-type', 'application/json');
        $this->assertTrue($isJson);

        $statusCode = $res->getStatusCode();
        $this->assertEquals($code, $statusCode);

        $resBody = json_decode($res->getContent());
        $this->doMakeFirstAssertions($resBody, $expected);

        return $resBody;
    }

    /**
     * Helper method to compare http response object & expected response array
     *     and makes assertions accordingly.
     * *Recursive
     */
    protected function doMakeFirstAssertions(
              $resBody,
        array $expected
    )
    {
        foreach ($expected as $key => $value)
        {
            // Asserts if the key exists in res
            $this->assertTrue(property_exists($resBody, $key));

            // Recursively calls same method to resolve nested assertions
            if (Utility::isAssocArray($value))
            {
                $this->doMakeFirstAssertions($resBody->$key, $value);
            }
            else
            {
                if (is_string($value) && TestFlag::isDefined($value))
                {
                    // TODO: Implement this
                    // - For now it's okay
                }
                else
                {
                    $this->assertEquals($value, $resBody->$key);
                }
            }
        }
    }
}
