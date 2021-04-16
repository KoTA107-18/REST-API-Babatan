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
 *         description="Halaman ini merupakan dokumentasi Server REST API dari Aplikasi Pengelolaan Antrean Puskesmas Babatan.",
 *         termsOfService="http://swagger.io/terms/",
 *         @OA\Contact(
 *             email="andi.fauzy.tif18@polban.ac.id"
 *         ),
 *     ),
 *     @OA\ExternalDocumentation(
 *         description="Find out more about Swagger",
 *         url="http://swagger.io"
 *     )
 * )
 */

 /**
 * @OA\Tag(
 *   name="Pasien",
 *   description="Operasi pada role Pasien",
 * )
 * @OA\Tag(
 *   name="Administrator",
 *   description="Operasi pada role Administrator"
 * )
 * @OA\Tag(
 *   name="Perawat",
 *   description="Operasi pada role Perawat",
 * )
 */

 /**
* @OA\Get(
 *   tags={"Pasien"},
 *   path="/pasien",
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

 /**
* @OA\Get(
 *   tags={"Administrator"},
 *   path="/administrator",
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

/**
* @OA\Get(
 *   tags={"Perawat"},
 *   path="/perawat",
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

 /**
* @OA\Post(
 *   tags={"Perawat"},
 *   path="/perawat",
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