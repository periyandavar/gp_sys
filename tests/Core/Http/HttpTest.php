<?php

use System\Core\Http\Request\Request;
use System\Core\Http\Request\RestRequest;
use System\Core\Http\Request\WebRequest;
use System\Core\Http\Response\Response;
use System\Core\Http\Response\RestResponse;
use System\Core\Http\Response\WebResponse;
use System\Core\Test\TestCase;

class HttpTest extends TestCase
{
    public function testRequestInstance()
    {
        $request = new Request();
        $this->assertInstanceOf(Request::class, $request);
    }

    public function testRestRequestContentType()
    {
        $restRequest = $this->getMockBuilder(RestRequest::class)
            ->onlyMethods(['server'])
            ->getMock();
        $restRequest->method('server')->with('CONTENT_TYPE')->willReturn('application/json');
        $this->assertEquals('application/json', $restRequest->getContentType());
    }

    public function testRestRequestGetRawBodyAndJsonBody()
    {
        $restRequest = $this->getMockBuilder(RestRequest::class)
            ->onlyMethods(['getRawBody'])
            ->getMock();
        $restRequest->method('getRawBody')->willReturn('{"foo":"bar"}');
        $this->assertEquals(['foo' => 'bar'], $restRequest->getJsonBody(true));
    }

    public function testRestRequestGetAuthorization()
    {
        $restRequest = $this->getMockBuilder(RestRequest::class)
            ->onlyMethods(['server'])
            ->getMock();
        $restRequest->method('server')->willReturnMap([
            ['HTTP_AUTHORIZATION', 'Bearer token'],
            ['REDIRECT_HTTP_AUTHORIZATION', null]
        ]);
        $this->assertEquals('Bearer token', $restRequest->getAuthorization());
    }

    public function testRestRequestGetMethodOverride()
    {
        $restRequest = $this->getMockBuilder(RestRequest::class)
            ->onlyMethods(['post'])
            ->getMock();
        $restRequest->method('post')->with('_method')->willReturn('put');
        $this->assertEquals('PUT', $restRequest->getMethodOverride());
    }

    public function testWebRequestGetClientIp()
    {
        $webRequest = $this->getMockBuilder(WebRequest::class)
            ->onlyMethods(['server'])
            ->getMock();
        $webRequest->method('server')->willReturnMap([
            ['HTTP_CLIENT_IP', '1.2.3.4'],
            ['HTTP_X_FORWARDED_FOR', null],
            ['REMOTE_ADDR', '5.6.7.8']
        ]);
        $this->assertEquals('1.2.3.4', $webRequest->getClientIp());
    }

    public function testWebRequestGetUserAgent()
    {
        $webRequest = $this->getMockBuilder(WebRequest::class)
            ->onlyMethods(['server'])
            ->getMock();
        $webRequest->method('server')->with('HTTP_USER_AGENT')->willReturn('TestAgent');
        $this->assertEquals('TestAgent', $webRequest->getUserAgent());
    }

    public function testWebRequestGetReferer()
    {
        $webRequest = $this->getMockBuilder(WebRequest::class)
            ->onlyMethods(['server'])
            ->getMock();
        $webRequest->method('server')->with('HTTP_REFERER')->willReturn('http://referer');
        $this->assertEquals('http://referer', $webRequest->getReferer());
    }

    public function testWebRequestIsSecure()
    {
        $webRequest = $this->getMockBuilder(WebRequest::class)
            ->onlyMethods(['server'])
            ->getMock();
        $webRequest->method('server')->willReturnMap([
            ['HTTPS', 'on'],
            ['SERVER_PORT', 443]
        ]);
        $this->assertTrue($webRequest->isSecure());
    }

    public function testResponseHandleException()
    {
        $response = new Response();
        $e = new Exception('Error', 500);
        $result = $response->handleException($e);
        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals(500, $result->getStatusCode());
        $this->assertEquals('Error', $result->getBody());
    }

    public function testRestResponseJson()
    {
        $restResponse = $this->getMockBuilder(RestResponse::class)
            ->onlyMethods(['setStatusCode', 'setBody', 'send'])
            ->getMock();
        $restResponse->expects($this->once())->method('setStatusCode')->with(200);
        $restResponse->expects($this->once())->method('setBody')->with(json_encode(['foo' => 'bar']));
        $restResponse->expects($this->once())->method('send');
        $restResponse->json(['foo' => 'bar'], 200);
    }

    public function testRestResponseCreated()
    {
        $restResponse = $this->getMockBuilder(RestResponse::class)
            ->onlyMethods(['setStatusCode', 'setHeader', 'setBody', 'send'])
            ->getMock();
        $restResponse->expects($this->once())->method('setStatusCode')->with(201);
        $restResponse->expects($this->once())->method('setHeader')->with('Location', '/resource');
        $restResponse->expects($this->once())->method('setBody')->with(json_encode(['foo' => 'bar']));
        $restResponse->expects($this->once())->method('send');
        $restResponse->created(['foo' => 'bar'], '/resource');
    }

    public function testRestResponseNoContent()
    {
        $restResponse = $this->getMockBuilder(RestResponse::class)
            ->onlyMethods(['setStatusCode', 'setBody', 'send'])
            ->getMock();
        $restResponse->expects($this->once())->method('setStatusCode')->with(204);
        $restResponse->expects($this->once())->method('setBody')->with('');
        $restResponse->expects($this->once())->method('send');
        $restResponse->noContent();
    }

    public function testRestResponseUpdated()
    {
        $restResponse = $this->getMockBuilder(RestResponse::class)
            ->onlyMethods(['json', 'noContent'])
            ->getMock();
        $restResponse->expects($this->once())->method('json')->with(['foo' => 'bar'], 200);
        $restResponse->updated(['foo' => 'bar']);
    }

    public function testRestResponseError()
    {
        $restResponse = $this->getMockBuilder(RestResponse::class)
            ->onlyMethods(['json'])
            ->getMock();
        $restResponse->expects($this->once())->method('json')->with([
            'error' => true,
            'message' => 'fail',
            'status' => 400,
        ], 400);
        $restResponse->error('fail', 400);
    }

    public function testRestResponseHandleException()
    {
        $restResponse = new RestResponse();
        $e = new Exception('fail', 501);
        $result = $restResponse->handleException($e);
        $this->assertInstanceOf(RestResponse::class, $result);
        $this->assertEquals(500, $result->getStatusCode());
        $body = json_decode($result->getBody(), true);
        $this->assertTrue($body['error']);
        $this->assertEquals('fail', $body['message']);
    }

    public function testWebResponseHtml()
    {
        $webResponse = $this->getMockBuilder(WebResponse::class)
            ->onlyMethods(['setHeader', 'setStatusCode', 'setBody', 'send'])
            ->getMock();
        $webResponse->expects($this->once())->method('setHeader')->with('Content-Type', 'text/html; charset=utf-8');
        $webResponse->expects($this->once())->method('setStatusCode')->with(200);
        $webResponse->expects($this->once())->method('setBody')->with('<h1>hi</h1>');
        $webResponse->expects($this->once())->method('send');
        $webResponse->html('<h1>hi</h1>', 200);
    }

    public function testWebResponseHandleException()
    {
        $webResponse = $this->getMockBuilder(WebResponse::class)
            ->onlyMethods(['setStatusCode', 'html'])
            ->getMock();
        $e = new Exception('fail', 502);
        $webResponse->expects($this->once())->method('setStatusCode')->with(502);
        $webResponse->expects($this->once())->method('html');
        $result = $webResponse->handleException($e);
        $this->assertInstanceOf(WebResponse::class, $result);
    }
}
