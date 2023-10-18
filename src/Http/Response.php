<?php

namespace VM\Http;

use VM\Http\Response\Encode;
use VM\Http\Response\Stream;
use VM\Http\Response\ResponseTraits;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

/**
 * Class Response
 * @package VM\Http
 */
class Response implements ResponseInterface 
{
    use ResponseTraits;

    /**
     * @var ResponseHeaderBag
     */
    protected $headers;

    /**
     * @var BaseResponse
     */
    protected $response;

    /**
     * constructor Response
     */
    public function __construct()
    {
        $this->headers = new ResponseHeaderBag();
    }

    /**
     * Make an Response instance
     * @param string $content
     * @param int $status
     * @param array $headers 
     * @return self
     */
    public function make($context = '', int $status = 200, array $headers = [])
    {
        return $this->withHeaders($headers)->setResponse(new BaseResponse(new Stream((string) $context), $status));
    }

    /**
     * Format data to XML and return data with Content-Type:application/xml header.
     * @param array|Arrayable|Xmlable $context
     * @param int $status
     * @param array $headers
     * @param string $root
     * @return self
     */
    public function xml(array $context = [], string $root = 'root', int $status = 200,  array $headers = [])
    {
        return $this->withHeader('content-type', 'application/xml; charset=utf-8')
            ->withHeaders($headers)
            ->setResponse(new BaseResponse(new Stream(Encode::toXml($context, null, $root)),$status));
    }
    /**
     * Format data to a string and return data with content-type:text/plain header.
     * @param mixed $context will transfer to a string value
     * @param int $status
     * @param array $headers
     * @return self
     */
    public function raw($context = '', int $status = 200, array $headers = [])
    {
        return $this->withHeader('content-type', 'text/plain; charset=utf-8')
            ->withHeaders($headers)
            ->setResponse(new BaseResponse(new Stream(is_string($context) ? $context : print_r($context, true)),$status));
    }

    /**
     * Format data to JSON and return data with Content-Type: application/json header.
     *
     * @param array|Arrayable|Jsonable $context
     * @param int $status
     * @param array $headers
     * @param string $callback
     * @param int $options
     * @return ResponseInterface
     */
    public function json($context = [], int $status = 200,  array $headers = [], string $callback = null, int $options = JSON_UNESCAPED_UNICODE)
    {
        $this->withHeader('content-type', 'application/json; charset=utf-8')
            ->withHeaders($headers)
            ->setResponse(new JsonResponse(new Stream(Encode::toJson($context, $options)), $status, [], true));
        $callback && $this->getResponse()->setCallback($callback);
        return $this;
    }


    /**
     * @param string $context
     * @param array $data
     * @param int $status
     * 
     * @return ResponseInterface
     */
    public function html(string $context = '', int $status = 200, array $headers = [])
    {
        return $this->withHeader('content-type', 'text/html; charset=utf-8')
            ->withHeaders($headers)
            ->setResponse(new BaseResponse(new Stream($context),$status));
    }


    /**
     * Redirect to a url with a status.
     * @param string $url
     * @param int $status
     * @param array $headers
     * @return ResponseInterface
     */
    public function redirect(string $url,  int $status = 302, array $headers = []) 
    {
        return $this->withHeaders($headers)->setResponse(new RedirectResponse($url, $status));
    }


    /**
     * Create a file download response.
     *
     * @param string $file the file path which want to send to client
     * @param string $name the alias name of the file that client receive
     */
    public function download(string $file, string $name = null)
    {
        $file = new \SplFileInfo($file);
        if (! $file->isReadable()) {
            throw new \RuntimeException("The file {$file} Unreadable.");
        }
        $etag = $this->createEtag($file);
        $name = $name ?: $file->getBasename();
        $contentType = value(function () use ($file) {
            return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file) ?? 'application/octet-stream';
        });

        // Determine if ETag the client expects matches calculated ETag
        $ifMatch = app('request')->header('if-match');
        $ifNoneMatch = app('request')->header('if-none-match');
        $clientEtags = explode(',', $ifMatch ?: $ifNoneMatch);
        array_walk($clientEtags, 'trim');
        if (in_array($etag, $clientEtags, true)) {
            return $this->withHeader('content-type', $contentType)->setResponse(new BinaryFileResponse($file, 304));
        }

        return $this->withHeaders([
                'etag'=>$etag,
                'pragma'=>'public',
                'content-type'=>$contentType,
                'content-description'=>'File Transfer',
                'content-transfer-encoding'=>'binary',
                'content-disposition'=>"attachment; filename={$name}; filename*=UTF-8''" . rawurlencode($name)
            ])->setResponse(new BinaryFileResponse($file, 200));
    }

    /**
     * Set the response header 
     * @param $name string
     * @param $value string
     * @return self|string
     */
    public function header($name, $value = null)
    {
        return is_null($value) ? $this->headers->get($name) : $this->withHeader($name, $value);
    }

    /**
     * Response Headers 
     * @param mixed|null $headers 
     * @return ResponseHeaderBag
     */
    public function headers(...$headers)
    {
        return $headers ? $this->withHeaders(...$headers) : $this->headers;
    }
    
    /**
     * With Cookie alias name
     * @param string $name
     * @param string $value
     * @return self
     */
    public function cookie($name, $value)
    {
        return $this->withCookie($name, $value);
    }

    /**
     * withCookie
     * @param $key
     * @param $value
     * @return self
     */
    public function withCookie($key, $value): self
    {
        $this->headers->setCookie(app('cookie')->make($key, $value));
        return $this;
    }

    /**
     * Get the response object from self.
     * 
     * @return BaseResponse|JsonResponse|RedirectResponse|BinaryFileResponse
     */
    protected function getResponse()
    {
        return $this->response;
    }

    /**
     * Get the response object from self.
     * 
     * @return self it's an object that , or maybe it's a proxy class
     */
    protected function setResponse(BaseResponse $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * Get ETag header according to the checksum of the file.
     * @param \SplFileInfo $file
     * @param bool $weak
     * @return string
     */
    protected function createEtag(\SplFileInfo $file, bool $weak = false): string
    {
        if ($weak) {
            $lastModified = $file->getMTime();
            $filesize = $file->getSize();
            if (! $lastModified || ! $filesize) {
                return '';
            }
            return sprintf('W/"%x-%x"', $lastModified, $filesize);
        } else {
            return md5_file($file->getPathname());
        }
    }

    /**
     * Dynamic call method
     */
    public function __call($name, $arguments)
    {
        in_array($name, ['prepare', 'send']) && $this->getResponse()->headers = $this->headers; 
        if (! method_exists($this->getResponse(), $name)) {
            throw new \BadMethodCallException(sprintf('Call to undefined method %s::%s()', get_class($this), $name));
        }
        return $this->getResponse()->{$name}(...$arguments);
    }
}
