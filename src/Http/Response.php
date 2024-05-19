<?php

namespace VM\Http;

use VM\Http\Response\Encode;
use VM\Http\Response\Stream;
use VM\Http\Response\StreamFile;
use VM\Http\Response\PsrTraits;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

/**
 * Class Response
 * @package VM\Http
 */
class Response extends BaseResponse implements ResponseInterface 
{
    /**
     * Psr Response Trait
     */
    use PsrTraits;
    
    /**
     * Make an Response instance
     * @param string $content
     * @param int $status
     * @param array $headers 
     * @return self
     */
    public function make($context = '', int $status = 200, array $headers = [])
    {
        return $this->withStatus($status)->withHeaders($headers)->withBody(new Stream((string) $context));
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
        return $this->withStatus($status)
            ->withHeader('content-type', 'application/xml; charset=utf-8')
            ->withHeaders($headers)
            ->withBody(new Stream(Encode::toXml($context, null, $root)));
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
        return $this->withStatus($status)
            ->withHeader('content-type', 'text/plain; charset=utf-8')
            ->withHeaders($headers)
            ->withBody(new Stream(Encode::toRaw($context)));
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
        return $this->withStatus($status)
            ->withHeader('content-type', 'application/json; charset=utf-8')
            ->withHeaders($headers)
            ->withBody(new Stream(Encode::toJson($context, $callback, $options)));
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
        return $this->withStatus($status)
            ->withHeader('content-type', 'text/html; charset=utf-8')
            ->withHeaders($headers)
            ->withBody(new Stream($context));
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
        $this->withStatus($status)->withHeaders($headers)->headers->set('Location', $url);

        if (301 == $status && !\array_key_exists('cache-control', array_change_key_case($headers, \CASE_LOWER))) {
            $this->headers->remove('cache-control');
        }

        if (!$this->isRedirect()) {
            throw new \InvalidArgumentException(sprintf('The HTTP status code is not a redirect ("%s" given).', $status));
        }

        return $this;
    }


    /**
     * Create a file download response.
     *
     * @param string $file the file path which want to send to client
     * @param string $name the alias name of the file that client receive
     * @param string $disposition the disposition of the file, default is attachment 
     * @param bool $deleteFile delete the file after download, default is false
     * @return ResponseInterface
     */
    public function download(string $file, string $name = null, $disposition = 'attachment',  $deleteFile = false)
    {
        $file = new StreamFile($file);

        $this->setEtag(md5_file($file->getRealPath()));

        $this->setLastModified(\DateTime::createFromFormat('U', $file->getMTime()));
        
        $this->withoutHeader('Transfer-Encoding')
            ->withHeader('Content-Length', $file->getSize())
            ->withHeader('Content-Type', $file->getMimeType() ?: 'application/octet-stream')
            ->withHeader('Content-Disposition', $this->headers->makeDisposition($disposition, $name ?? $file->getFilename()))
            ->withBody($file);
        return $this;
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
     * @return self
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
     * getCookies
     * @return array
     */
    public function getCookies()
    {
        return $this->headers->getCookies();
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
}
