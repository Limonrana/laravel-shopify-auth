<?php

namespace Osiset\ShopifyApp\Macros;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Osiset\ShopifyApp\Objects\Values\ShopDomain;
use Osiset\ShopifyApp\Util;

/**
 * Common URL generation for TokenRedirect and TokenRoute macros.
 */
abstract class TokenUrl
{
    /**
     * Return a URL to token path with shop and target.
     *
     * When App Bridge is disabled (non-embedded), skip the token bootstrap and
     * point straight at the destination route.
     *
     * @param string $route The route name.
     * @param array $params Additional route params.
     * @param bool $absolute Absolute or relative?
     *
     * @return array
     */
    public function generateParams(string $route, array $params = [], bool $absolute = true): array
    {
        if (! Util::isAppBridgeEnabled()) {
            $request = Request::instance();
            $destinationParams = array_merge($params, array_filter([
                'shop' => ShopDomain::fromRequest($request)->toNative(),
                'host' => $request->get('host'),
                'locale' => $request->get('locale'),
            ], static fn ($value) => $value !== null && $value !== ''));

            return [$route, $destinationParams];
        }

        return [
            Util::getShopifyConfig('route_names.authenticate.token'),
            [
                'shop' => ShopDomain::fromRequest(Request::instance())->toNative(),
                'target' => URL::route($route, $params, $absolute),
                'host' => Request::instance()->get('host'),
                'locale' => Request::instance()->get('locale'),
            ],
        ];
    }
}
