<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Catalogue\Product\Infrastructure;

use OxidEsales\Eshop\Application\Model\Article as EshopProductModel;
use OxidEsales\Eshop\Application\Model\ArticleList as EshopProductListModel;
use OxidEsales\Eshop\Application\Model\Attribute as EshopAttributeModel;
use OxidEsales\Eshop\Application\Model\AttributeList as EshopAttributeListModel;
use OxidEsales\Eshop\Application\Model\Category as EshopCategoryModel;
use OxidEsales\Eshop\Application\Model\Manufacturer as EshopManufacturerModel;
use OxidEsales\Eshop\Application\Model\Review as EshopReviewModel;
use OxidEsales\Eshop\Application\Model\SelectList as EshopSelectionListModel;
use OxidEsales\Eshop\Application\Model\Vendor as EshopVendorModel;
use OxidEsales\GraphQL\Catalogue\Category\DataType\Category as CategoryDataType;
use OxidEsales\GraphQL\Catalogue\Category\Service\Category as CategoryService;
use OxidEsales\GraphQL\Catalogue\Manufacturer\DataType\Manufacturer as ManufacturerDataType;
use OxidEsales\GraphQL\Catalogue\Product\DataType\Product as ProductDataType;
use OxidEsales\GraphQL\Catalogue\Product\DataType\ProductAttribute as ProductAttributeDataType;
use OxidEsales\GraphQL\Catalogue\Product\DataType\ProductScalePrice as ProductScalePriceDataType;
use OxidEsales\GraphQL\Catalogue\Product\DataType\SelectionList as SelectionListDataType;
use OxidEsales\GraphQL\Catalogue\Review\DataType\Review as ReviewDataType;
use OxidEsales\GraphQL\Catalogue\Vendor\DataType\Vendor as VendorDataType;

use function array_map;
use function count;
use function is_iterable;

final class Product
{
    /** @var CategoryService */
    private $categoryService;

    public function __construct(
        CategoryService $categoryService
    ) {
        $this->categoryService = $categoryService;
    }

    /**
     * @return ProductScalePriceDataType[]
     */
    public function getScalePrices(ProductDataType $product): array
    {
        $amountPrices = $product->getEshopModel()->loadAmountPriceInfo();

        return array_map(
            function ($amountPrice) {
                return new ProductScalePriceDataType($amountPrice);
            },
            $amountPrices
        );
    }

    public function getManufacturer(ProductDataType $product): ?ManufacturerDataType
    {
        /** @var null|EshopManufacturerModel $manufacturer */
        $manufacturer = $product->getEshopModel()->getManufacturer();

        if ($manufacturer === null) {
            return null;
        }

        return new ManufacturerDataType(
            $manufacturer
        );
    }

    public function getVendor(ProductDataType $product): ?VendorDataType
    {
        /** @var null|EshopVendorModel $vendor */
        $vendor = $product->getEshopModel()->getVendor();

        if ($vendor === null) {
            return null;
        }

        return new VendorDataType(
            $vendor
        );
    }

    /**
     * @return CategoryDataType[]
     */
    public function getCategories(
        ProductDataType $product,
        bool $onlyMainCategory
    ): array {
        $categories = [];

        if ($onlyMainCategory) {
            /** @var null|EshopCategoryModel $category */
            $category = $product->getEshopModel()->getCategory();

            if (
                $category === null ||
                !$category->getId()
            ) {
                return [];
            }

            $categories[] = new CategoryDataType(
                $category
            );
        } else {
            /** @var array $categoryIds */
            $categoryIds = $product->getEshopModel()->getCategoryIds();

            foreach ($categoryIds as $categoryId) {
                $categories[] = $this->categoryService->category($categoryId);
            }
        }

        return $categories;
    }

    /**
     * @return ProductDataType[]
     */
    public function getCrossSelling(ProductDataType $product): array
    {
        /** @var EshopProductListModel $products */
        $products = $product->getEshopModel()->getCrossSelling();

        if (!is_iterable($products) || count($products) === 0) {
            return [];
        }

        $crossSellings = [];

        /** @var EshopProductModel $product */
        foreach ($products as $product) {
            $crossSellings[] = new ProductDataType($product);
        }

        return $crossSellings;
    }

    /**
     * @return ProductAttributeDataType[]
     */
    public function getAttributes(ProductDataType $product): array
    {
        /** @var EshopAttributeListModel $productAttributes */
        $productAttributes = $product->getEshopModel()->getAttributes();

        if (!is_iterable($productAttributes) || count($productAttributes) === 0) {
            return [];
        }

        $attributes = [];

        /** @var EshopAttributeModel $attribute */
        foreach ($productAttributes as $key => $attribute) {
            $attributes[$key] = new ProductAttributeDataType($attribute);
        }

        return $attributes;
    }

    /**
     * @return ProductDataType[]
     */
    public function getAccessories(ProductDataType $product): array
    {
        /** @var EshopProductListModel $products */
        $products = $product->getEshopModel()->getAccessoires();

        if (!is_iterable($products) || count($products) === 0) {
            return [];
        }

        $accessories = [];

        /** @var EshopProductModel $product */
        foreach ($products as $product) {
            $accessories[] = new ProductDataType($product);
        }

        return $accessories;
    }

    /**
     * @return SelectionListDataType[]
     */
    public function getSelectionLists(ProductDataType $product): array
    {
        $selections = $product->getEshopModel()->getSelections();

        if (!is_iterable($selections) || count($selections) === 0) {
            return [];
        }

        $selectionLists = [];

        /** @var EshopSelectionListModel $selection */
        foreach ($selections as $selection) {
            $selectionLists[] = new SelectionListDataType($selection);
        }

        return $selectionLists;
    }

    /**
     * @return ReviewDataType[]
     */
    public function getReviews(ProductDataType $product): array
    {
        $productReviews = $product->getEshopModel()->getReviews();

        if (!is_iterable($productReviews) || count($productReviews) === 0) {
            return [];
        }

        $reviews = [];

        /** @var EshopReviewModel $review */
        foreach ($productReviews as $review) {
            $reviews[] = new ReviewDataType($review);
        }

        return $reviews;
    }

    /**
     * @return ProductDataType[]
     */
    public function getVariants(ProductDataType $product): array
    {
        $productVariants = $product->getEshopModel()->getVariants();

        if (!is_iterable($productVariants) || count($productVariants) === 0) {
            return [];
        }

        $variants = [];

        /** @var EshopProductModel $variant */
        foreach ($productVariants as $variant) {
            $variants[] = new ProductDataType($variant);
        }

        return $variants;
    }
}
