<?php


namespace App\Http\Controllers\Api\Swagger\Interfaces;


/**
 * Interface TrophyControllerInterface
 * @package App\Http\Controllers\Api\Swagger\Interfaces
 */
interface TrophyControllerInterface
{
	/**
	 * @OA\Get(
	 *     path="/{lang}/trophies/team/{team}",
	 *     tags={"Trophy"},
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
	 *     						example= {{
	 *								"competition": {
	 *									"id": "51a45d69-a854-3acf-8660-13184c93e2cd",
	 *									"name": "PremierLeague_2"
	 *								},
	 *								"trophies": {
	 *									{
	 *     									"tournament": {
	 *											"id": "6b025699-b528-33b0-871e-be53d75c0de6",
	 *											"season": "2017/2018",
	 *	 									},
	 *										"winnerTeam": {
	 *											"id": "672ead46-a66c-30e1-b6f8-f577c9c28332",
	 *     										"name": {
	 *												"full": "Ervin Block",
	 *     											"short": "Ervin .B",
	 *     											"official": "Ervin Block"
	 *	 										}
	 *										},
	 *										"runnerUpTeam": {
	 *											"id": "eaa6c82e-9d83-30d9-89e2-860a2dda4503",
	 *     										"name": {
	 *												"full": "Manchester city",
	 *     											"short": "Manchester",
	 *     											"official": "Manchester city"
	 *	 										}
	 *										}
	 *									}
	 *								}
	 *     						}},
	 *        					@OA\Items()
	 *                     ),
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
	 *                      example="TM-404 or TM-001"
	 *                  )
	 *             )
	 *         )
	 *     )
	 * )
	 * @param string $team
	 */
	public function trophiesByTeam(string $team);

	/**
	 * @OA\Get(
	 *     path="/{lang}/trophies/competition/{competition}",
	 *     tags={"Trophy"},
	 *     @OA\Parameter(
	 *         name="competition",
	 *         in="path",
	 *         description="competitionId",
	 *         required=true,
	 *         example="66548f07-1015-3d11-9ac8-46a695acfecd",
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
	 *                            example= {
	 *	 							{
	 *     								"tournament": {
	 *										"season": "2017/2018",
	 *										"id": "007f646e-b7f7-334e-963f-2df2153a588e",
	 *	 								},
	 *									"runnerUpTeam": {
	 *										"id": "30904db2-ee74-3559-81e4-7ea6ee6bbc91",
	 *     									"name": {
	 *											"full": "Manchester city",
	 *											"short": "Manchester",
	 *											"official": "Manchester city"
	 *	 									},
	 *									},
	 *									"winnerTeam": {
	 *										"id": "fd521439-1f40-3e10-a2b3-a396c50e4450",
	 *     									"name": {
	 *											"full": "Maximilian Ebert",
	 *											"short": "Maximilian",
	 *											"official": "Maximilian Ebert"
	 *	 									},
	 *									}
	 *								}
	 *	 						},
	 *        					@OA\Items()
	 *                     ),
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
	 *                      example="TM-404 or TM-001"
	 *                  )
	 *             )
	 *         )
	 *     )
	 * )
	 * @param string $competition
	 */
	public function trophiesByCompetition(string $competition);
}