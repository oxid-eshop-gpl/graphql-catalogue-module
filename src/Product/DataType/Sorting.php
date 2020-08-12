<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Catalogue\Product\DataType;

use OxidEsales\GraphQL\Base\DataType\Sorting as BaseSorting;
use TheCodingMachine\GraphQLite\Annotations\Factory;

final class Sorting extends BaseSorting
{
    /**
     * @Factory()
     */
    public static function fromUserInput(
        ?string $title = null,
        ?string $price = null
    ): self {
        return new self([
            'oxtitle'       => $title,
            'oxvarminprice' => $price,
        ]);
    }
}