<?php

namespace SMW\Tests\Exporter;

use SMW\DIWikiPage;
use SMW\Exporter\DataItemToExpResourceMapper;
use SMW\Exporter\Element;
use SMW\Exporter\Escaper;
use SMWExporter as Exporter;

/**
 * @covers \SMW\Exporter\DataItemToExpResourceMapper
 *
 * @group semantic-mediawiki
 *
 * @license GNU GPL v2+
 * @since 2.2
 *
 * @author mwjames
 */
class DataItemToExpResourceMapperTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider diWikiPageProvider
	 */
	public function testMapWikiPageToResourceElement( $dataItem, $modifier, $expected ) {

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$instance = new DataItemToExpResourceMapper(
			$store
		);

		$resource = $instance->mapWikiPageToResourceElement( $dataItem, $modifier );

		$this->assertSame(
			$expected,
			$resource->getSerialization()
		);
	}

	public function testMapWikiPageToResourceElementForImportMatch() {

		$dataItem = new DIWikiPage( 'Foo', SMW_NS_PROPERTY, '', '' );

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$store->expects( $this->once() )
			->method( 'getPropertyValues' )
			->will(
				$this->returnValue( array( new \SMWDIBlob( 'foo:bar:fom:fuz' ) ) ) );

		$instance = new DataItemToExpResourceMapper(
			$store
		);

		$resource = $instance->mapWikiPageToResourceElement(
			$dataItem
		);

		// || is not the result we normally would expect but mocking the
		// dataValueFactory at this point is not worth the hassle therefore
		// we live with || output
		$expected =	array(
			'type' => Element::TYPE_NSRESOURCE,
			'uri'  => "||",
			'dataitem' => array( 'type' => 9, 'item' => 'Foo#102#' )
		);

		$this->assertSame(
			$expected,
			$resource->getSerialization()
		);
	}

	public function diWikiPageProvider() {

		// Constant
		$wiki = \SMWExporter::getInstance()->getNamespaceUri( 'wiki' );
		$property = \SMWExporter::getInstance()->getNamespaceUri( 'property' );

		#0
		$provider[] = array(
			new DIWikiPage( 'Foo', NS_MAIN, '', '' ),
			'',
			array(
				'type' => Element::TYPE_NSRESOURCE,
				'uri'  => "Foo|{$wiki}|wiki",
				'dataitem' => array( 'type' => 9, 'item' => 'Foo#0#' )
			)
		);

		#1
		$provider[] = array(
			new DIWikiPage( 'Foo', NS_MAIN, 'bar', '' ),
			'',
			array(
				'type' => Element::TYPE_NSRESOURCE,
				'uri'  => "bar-3AFoo|{$wiki}|wiki",
				'dataitem' => array( 'type' => 9, 'item' => 'Foo#0#bar' )
			)
		);

		#2
		$provider[] = array(
			new DIWikiPage( 'Foo', NS_MAIN, 'bar', '1234' ),
			'',
			array(
				'type' => Element::TYPE_NSRESOURCE,
				'uri'  => "bar-3AFoo-231234|{$wiki}|wiki",
				'dataitem' => array( 'type' => 9, 'item' => 'Foo#0#bar#1234' )
			)
		);

		#3 Extra modififer doesn't not alter the object when a subobject is used
		$provider[] = array(
			new DIWikiPage( 'Foo', NS_MAIN, 'bar', '1234' ),
			'abc',
			array(
				'type' => Element::TYPE_NSRESOURCE,
				'uri'  => "bar-3AFoo-231234|{$wiki}|wiki",
				'dataitem' => array( 'type' => 9, 'item' => 'Foo#0#bar#1234' )
			)
		);

		#4
		$provider[] = array(
			new DIWikiPage( 'Foo', SMW_NS_PROPERTY, '', '' ),
			'',
			array(
				'type' => Element::TYPE_NSRESOURCE,
				'uri'  => "Foo|{$property}|property",
				'dataitem' => array( 'type' => 9, 'item' => 'Foo#102#' )
			)
		);

		#5
		$provider[] = array(
			new DIWikiPage( 'Foo', SMW_NS_PROPERTY, '', '' ),
			true,
			array(
				'type' => Element::TYPE_NSRESOURCE,
				'uri'  => "Foo-23aux|{$property}|property",
				'dataitem' => array( 'type' => 9, 'item' => 'Foo#102#' )
			)
		);

		#6
		$name = Escaper::encodePage(
			new DIWikiPage( '-Foo', SMW_NS_PROPERTY, '', '' )
		);

		$provider[] = array(
			new DIWikiPage( '-Foo', SMW_NS_PROPERTY, '', '' ),
			true,
			array(
				'type' => Element::TYPE_NSRESOURCE,
				'uri'  => "$name-23aux|{$wiki}|wiki",
				'dataitem' => array( 'type' => 9, 'item' => '-Foo#102#' )
			)
		);

		return $provider;
	}

}
