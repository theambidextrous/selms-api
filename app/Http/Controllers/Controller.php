<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         version="3.1.121",
 *         title="Senior education learning management system apis",
 *         description="API Documentation",
 *         @OA\Contact(email="api@selms-app.com"),
 *         @OA\License(name="BSD")
 *     ),
 *     @OA\Server(
 *         url=L5_SWAGGER_CONST_HOST,
 *         description="API Server"
 *     ),
 *     @OA\SecurityScheme(
 *         securityScheme="bearerAuth",
 *         type="http",
 *         scheme="bearer",
 *         bearerFormat="JWT"
 *     )
 * )
 */

/**
 * @OA\Tag(name="General", description="General endpoints")
 */

/**
 * @OA\Info( version="3.1.121",
 *         title="Senior education learning management system apis",
 *         description="API Documentation",
 *         @OA\Contact(email="api@selms-app.com"),
 *         @OA\License(name="BSD")),
 * @OA\Server(
 *         url=L5_SWAGGER_CONST_HOST,
 *         description="API Server"),
 * @OA\SecurityScheme(
 *         securityScheme="bearerAuth",
 *         type="http",
 *         scheme="bearer",
 *         bearerFormat="JWT")
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
