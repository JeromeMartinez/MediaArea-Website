<?php

namespace MediaConchBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MoreInfoControllerTest extends WebTestCase
{
    private $errorReporting;

    protected function setUp(): void
    {
        // The analysis of the reports emits notices and warnings, ignored in production
        $this->errorReporting = error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
    }

    protected function tearDown(): void
    {
        error_reporting($this->errorReporting);

        parent::tearDown();
    }

    public function testReportTextIsEscaped()
    {
        $report = '{"media":{"track":[{"Format":"MXF","extra":{"ConformanceErrors":'
            .'{"<b>MXF</b>":{"<i>Element</i>":"<img src=x onerror=alert(1)> \"quoted\""}}}}]}}';
        $client = static::createClient();
        $client->request('GET', '/MoreInfo', ['mi' => $report]);
        $content = $client->getResponse()->getContent();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertStringNotContainsString('<img src=x', $content);
        $this->assertStringNotContainsString('<b>MXF</b>', $content);
        $this->assertStringContainsString('&lt;b&gt;MXF&lt;/b&gt;/&lt;i&gt;Element&lt;/i&gt;', $content);
        $this->assertStringContainsString('&lt;img src=x onerror=alert(1)&gt; &quot;quoted&quot;', $content);
    }
}
