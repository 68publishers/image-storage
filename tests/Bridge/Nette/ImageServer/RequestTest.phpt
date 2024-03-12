<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Bridge\Nette\ImageServer;

use Nette\Http\Request as NetteRequest;
use Nette\Http\UrlScript;
use SixtyEightPublishers\ImageStorage\Bridge\Nette\ImageServer\Request;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

final class RequestTest extends TestCase
{
    public function testRequestMethods(): void
    {
        $netteRequest = new NetteRequest(
            new UrlScript('https://www.example.com/images/test/w:100/image.png?_v=123'),
        );
        $request = new Request($netteRequest);

        Assert::same('/images/test/w:100/image.png', $request->getUrlPath());
        Assert::same('123', $request->getQueryParameter('_v'));
        Assert::null($request->getQueryParameter('_s'));
        Assert::same($netteRequest, $request->getOriginalRequest());
    }
}

(new RequestTest())->run();
