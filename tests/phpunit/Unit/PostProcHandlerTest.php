<?php

namespace SMW\Tests;

use SMW\PostProcHandler;

/**
 * @covers \SMW\PostProcHandler
 * @group semantic-mediawiki
 *
 * @license GNU GPL v2+
 * @since 3.0
 *
 * @author mwjames
 */
class PostProcHandlerTest extends \PHPUnit_Framework_TestCase {

	private $parserOutput;

	protected function setUp() {
		parent::setUp();

		$this->parserOutput = $this->getMockBuilder( '\ParserOutput' )
			->disableOriginalConstructor()
			->getMock();
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			PostProcHandler::class,
			new PostProcHandler( $this->parserOutput )
		);
	}

	public function testGetHtml() {

		$this->parserOutput->expects( $this->once() )
			->method( 'getExtensionData' )
			->with( $this->equalTo( PostProcHandler::PROC_POST_QUERYREF ) )
			->will( $this->returnValue( [ 'Bar' => true ] ) );

		$instance = new PostProcHandler( $this->parserOutput );

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->once() )
			->method( 'getDBKey' )
			->will( $this->returnValue( 'Foo' ) );

		$title->expects( $this->once() )
			->method( 'getNamespace' )
			->will( $this->returnValue( NS_MAIN ) );

		$title->expects( $this->once() )
			->method( 'getLatestRevID' )
			->will( $this->returnValue( 42 ) );

		$webRequest = $this->getMockBuilder( '\WebRequest' )
			->disableOriginalConstructor()
			->getMock();

		$webRequest->expects( $this->once() )
			->method( 'getCookie' )
			->will( $this->returnValue( 'FakeCokie' ) );

		$this->assertContains(
			'<div class="smw-postproc" data-subject="Foo#0#" data-queryref="[&quot;Bar&quot;]"></div>',
			$instance->getHtml( $title,  $webRequest )
		);
	}

	/**
	 * @dataProvider queryRefProvider
	 */
	public function testAddQueryRef( $gExtensionData, $sExtensionData, $ref ) {

		$this->parserOutput->expects( $this->once() )
			->method( 'getExtensionData' )
			->with( $this->equalTo( PostProcHandler::PROC_POST_QUERYREF ) )
			->will( $this->returnValue( $gExtensionData ) );

		$this->parserOutput->expects( $this->once() )
			->method( 'setExtensionData' )
			->with( $this->equalTo( PostProcHandler::PROC_POST_QUERYREF ) )
			->will( $this->returnValue( $sExtensionData ) );

		$instance = new PostProcHandler( $this->parserOutput );

		$instance->addQueryRef( $ref );
	}

	public function queryRefProvider() {

		$provider[] =[
			null,
			[ 'Foo' => true ],
			'Foo'
		];

		$provider[] =[
			[ 'Bar' => true ],
			[ 'Bar' => true, 'Foo' => true ],
			'Foo'
		];

		return $provider;
	}

}
