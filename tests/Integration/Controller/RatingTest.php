<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Catalogue\Tests\Integration\Controller;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\GraphQL\Catalogue\Tests\Integration\TokenTestCase;

final class RatingTest extends TokenTestCase
{
    private const RATING_ID = '13f810d1aa415400c8abdd37a5b2181a';
    private const WRONG_USER = '_test_wrong_user';
    private const WRONG_PRODUCT = '_test_wrong_product';
    private const WRONG_OBJECT_TYPE = '_test_wrong_object_type';

    public function testGetSingleRating()
    {
        $result = $this->query('query {
            rating(id: "' . self::RATING_ID . '") {
                id
                rating
                timestamp
                user {
                    id
                    firstName
                    lastName
                }
                product {
                    id
                    title
                }
            }
        }');

        $this->assertResponseStatus(
            200,
            $result
        );

        $rating = $result['body']['data']['rating'];

        $this->assertSame([
            'id' => self::RATING_ID,
            'rating' => 4,
            'timestamp' => '2011-02-16T15:21:20+01:00',
            'user' => [
                'id' => 'e7af1c3b786fd02906ccd75698f4e6b9',
                'firstName' => 'Marc',
                'lastName' => 'Muster'
            ],
            'product' => [
                'id' => 'd86e244c8114c8214fbf83da8d6336b3',
                'title' => 'Wakeboard LIQUID FORCE SHANE 2010'
            ]
        ], $rating);
    }

    public function testGetSingleNonExistingRating()
    {
        $result = $this->query('query {
            rating (id: "DOES-NOT-EXIST") {
                id
            }
        }');

        $this->assertResponseStatus(
            404,
            $result
        );
    }

    public function testGetWrongUserCase()
    {
        $result = $this->query('query {
            rating(id: "' . self::WRONG_USER . '") {
                id
                user {
                    id
                }
            }
        }');

        $this->assertResponseStatus(
            200,
            $result
        );

        $rating = $result['body']['data']['rating'];

        $this->assertSame([
            'id' => self::WRONG_USER,
            'user' => null
        ], $rating);
    }

    /**
     * @dataProvider nullProductIdsDataProvider
     */
    public function testGetWrongProductCase($id)
    {
        $result = $this->query('query {
            rating(id: "' . $id . '") {
                id
                product {
                    id
                }
            }
        }');

        $this->assertResponseStatus(
            200,
            $result
        );

        $rating = $result['body']['data']['rating'];

        $this->assertSame([
            'id' => $id,
            'product' => null
        ], $rating);
    }

    public function nullProductIdsDataProvider()
    {
        return [
            [self::WRONG_PRODUCT],
            [self::WRONG_OBJECT_TYPE]
        ];
    }
}