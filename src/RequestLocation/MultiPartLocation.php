<?php
namespace GuzzleHttp\Command\Guzzle\RequestLocation;

use GuzzleHttp\Command\CommandInterface;
use GuzzleHttp\Command\Guzzle\Parameter;
use GuzzleHttp\Psr7;
use Psr\Http\Message\RequestInterface;

/**
 * Adds POST files to a request
 */
class MultiPartLocation extends AbstractLocation
{
    protected $contentType = 'multipart/form-data; boundary=';

    /** @var array $formParamsData */
    protected $multipartData = [];

    /**
     * Set the name of the location
     *
     * @param string $locationName
     */
    public function __construct($locationName = 'multipart')
    {
        parent::__construct($locationName);
    }

    public function visit(
        CommandInterface $command,
        RequestInterface $request,
        Parameter $param
    ) {
        $this->multipartData['multipart'] = [
            'name' => $param->getWireName(),
            'contents' => $this->prepareValue($command[$param->getName()], $param)
        ];

        $body = new Psr7\MultipartStream($this->multipartData);
        $modify['body'] = Psr7\stream_for($body);
        $request = Psr7\modify_request($request, $modify);
        if ($request->getBody() instanceof Psr7\MultipartStream) {
            // Use a multipart/form-data POST if a Content-Type is not set.
            $request->withHeader('Content-Type', $this->contentType . $request->getBody()->getBoundary());
        }

        return $request;
    }
}
