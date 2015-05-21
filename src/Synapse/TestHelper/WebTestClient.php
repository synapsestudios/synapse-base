<?php
namespace Synapse\TestHelper;

use Symfony\Component\BrowserKit\Request as DOMRequest;
use Symfony\Component\HttpKernel\Client;

class WebTestClient extends Client
{
    protected $extraHeaders = [];

    /**
     * Extra headers to inject into the array
     * @param array $headers
     */
    public function setExtraHeaders(array $headers)
    {
        $this->extraHeaders = $headers;
    }

    public function setBearerHeader($token = '123')
    {
        $this->extraHeaders['Authorization'] = 'Bearer '.$token;
    }

    public function setRequestJson($setAccept = true)
    {
        if ($setAccept) {
            $this->extraHeaders['HTTP_ACCEPT']       = 'application/json';
        }
        $this->extraHeaders['HTTP_CONTENT_TYPE'] = 'application/json';
    }

    /**
     * @param string $uri
     * @param array  $params
     *
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    public function get($uri, $params = [])
    {
        return $this->request(
            'GET',
            $uri,
            $params
        );
    }

    /**
     * @param string       $uri
     * @param string|array $content
     * @param array        $params
     * @param array        $files
     *
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    public function post($uri, $content = '', $params = [], $files = [])
    {
        return $this->request(
            'POST',
            $uri,
            $params,
            $files,
            [],
            is_array($content) ? json_encode($content) : $content
        );
    }

    /**
     * @param string       $uri
     * @param string|array $content
     * @param array        $params
     * @param array        $files
     *
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    public function put($uri, $content = '', $params = [], $files = [])
    {
        return $this->request(
            'PUT',
            $uri,
            $params,
            $files,
            [],
            is_array($content) ? json_encode($content) : $content
        );
    }

    /**
     * @param string       $uri
     * @param string|array $content
     * @param array        $params
     * @param array        $files
     *
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    public function patch($uri, $content = '', $params = [], $files = [])
    {
        return $this->request(
            'PATCH',
            $uri,
            $params,
            $files,
            [],
            is_array($content) ? json_encode($content) : $content
        );
    }

    /**
     * @param string       $uri
     * @param array        $params
     *
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    public function delete($uri, $params = [])
    {
        return $this->request(
            'DELETE',
            $uri,
            $params
        );
    }

    /**
     * Overrides filterRequest to add any extra necessary headers
     * {@inheritdoc}
     */
    protected function filterRequest(DOMRequest $request)
    {
        $request = parent::filterRequest($request);

        $request->headers->add($this->extraHeaders);

        return $request;
    }
}
