<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use AppBundle\Utility;

class AbstractControllerTest extends WebTestCase
{

    const TF_SOMETHING = "TF_SOMETHING";

    public static $allTFs = [
        self::TF_SOMETHING,
    ];

    public function makeFirstAssertions(
        $res,
        $code,
        $expected)
    {
        $resBody = json_decode($res->getContent());

        // Asserts response status code and header
        $this->assertEquals($code, $res->getStatusCode());
        $this->assertTrue($res->headers->contains('Content-Type', 'application/json'));

        // Asserts "expected"
        $this->doMakeFirstAssertions($resBody, $expected);

        return $resBody;
    }

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
                } else {
                    $this->assertEquals($value, $resBody->$key);
                }
            }
        }
    }
}
