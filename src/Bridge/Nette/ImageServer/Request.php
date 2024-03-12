<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Nette\ImageServer;

use Nette\Http\IRequest;
use SixtyEightPublishers\ImageStorage\ImageServer\RequestInterface;

final class Request implements RequestInterface
{
    public function __construct(
        private readonly IRequest $request,
    ) {}

    public function getUrlPath(): string
    {
        return $this->request->getUrl()->getPath();
    }

    public function getQueryParameter(string $name): array|string|null
    {
        return $this->request->getUrl()->getQueryParameter($name);
    }

    public function getOriginalRequest(): IRequest
    {
        return $this->request;
    }
}
