<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Bridge\Nette\ImageServer;

use Mockery;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Utils\Helpers;
use SixtyEightPublishers\ImageStorage\Bridge\Nette\ImageServer\ErrorResponse;
use SixtyEightPublishers\ImageStorage\Exception\ResponseException;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

final class ErrorResponseTest extends TestCase
{
    public function testSentErrorResponse(): void
    {
        $exception = new ResponseException('File not found.', 404);
        $httpResponse = Mockery::mock(IResponse::class);
        $httpRequest = Mockery::mock(IRequest::class);

        $httpResponse->shouldReceive('setCode')
            ->once()
            ->with(404)
            ->andReturnSelf();

        $response = new ErrorResponse($exception);
        $output = Helpers::capture(static fn () => $response->send($httpRequest, $httpResponse));

        Assert::same($exception, $response->getException());
        Assert::same('{"code":404,"message":"File not found."}', $output);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}

(new ErrorResponseTest())->run();
