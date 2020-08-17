<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Catalogue\Manufacturer\Controller;

use OxidEsales\GraphQL\Catalogue\Manufacturer\DataType\Manufacturer as ManufacturerDataType;
use OxidEsales\GraphQL\Catalogue\Manufacturer\DataType\ManufacturerFilterList;
use OxidEsales\GraphQL\Catalogue\Manufacturer\DataType\Sorting;
use OxidEsales\GraphQL\Catalogue\Manufacturer\Service\Manufacturer as ManufacturerService;
use TheCodingMachine\GraphQLite\Annotations\Query;

final class Manufacturer
{
    /** @var ManufacturerService */
    private $manufacturerService;

    public function __construct(
        ManufacturerService $manufacturerService
    ) {
        $this->manufacturerService = $manufacturerService;
    }

    /**
     * @Query()
     */
    public function manufacturer(string $id): ManufacturerDataType
    {
        return $this->manufacturerService->manufacturer($id);
    }

    /**
     * @Query()
     *
     * @return ManufacturerDataType[]
     */
    public function manufacturers(
        ?ManufacturerFilterList $filter = null,
        ?Sorting $sort = null
    ): array {
        return $this->manufacturerService->manufacturers(
            $filter ?? new ManufacturerFilterList(),
            $sort ?? new Sorting([])
        );
    }
}
