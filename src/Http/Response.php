<?php

namespace VM\Http;

use VM\Http\Response\Encode;
use VM\Http\Response\StreamBase;
use VM\Http\Response\ResponseTraits;
use Psr\Http\Message\ResponseInterface;
use Illuminate\Support\Traits\Macroable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

use function get_class;

/**
 * Class Response
 * @package VM\Http
 */
class Response implements ResponseInterface 
{
    use Macroable, ResponseTraits;


    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var BaseResponse
     */
    protected $response;

    /**
     * Make an Response instance
     * @param string $content
     * @param int $status
     * @param array $headers 
     * @return self
     */
    public function make($content = '', int $status = 200, array $headers = [])
    {
        if($content instanceof self) return $content;
        return $this->setResponse(new BaseResponse)
        ->withStatus($status)
        ->withHeaders($headers)
        ->withBody(new StreamBase((string) $content));
    }

    /**
     * Format data to a string and return data with content-type:text/plain header.
     * @param mixed $content will transfer to a string value
     * @param int $status
     * @param array $headers
     * @return self
     */
    public function raw(mixed $content = null, int $status = 200, array $headers = [])
    {
       return $this->setResponse(new BaseResponse)
            ->withStatus($status)
            ->withHeader('content-type', 'text/plain; charset=utf-8')
            ->withHeaders($headers)
            ->withBody(new StreamBase(is_string($content) ? $content : print_r($content, false)));
    }

    /**
     * Format data to XML and return data with Content-Type:application/xml header.
     * @param array|Arrayable|Xmlable $content
     * @param string $root
     * @param int $status
     * @param array $headers
     * @return self
     */
    public function xml(array $content = [], string $root = 'root', int $status = 200, array $headers = [])
    {
        return $this->setResponse(new BaseResponse)
            ->withStatus($status)
            ->withHeader('content-type', 'application/xml; charset=utf-8')
            ->withHeaders($headers)
            ->withBody(new StreamBase(Encode::toXml($content, null, $root)));
    }

    /**
     * Format data to JSON and return data with Content-Type:application/json header.
     *
     * @param array|Arrayable|Jsonable $data
     * @param int $status
     * @param array $headers
     * @return ResponseInterface
     */
    public function json(array $data = [], int $status = 200, int $options = JSON_UNESCAPED_UNICODE)
    {
        return $this->setResponse(new JsonResponse)
            ->withStatus($status)
            ->withHeader('content-type', 'application/json; charset=utf-8')
            ->withBody(new StreamBase(Encode::toJson($data, $options)));
    }


    /**
     * @param string $html
     * @param array $data
     * @param int $status
     * 
     * @return ResponseInterface
     */
    public function html(string $html, array $data = [], int $status = 200, array $headers = [])
    {
        return $this->setResponse(new BaseResponse)
            ->withStatus($status)
            ->withHeaders($headers)
            ->withBody(new StreamBase($html));
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
        return $this->setResponse(new RedirectResponse($url, $status))
            ->withStatus($status)
            ->withHeader('Location', $url)
            ->withHeaders($headers);
    }


    /**
     * Create a file download response.
     *
     * @param string $file the file path which want to send to client
     * @param string $name the alias name of the file that client receive
     */
    public function download(string $file, string $name = '')
    {
        $file = new \SplFileInfo($file);
        $this->response = new BinaryFileResponse($file);

        if (! $file->isReadable()) {
            throw new \RuntimeException('The file Unreadable.');
        }

        $filename = $name ?: $file->getBasename();
        $etag = $this->createEtag($file);
        $this->response->setEtag($etag);
        $contentType = value(function () use ($file) {
            return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file) ?? 'application/octet-stream';
        });

        // Determine if ETag the client expects matches calculated ETag
        $request = new \VM\Http\Request;
        $ifMatch = $request->header('if-match');
        $ifNoneMatch = $request->header('if-none-match');
        $clientEtags = explode(',', $ifMatch ?: $ifNoneMatch);
        array_walk($clientEtags, 'trim');
        if (in_array($etag, $clientEtags, true)) {
            return $this->withStatus(304)->withHeader('content-type', $contentType);
        }

        return $this->withHeader('content-description', 'File Transfer')
            ->withHeader('content-type', $contentType)
            ->withHeader('content-disposition', "attachment; filename={$filename}; filename*=UTF-8''" . rawurlencode($filename))
            ->withHeader('content-transfer-encoding', 'binary')
            ->withHeader('pragma', 'public')
            ->withHeader('etag', $etag);
    }

    /**
     * Set the response header 
     * @param $key string
     * @param $value string
     * @return self
     */
    public function header($key, $value = null){
        if(!$value) return $this->getHeader($key);
        return $this->withHeader($key, $value);
    }

    /**
     * Set bulk response header 
     * @param array|string $headers
     * @return self
     */
    public function headers(...$headers){
        if(!$headers) return $this->getHeaders();
        return $this->withHeaders(...$headers);
    }

    /**
     * Get the response object from self.
     * 
     * @return BaseResponse it's an object that , or maybe it's a proxy class
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
     * withCookie
     */
    public function withCookie(Cookie $cookie): ResponseInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * Get ETag header according to the checksum of the file.
     */
    protected function createEtag(\SplFileInfo $file, bool $weak = false): string
    {
        $etag = '';
        if ($weak) {
            $lastModified = $file->getMTime();
            $filesize = $file->getSize();
            if (! $lastModified || ! $filesize) {
                return $etag;
            }
            $etag = sprintf('W/"%x-%x"', $lastModified, $filesize);
        } else {
            $etag = md5_file($file->getPathname());
        }
        return $etag;
    }

    /**
     * Dynamic call method
     */
    public function __call($name, $arguments)
    {
        $response = $this->getResponse();
        if (! method_exists($response, $name)) {
            throw new \BadMethodCallException(sprintf('Call to undefined method %s::%s()', get_class($this), $name));
        }
        return $response->{$name}(...$arguments);
    }

    /**
     * Static call method
     */
    public static function __callStatic($name, $arguments)
    {
        $response = (new self)->getResponse();
        if (! method_exists($response, $name)) {
            throw new \BadMethodCallException(sprintf('Call to undefined static method %s::%s()', self::class, $name));
        }
        return $response::{$name}(...$arguments);
    }
}
