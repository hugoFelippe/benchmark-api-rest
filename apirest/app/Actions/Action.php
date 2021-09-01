<?php

declare(strict_types=1);

namespace App\Actions;

use App\Domain\DomainException\DomainRecordNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Factory\StreamFactory;

abstract class Action
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var array
     */
    protected $arguments;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $arguments
     * @return Response
     * @throws HttpNotFoundException
     * @throws HttpBadRequestException
     */
    public function __invoke(Request $request, Response $response, array $arguments): Response
    {
        $this->request = $request;
        $this->response = $response;
        $this->arguments = $arguments;
        $this->parameters = array_merge(
            (array)($request->getParsedBody() ?? []),
            ($request->getQueryParams() ?? [])
        );

        return $this->action();
    }

    /**
     * @return Response
     * @throws DomainRecordNotFoundException
     * @throws HttpBadRequestException
     */
    abstract protected function action(): Response;

    /**
     * @return array|object
     * @throws HttpBadRequestException
     */
    protected function getFormData()
    {
        $input = json_decode(file_get_contents('php://input'));

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new HttpBadRequestException($this->request, 'Malformed JSON input.');
        }

        return $input;
    }

    /**
     * @param  string $name
     * @return mixed
     * @throws HttpBadRequestException
     */
    protected function resolveArg(string $name)
    {
        if (!isset($this->arguments[$name])) {
            throw new HttpBadRequestException($this->request, "Could not resolve argument `{$name}`.");
        }

        return $this->arguments[$name] ?? null;
    }

    protected function args(string $name, bool $optional = true)
    {
        if (!$optional && !isset($this->arguments[$name])) {
            throw new HttpBadRequestException($this->request, "Could not resolve argument `{$name}`.");
        }

        return $this->arguments[$name] ?? null;
    }

    protected function param(string $name, bool $optional = true)
    {
        if (!$optional && !isset($this->parameters[$name])) {
            throw new HttpBadRequestException($this->request, "Could not resolve parametro `{$name}`.");
        }

        return $this->parameters[$name] ?? null;
    }

    /**
     * @param array|object|null $data
     * @param int $statusCode
     * @return Response
     */
    protected function respondWithData($data = null, int $statusCode = 200): Response
    {
        $payload = new ActionPayload($statusCode, $data);

        return $this->respond($payload);
    }

    protected function respondWithFile($path = null, int $statusCode = 200, bool $download = false): Response
    {
        if (!realpath($path)) {
            throw new HttpNotFoundException($this->request, 'Arquivo não encontrado');
        }

        $streamFactory = new StreamFactory();
        $stream = $streamFactory->createStreamFromFile($path);

        $length = filesize($path);
        $type = mime_content_type($path);
        // $name = pathinfo($path, PATHINFO_BASENAME);
        // $contentDisposition = 'attachment; filename="' . iconv("UTF-8", "ISO-8859-1//IGNORE", $name) . '"';
        $disposition = $download ? 'attachment' : 'inline';

        return $this->response
                    ->withHeader('Content-Disposition', $disposition)
                    ->withHeader('Content-Length', $length)
                    ->withHeader('Content-Type', $type)
                    ->withHeader('Accept-Ranges', 'bytes')
                    ->withStatus($statusCode)
                    ->withBody($stream);
    }

    /**
     * @param ActionPayload $payload
     * @return Response
     */
    protected function respond(ActionPayload $payload): Response
    {
        $json = json_encode($payload, JSON_PRETTY_PRINT);
        $this->response->getBody()->write($json);

        return $this->response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus($payload->getStatusCode());
    }
}
