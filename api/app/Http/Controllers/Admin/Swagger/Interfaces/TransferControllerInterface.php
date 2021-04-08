<?php


namespace App\Http\Controllers\Admin\Swagger\Interfaces;


use Illuminate\Http\Request;


/**
 * Interface TransferControllerInterface
 * @package App\Http\Controllers\Admin\Swagger\Interfaces
 */
interface TransferControllerInterface
{
	/**
	 * @OA\Get(
	 *     path="/admin/players/{player}/transfers",
	 *     tags={"Admin"},
	 *     @OA\Parameter(
	 *         name="player",
	 *         in="path",
	 *         description="playerId",
	 *         required=true,
	 *         example="96557439-49a0-3f4f-aa23-bab108e83fe7",
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
	 *     						example= {
	 *	 							{
	 *									"transferId": "eyJwbGF5ZXJJZCI6Ijk2NTU3NDM5LTQ5YTAtM2Y0Zi1hYTIzLWJhYjEwOGU4M2ZlNyIsInN0YXJ0RGF0ZSI6IjIwMTktMDQtMDFUMDA6MDA6MDArMDA6MDAifQ==",
	 *									"player": {
	 *										"id": "96557439-49a0-3f4f-aa23-bab108e83fe7",
	 *										"name": "Dr. Albin Dickens I",
	 *										"position": "defender"
	 *									},
	 *									"team": {
	 *										"to": {
	 *											"id": "dafb67f1-d45e-345f-a996-ff94f57ed30f",
	 *											"name": "Team B"
	 *										},
	 * 										"from": {
	 *											"id": "6e88012b-da84-3db6-bafe-cb346a21711c",
	 *											"name": "Team B"
	 *										}
	 *									},
	 *									"startDate": 1554076800,
	 *									"endDate": 1612694306,
	 *									"marketValue": "200",
	 *									"announcedDate": 1612694306,
	 *									"contractDate": 1612694306,
	 *									"type": "transferred",
	 *									"season": "2019-2020"
	 *								}
	 *     						},
	 *     						@OA\Items()
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
	 * @param string $player
	 */
	public function index(string $player);

	/**
	 * @OA\Put(
	 *     path="/admin/players/transfers/{transfer}",
	 *     tags={"Admin"},
	 *     @OA\Parameter(
	 *          name="transfer",
	 *          in="path",
	 *          required=true,
	 *          description="transferId",
	 *          example="eyJwbGF5ZXJJZCI6ImMyZmViNDk5LTJkMTctMzI1ZC1hNzIxLTE2YzE1YTJiMjBmMCIsInN0YXJ0RGF0ZSI6IjIwMTktMDMtMDFUMDA6MDA6MDArMDA6MDAifQ==",
	 *          @OA\Schema(type="string")
	 *     ),
	 *     @OA\RequestBody(
	 *         @OA\MediaType(
	 *             mediaType="application/json",
	 *             @OA\Schema(
	 *                 required={"marketValue", "announcedDate", "contractDate"},
	 *                 @OA\Property(
	 *                     property="marketValue",
	 *                     type="object",
	 *                     example="12.33$"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="announcedDate",
	 *                     type="object",
	 *                     example=1612693084
	 *                 ),
	 *                 @OA\Property(
	 *                     property="contractDate",
	 *                     type="object",
	 *                     example=1612693093
	 *                 )
	 *             ),
	 *         )
	 *     ),
	 *     @OA\Response(
	 *          response=204,
	 *          description="When the update is successful"
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
	 * @param string $transfer
	 * @param Request $request
	 */
	public function update(string $transfer, Request $request);
}