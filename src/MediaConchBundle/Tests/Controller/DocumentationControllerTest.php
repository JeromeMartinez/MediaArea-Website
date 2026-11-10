<?php

namespace MediaConchBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class DocumentationControllerTest extends WebTestCase
{
    #[DataProvider('urlProvider')]
    public function testResponse200($url)
    {
        $client = static::createClient();

        $client->request('GET', $url);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public static function urlProvider()
    {
        return [
            ['/MediaConch/Documentation/Fixity'],
            ['/MediaConch/Documentation/FAQ'],
            ['/MediaConch/Documentation/HowToUse'],
            ['/MediaConch/Documentation/DataFormat'],
            ['/MediaConch/Documentation/Installation'],
        ];
    }
}
