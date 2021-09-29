<?php


namespace App\Http\Controllers\Api\Swagger\Interfaces;


use Illuminate\Http\Request;

/**
 * Interface TransferControllerInterface
 * @package App\Http\Controllers\Api\Swagger\Interfaces
 */
interface TransferControllerInterface
{
	/**
	 * @OA\Get(
	 *     path="/{lang}/transfers/team/{team}/{season}",
	 *     tags={"Transfer"},
	 *     @OA\Parameter(
	 *         name="team",
	 *         in="path",
	 *         description="teamId",
	 *         required=true,
	 *         example="cee429ce-70ee-38f1-9f11-5e829fd5db0c",
	 *         @OA\Schema(type="string")
	 *     ),
	 *     @OA\Parameter(
	 *         name="lang",
	 *         in="path",
	 *         description="language",
	 *         required=true,
	 *         example="en",
	 *         @OA\Schema(type="string")
	 *     ),
	 *     @OA\Parameter(
	 *         name="season",
	 *         in="path",
	 *         description="season",
	 *         required=true,
	 *         example="2020-2021",
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
	 *                          type="array",
	 *     						@OA\Items(),
	 *         					example={
	 *								{
	 *									"id": "eyJwbGF5ZXJJZCI6ImY3YTRjNGI",
	 *									"person": {
	 *									"id": "4c6f05da-5004-3aed-9cb3-eb9a0d55e591",
	 *									"name": {
	 *									"full": "Adonis Breitenberg",
	 *									"short": "Adonis. B",
	 *									"official": "Adonis Breitenberg"
	 *									}
	 *									},
	 *									"toTeam": {
	 *									"id": "723a7d16-4e1d-3899-bef1-38680a67f11a",
	 *									"name": {
	 *									"full": "Barcelona",
	 *									"short": "Barca",
	 *									"official": "Barcelona"
	 *									}
	 *									},
	 *									"fromTeam": {
	 *									"id": "fafe4497-b23e-3d7f-95db-da8b7da43ecc",
	 *									"name": {
	 *									"full": "real madrid",
	 *									"short": "real",
	 *									"official": "real madrid"
	 *									}
	 *									},
	 *									"marketValue": 200,
	 *									"startDate": 1577836800,
	 *									"endDate": 1610431027,
	 *									"type": "transferred | loan | loan_back",
	 *									"like": 0,
	 *									"dislike": 1,
	 *									"season": "2019-2020"
	 *									}
	 *	                       },
	 *
	 *                      )
	 *                 )
	 *         )
	 *     )
	 * )
	 * @param string $team
	 * @param string|null $season
	 */
	public function listByTeam(string $team, string $season);

	/**
	 * @OA\Get(
	 *     path="/{lang}/transfers/team/{team}",
	 *     tags={"Transfer"},
	 *     @OA\Parameter(
	 *         name="team",
	 *         in="path",
	 *         description="teamId",
	 *         required=true,
	 *         example="cee429ce-70ee-38f1-9f11-5e829fd5db0c",
	 *         @OA\Schema(type="string")
	 *     ),
	 *     @OA\Parameter(
	 *         name="lang",
	 *         in="path",
	 *         description="language",
	 *         required=true,
	 *         example="en",
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
	 *                          type="array",
	 *     						@OA\Items(),
	 *         					example={"2019-2020", "2019-2020"},
	 *                      )
	 *                 )
	 *         )
	 *     )
	 * )
	 * @param string $team
	 */
	public function seasonsByTeam(string $team);

	/**
	 * @OA\Get(
	 *     path="/{lang}/transfers/person/{person}",
	 *     tags={"Transfer"},
	 *     @OA\Parameter(
	 *         name="person",
	 *         in="path",
	 *         description="personId",
	 *         required=true,
	 *         example="cee429ce-70ee-38f1-9f11-5e829fd5db0c",
	 *         @OA\Schema(type="string")
	 *     ),
	 *     @OA\Parameter(
	 *         name="lang",
	 *         in="path",
	 *         description="language",
	 *         required=true,
	 *         example="en",
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
	 *                          type="array",
	 *     @OA\Items(),
	 *     						example= {{
	 *							"id": "eyJwbGF5ZXJJZCI6ImY3YTRjNGIw",
	 *							"toTeam": {
	 *							"id": "723a7d16-4e1d-3899-bef1-38680a67f11a",
	 *							"name": {
	 *							"full": "Barcelona",
	 *							"short": "Barca",
	 *							"official": "Barcelona"
	 *							}
	 *							},
	 *							"fromTeam": {
	 *							"id": "fafe4497-b23e-3d7f-95db-da8b7da43ecc",
	 *							"name": {
	 *							"full": "real madrid",
	 *							"short": "real",
	 *							"official": "real madrid"
	 *							}
	 *							},
	 *							"marketValue": 200,
	 *							"startDate": 1577836800,
	 *							"endDate": 1610431027,
	 *							"type": "transferred | loan | loan_back",
	 *							"like": 0,
	 *							"dislike": 1,
	 *							"season": "2019-2020"
	 *							}}
	 *        					)
	 *                 )
	 *         )
	 *     )
	 * )
	 * @param string $person
	 */
	public function listByPerson(string $person);

	/**
	 * @OA\Put(
	 *     path="/{lang}/transfers/{action}/{transfer}",
	 *     tags={"Transfer"},
	 *     @OA\Parameter(
	 *          name="action",
	 *          in="path",
	 *          required=true,
	 *          description="action",
	 *          example="'like' or 'dislike",
	 *          @OA\Schema(type="string")
	 *     ),
	 *     @OA\Parameter(
	 *          name="lang",
	 *          in="path",
	 *          required=true,
	 *          description="language",
	 *          example="en",
	 *          @OA\Schema(type="string")
	 *     ),
	 *     @OA\Parameter(
	 *          name="transfer",
	 *          in="path",
	 *          required=true,
	 *          description="id",
	 *          example="eyJwbGF5ZXJJZCI6IjMw",
	 *          @OA\Schema(type="string")
	 *     ),
	 *     @OA\RequestBody(
	 *         @OA\MediaType(
	 *             mediaType="application/json",
	 *             @OA\Schema(
	 *                 required={"userId"},
	 *                 @OA\Property(
	 *                     	property="userId",
	 *                     	type="object",
	 *						example="007f646e-b7f7-334e-963f-2df2153a588e"
	 *     				)
	 * 			   )
	 * 			)
	 * 		),
	 *     @OA\Response(
	 *          response=204,
	 *          description="When the update is successful"
	 *     ),
	 *     @OA\Response(
	 *          response=422,
	 *          description="when user is not allowed to like or dislike",
	 *          @OA\MediaType(
	 *             mediaType="application/json",
	 *           @OA\Schema(
	 *                  type="object",
	 *                  @OA\Property(
	 *                      property="message",
	 *                      type="string",
	 *                      example="Unprocessable_request."
	 *                  ),
	 *                  @OA\Property(
	 *                      property="code",
	 *                      type="string",
	 *                      example="TM-002"
	 *                  ),
	 *             )
	 *         )
	 *     )
	 * )
	 * @param string $action
	 * @param string $transfer
	 * @param Request $request
	 */
	public function userActionTransfer(string $action, string $transfer, Request $request);
}