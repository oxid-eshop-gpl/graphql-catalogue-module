<?php

declare(strict_types=1);

namespace OxidEsales\GraphQL\Catalogue\Tests\Integration\Controller;

use OxidEsales\GraphQL\Base\Tests\Integration\MultishopTestCase;

/**
 * Class PromotionEnterpriseTest
 * @package OxidEsales\GraphQL\Catalogue\Tests\Integration\Controller
 */
class PromotionEnterpriseTest extends MultishopTestCase
{
    private const PROMOTION_SUB_SHOP_ID = "test_active_sub_shop_promotion_1";

    /**
     * Get single active promotion from sub shop
     */
    public function testGetPromotionFromSubShop()
    {
        $this->setGETRequestParameter('shp', "2");

        $result = $this->query('query {
            promotion (id: "' . self::PROMOTION_SUB_SHOP_ID . '") {
                id
                active
                title
                text
            }
        }');

        $this->assertResponseStatus(
            200,
            $result
        );

        $promotion = $result['body']['data']['promotion'];

        $this->assertSame(self::PROMOTION_SUB_SHOP_ID, $promotion['id']);
        $this->assertSame(true, $promotion['active']);
        $this->assertSame('Current sub shop Promotion 1 DE', $promotion['title']);
        $this->assertSame('Long description 1 DE', $promotion['text']);

        $this->assertEmpty(array_diff(array_keys($promotion), [
            'id',
            'active',
            'title',
            'text'
        ]));
    }

    /**
     * Check if both promotion related to the shop 2 are available in list
     */
    public function testGetPromotionListFromSubShop()
    {
        $this->setGETRequestParameter('shp', "2");

        $result = $this->query('query{
            promotions {
                id
            }
        }');
        $this->assertResponseStatus(
            200,
            $result
        );
        // fixtures have 2 active promotion for shop 2
        $this->assertCount(
            2,
            $result['body']['data']['promotions']
        );
    }
}
