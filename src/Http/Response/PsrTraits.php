<?php

namespace VM\Http\Response;

use Psr\Http\Message\StreamInterface;

trait PsrTraits {
    /**
     * Retrieves the HTTP protocol version as a string.
     * The string MUST contain only the HTTP version number (e.g., "1.1", "1.0").
     *
     * @return string HTTP protocol version
     */
    public function getProtocolVersion(): string
    {
        return parent::getProtocolVersion();
    }

    /**
     * Return an instance with the specified HTTP protocol version.
     * The version string MUST contain only the HTTP version number (e.g.,
     * "1.1", "1.0").
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new protocol version.
     *
     * @param string $version HTTP protocol version
     * @return PsrResponseInterface
     */
    public function withProtocolVersion($version)
    {
        return $this->setProtocolVersion($version);
    }

    /**
     * Retrieves all message header values.
     * The keys represent the header name as it will be sent over the wire, and
     * each value is an array of strings associated with the header.
     *     // Represent the headers as a string
     *     foreach ($message->getHeaders() as $name => $values) {
     *         echo $name . ": " . implode(", ", $values);
     *     }
     *     // Emit headers iteratively:
     *     foreach ($message->getHeaders() as $name => $values) {
     *         foreach ($values as $value) {
     *             header(sprintf('%s: %s', $name, $value), false);
     *         }
     *     }
     * While header names are not case-sensitive, getHeaders() will preserve the
     * exact case in which headers were originally specified.
     *
     * @return string[][] Returns an associative array of the message's headers. Each
     *                    key MUST be a header name, and each value MUST be an array of strings
     *                    for that header.
     */
    public function getHeaders(): array
    {
        return $this->headers->all();
    }

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name case-insensitive header field name
     * @return bool Returns true if any header names match the given header
     *              name using a case-insensitive string comparison. Returns false if
     *              no matching header name is found in the message.
     */
    public function hasHeader($name): bool
    {
        return $this->headers->has($name);
    }

    /**
     * Retrieves a message header value by the given case-insensitive name.
     * This method returns an array of all the header values of the given
     * case-insensitive header name.
     * If the header does not appear in the message, this method MUST return an
     * empty array.
     *
     * @param string $name case-insensitive header field name
     * @return string[] An array of string values as provided for the given
     *                  header. If the header does not appear in the message, this method MUST
     *                  return an empty array.
     */
    public function getHeader($name): array
    {
        return $this->headers->get($name);
    }

    /**
     * Retrieves a comma-separated string of the values for a single header.
     * This method returns all of the header values of the given
     * case-insensitive header name as a string concatenated together using
     * a comma.
     * NOTE: Not all header values may be appropriately represented using
     * comma concatenation. For such headers, use getHeader() instead
     * and supply your own delimiter when concatenating.
     * If the header does not appear in the message, this method MUST return
     * an empty string.
     *
     * @param string $name case-insensitive header field name
     * @return string A string of values as provided for the given header
     *                concatenated together using a comma. If the header does not appear in
     *                the message, this method MUST return an empty string.
     */
    public function getHeaderLine($name): string
    {
        return $this->headers->getHeaderLine($name);
    }

    /**
     * Return an instance with the provided value replacing the specified header.
     * While header names are case-insensitive, the casing of the header will
     * be preserved by this function, and returned from getHeaders().
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new and/or updated header and value.
     *
     * @param string $name case-insensitive header field name
     * @param string|string[] $value header value(s)
     * @throws \InvalidArgumentException for invalid header names or values
     * @return PsrResponseInterface
     */
    public function withHeader($name, $value)
    {
        $this->headers->set($name, trim($value));
        return $this;
    }


    /**
    * Muiltaple set Headers
    * @param array|string $headers
    * @return PsrResponseInterface
    */
    public function withHeaders(...$headers)
    {
        array_map(function($header){
            if(is_array($header)){
                $this->withHeader(key($header), pos($header));
            }else{
                $header = explode(':', $header);
                $this->withHeader($header[0], $header[1]);
            }
        }, (count($headers) == 1 && is_array($headers[0])) ? array_chunk($headers[0],1, true) : $headers);
        return $this;
    }

    /**
     * Return an instance with the specified header appended with the given value.
     * Existing values for the specified header will be maintained. The new
     * value(s) will be appended to the existing list. If the header did not
     * exist previously, it will be added.
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new header and/or value.
     *
     * @param string $name case-insensitive header field name to add
     * @param string|string[] $value header value(s)
     * @throws \InvalidArgumentException for invalid header names or values
     * @return PsrResponseInterface
     */
    public function withAddedHeader($name, $value)
    {
        return $this->withHeader($name, $value);
    }

    /**
     * Return an instance without the specified header.
     * Header resolution MUST be done without case-sensitivity.
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the named header.
     *
     * @param string $name case-insensitive header field name to remove
     * @return ResponseInterface
     */
    public function withoutHeader($name)
    {
        $this->headers->remove($name);
        return $this;
    }

    /**
     * Gets the body of the message.
     *
     * @return StreamInterface returns the body as a stream
     */
    public function getBody()
    {
        return $this->getContent();
    }

    /**s
     * Return an instance with the specified message body.
     * The body MUST be a StreamInterface object.
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new body stream.
     *
     * @param StreamInterface $body body
     * @throws \InvalidArgumentException when the body is not valid
     * @return PsrResponseInterface
     */
    public function withBody(StreamInterface $body)
    {
        $this->setContent($body);
        return $this;
    }

    /**
     * Gets the response status code.
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @return int status code
     */
    public function getStatusCode(): int
    {
        return parent::getStatusCode();
    }

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     * If no reason phrase is specified, implementations MAY choose to default
     * to the RFC 7231 or IANA recommended reason phrase for the response's
     * status code.
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated status and reason phrase.
     *
     * @see http://tools.ietf.org/html/rfc7231#section-6
     * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @param int $code the 3-digit integer result code to set
     * @param string $reasonPhrase the reason phrase to use with the
     *                             provided status code; if none is provided, implementations MAY
     *                             use the defaults as suggested in the HTTP specification
     * @throws \InvalidArgumentException for invalid status code arguments
     * @return PsrResponseInterface
     */
    public function withStatus($code, $reasonPhrase = null)
    {
        $this->setStatusCode(isset(parent::$statusTexts[$code]) ? $code : 500, $reasonPhrase);
        return $this;
    }

    /**
     * Gets the response reason phrase associated with the status code.
     * Because a reason phrase is not a required element in a response
     * status line, the reason phrase value MAY be null. Implementations MAY
     * choose to return the default RFC 7231 recommended reason phrase (or those
     * listed in the IANA HTTP Status Code Registry) for the response's
     * status code.
     *
     * @see http://tools.ietf.org/html/rfc7231#section-6
     * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @return string reason phrase; must return an empty string if none present
     */
    public function getReasonPhrase(): string
    {
        return parent::$statusTexts[$this->getStatusCode()];
    }
}