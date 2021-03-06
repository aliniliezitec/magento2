<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Test\Unit\Model\Wishlist\Product;

use Magento\GroupedProduct\Model\Product\Type\Grouped as TypeGrouped;

/**
 * Unit test for Wishlist Item Plugin.
 */
class ItemTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\GroupedProduct\Model\Wishlist\Product\Item
     */
    protected $model;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \Magento\Wishlist\Model\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    /**
     * Init Mock Objects
     */
    protected function setUp()
    {
        $this->subjectMock = $this->createPartialMock(
            \Magento\Wishlist\Model\Item::class,
            [
                'getOptionsByCode',
                'getBuyRequest',
                'setOptions',
                'mergeBuyRequest',
                'getProduct'
            ]
        );

        $this->productMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            [
                'getId',
                'getTypeId',
                'getCustomOptions'
            ]
        );

        $this->model = new \Magento\GroupedProduct\Model\Wishlist\Product\Item();
    }

    /**
     * Test Before Represent Product method
     */
    public function testBeforeRepresentProduct()
    {
        $testSimpleProdId = 34;
        $prodInitQty = 2;
        $prodQtyInWishlist = 3;
        $resWishlistQty = $prodInitQty + $prodQtyInWishlist;
        $superGroup = [
            'super_group' => [
                33 => "0",
                34 => 3,
                35 => "0"
            ]
        ];

        $superGroupObj = new \Magento\Framework\DataObject($superGroup);

        $this->productMock->expects($this->once())->method('getId')->willReturn($testSimpleProdId);
        $this->productMock->expects($this->once())->method('getTypeId')
            ->willReturn(TypeGrouped::TYPE_CODE);
        $this->productMock->expects($this->once())->method('getCustomOptions')
            ->willReturn(
                $this->getProductAssocOption($prodInitQty, $testSimpleProdId)
            );

        $wishlistItemProductMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            [
                'getId',
            ]
        );
        $wishlistItemProductMock->expects($this->once())->method('getId')->willReturn($testSimpleProdId);

        $this->subjectMock->expects($this->once())->method('getProduct')
            ->willReturn($wishlistItemProductMock);
        $this->subjectMock->expects($this->once())->method('getOptionsByCode')
            ->willReturn(
                $this->getWishlistAssocOption($prodQtyInWishlist, $resWishlistQty, $testSimpleProdId)
            );
        $this->subjectMock->expects($this->once())->method('getBuyRequest')->willReturn($superGroupObj);

        $this->model->beforeRepresentProduct($this->subjectMock, $this->productMock);
    }

    /**
     * Test Before Compare Options method with same keys
     */
    public function testBeforeCompareOptionsSameKeys()
    {
        $options1 = ['associated_product_34' => 3];
        $options2 = ['associated_product_34' => 2];

        $res = $this->model->beforeCompareOptions($this->subjectMock, $options1, $options2);

        $this->assertEquals([], $res[0]);
        $this->assertEquals([], $res[1]);
    }

    /**
     * Test Before Compare Options method with diff keys
     */
    public function testBeforeCompareOptionsDiffKeys()
    {
        $options1 = ['associated_product_1' => 3];
        $options2 = ['associated_product_34' => 2];

        $res = $this->model->beforeCompareOptions($this->subjectMock, $options1, $options2);

        $this->assertEquals($options1, $res[0]);
        $this->assertEquals($options2, $res[1]);
    }

    /**
     * Return mock array with wishlist options
     *
     * @param int $initVal
     * @param int $resVal
     * @param int $prodId
     * @return array
     */
    private function getWishlistAssocOption($initVal, $resVal, $prodId)
    {
        $items = [];

        $optionMock = $this->createPartialMock(
            \Magento\Wishlist\Model\Item\Option::class,
            [
                'getValue',
            ]
        );
        $optionMock->expects($this->at(0))->method('getValue')->willReturn($initVal);
        $optionMock->expects($this->at(1))->method('getValue')->willReturn($resVal);

        $items['associated_product_' . $prodId] = $optionMock;

        return $items;
    }

    /**
     * Return mock array with product options
     *
     * @param int $initVal
     * @param int $prodId
     * @return array
     */
    private function getProductAssocOption($initVal, $prodId)
    {
        $items = [];

        $optionMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product\Configuration\Item\Option::class,
            [
                'getValue',
            ]
        );

        $optionMock->expects($this->once())->method('getValue')->willReturn($initVal);

        $items['associated_product_' . $prodId] = $optionMock;

        return $items;
    }
}
