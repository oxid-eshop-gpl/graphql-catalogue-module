<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Catalogue\Service;

use InvalidArgumentException;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\GraphQL\Base\Exception\NotFound;
use OxidEsales\GraphQL\Catalogue\DataType\FilterList;
use OxidEsales\GraphQL\Catalogue\DataType\DataType;

class Repository
{
    /** @var QueryBuilderFactoryInterface $queryBuilderFactory */
    private $queryBuilderFactory;

    public function __construct(
        QueryBuilderFactoryInterface $queryBuilderFactory
    ) {
        $this->queryBuilderFactory = $queryBuilderFactory;
    }

    /**
     * @template T
     * @param class-string<T> $type
     * @return T
     * @throws InvalidArgumentException if $type is not instance of DataType
     * @throws NotFound if BaseModel can not be loaded
     */
    public function getById(
        string $id,
        string $type
    ) {
        $model = oxNew($type::getModelClass());
        if (!($model instanceof BaseModel)) {
            throw new InvalidArgumentException();
        }
        if (!$model->load($id)) {
            throw new NotFound($id);
        }
        $type = new $type($model);
        if (!($type instanceof DataType)) {
            throw new InvalidArgumentException();
        }
        return $type;
    }

    /**
     * @template T
     * @param class-string<T> $type
     * @return T[]
     * @throws InvalidArgumentException if $model is not instance of BaseModel
     */
    public function getByFilter(
        FilterList $filter,
        string $type
    ): array {
        $types = [];
        $model = oxNew($type::getModelClass());
        if (!($model instanceof BaseModel)) {
            throw new InvalidArgumentException();
        }
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder->select('*')
                     ->from($model->getViewName())
                     ->orderBy('oxid');

        if (
            $filter->getActive() !== null &&
            $filter->getActive()->equals() === true
        ) {
            $queryBuilder->andWhere($model->getSqlActiveSnippet());
        }

        $filters = array_filter($filter->getFilters());
        foreach ($filters as $field => $fieldFilter) {
            $fieldFilter->addToQuery($queryBuilder, $field);
        }

        $result = $queryBuilder->execute();

        if (!$result instanceof \Doctrine\DBAL\Driver\Statement) {
            return $types;
        }
        foreach ($result as $row) {
            $newModel = clone $model;
            $newModel->assign($row);
            $types[] = new $type($newModel);
        }
        return $types;
    }
}
