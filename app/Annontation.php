<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;


/**
 * @OA\Swagger(
 *     basePath="/",
 *     schemes={"http"},
 *     @OA\Info(
 *         version="1.0.0",
 *         title="REST API Documentation Babatan",
 *         @OA\Contact(
 *             email="andi.fauzy.tif18@polban.ac.id"
 *         ),
 *     )
 * )
 */
/**
* @OA\Get(
 *   path="/annotation",
 *   summary="Version",
 *   @OA\Response(
 *     response=200,
 *     description="Working"
 *   ),
 *   @OA\Response(
 *     response="default",
 *     description="an ""unexpected"" error"
 *   )
 * )
 */
class Annotation extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

}