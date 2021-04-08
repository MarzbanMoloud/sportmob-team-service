<?php


namespace App\Http\Controllers\Admin\Swagger\Interfaces;


use Illuminate\Http\Request;


/**
 * Interface TeamControllerInterface
 * @package App\Http\Controllers\Admin\Swagger\Interfaces
 */
interface TeamControllerInterface
{
	/**
	 * @OA\Get(
	 *     path="/admin/teams/{team}",
	 *     tags={"Admin"},
	 *     @OA\Parameter(
	 *         name="team",
	 *         in="path",
	 *         description="TeamId",
	 *         required=true,
	 *         example="11857851-cb0e-3bc3-830b-774d7cfc72ec",
	 *         @OA\Schema(type="string")
	 *     ),
	 *     @OA\Response(
	 *          response=200,
	 *          description="when response is OK",
	 *          @OA\MediaType(
	 *             mediaType="application/json",
	 *             @OA\Schema(
	 *                     type="object",
	 *                     @OA\Property(
	 *                         property="links",
	 *                         type="array",
	 *                         @OA\Items()
	 *                     ),
	 *                     @OA\Property(
	 *                          property="data",
	 *                          type="object",
	 *                          @OA\Property(
	 *                              property="id",
	 *                              type="string",
	 *                              example="74e07bd3-72ba-3e59-b2fd-52ad5fcaf61a"
	 *                          ),
	 *                          @OA\Property(
	 *                              property="name",
	 *                              type="object",
	 *                          	@OA\Property(
	 *                              	property="original",
	 *                              	type="string",
	 *                              	example="Barcelona"
	 *                          	),
	 *                          	@OA\Property(
	 *                              	property="official",
	 *                              	type="string",
	 *                              	example="BarcelonaOfficial"
	 *                          	),
	 *                          	@OA\Property(
	 *                              	property="short",
	 *                              	type="string",
	 *                              	example="Bar"
	 *                          	)
	 *                          ),
	 *                          @OA\Property(
	 *                              property="country",
	 *                              type="string",
	 *                              example="England"
	 *                          ),
	 *                          @OA\Property(
	 *                              property="city",
	 *                              type="string",
	 *                              example="London"
	 *                          ),
	 *                          @OA\Property(
	 *                              property="founded",
	 *                              type="string",
	 *                              example="2020-01-01"
	 *                          ),
	 *                          @OA\Property(
	 *                              property="gender",
	 *                              type="string",
	 *                              example="female"
	 *                          ),
	 *                          @OA\Property(
	 *                              property="active",
	 *                              type="boolean",
	 *                              example=false
	 *                          ),
	 *                          @OA\Property(
	 *                              property="type",
	 *                              type="string",
	 *                              example="'club' or 'national'"
	 *                          )
	 *                      )
	 *                 )
	 *         )
	 *     ),
	 *     @OA\Response(
	 *          response=404,
	 *          description="When Resource not found",
	 *          @OA\MediaType(
	 *             mediaType="application/json",
	 *             @OA\Schema(
	 *                  type="object",
	 *                  @OA\Property(
	 *                      property="message",
	 *                      type="string",
	 *                      example="Resource not found."
	 *                  ),
	 *                  @OA\Property(
	 *                      property="code",
	 *                      type="string",
	 *                      example="TM-404"
	 *                  ),
	 *             )
	 *         )
	 *     )
	 * )
	 * @param string $team
	 */
	public function show(string $team);

	/**
	 * @OA\Put(
	 *     path="/admin/teams/{team}",
	 *     tags={"Admin"},
	 *     @OA\Parameter(
	 *          name="team",
	 *          in="path",
	 *          required=true,
	 *          description="teamId",
	 *          example="123e4567-e89b-12d3-a456-426614174000",
	 *          @OA\Schema(type="string")
	 *     ),
	 *     @OA\RequestBody(
	 *         @OA\MediaType(
	 *             mediaType="application/json",
	 *             @OA\Schema(
	 *                 required={"name"},
	 *                 @OA\Property(
	 *                     property="name",
	 *                     type="object",
	 *                     @OA\Property(
	 *                     		property="original",
	 *                     		type="object",
	 *							example="RealMadrid"
	 *                 	   ),
	 *                     @OA\Property(
	 *                     		property="official",
	 *                     		type="object",
	 *							example="RealMadridOfficial"
	 *                 	   ),
	 *                     @OA\Property(
	 *                     		property="short",
	 *                     		type="object",
	 *							example="Real"
	 *                 	   )
	 *                 )
	 *             ),
	 *         )
	 *     ),
	 *     @OA\Response(
	 *          response=204,
	 *          description="When the update is successful"
	 *     ),
	 * )
	 * @param string $team
	 * @param Request $request
	 */
	public function update(string $team, Request $request);
}