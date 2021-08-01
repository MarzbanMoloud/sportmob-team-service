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
	 *     path="/admin/persons/{person}/transfers",
	 *     tags={"Admin"},
	 *     @OA\Parameter(
	 *         name="person",
	 *         in="path",
	 *         description="personId",
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
	 *									"transferId": "eyJwbGF5ZXJJZCI6Ijk2N",
	 *									"person": {
	 *										"id": "96557439-49a0-3f4f-aa23-bab108e83fe7",
	 *										"name": "Dr. Albin Dickens I"
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
	 *     )
	 * )
	 * @param string $player
	 */
	public function index(string $player);

	/**
	 * @OA\Put(
	 *     path="/admin/persons/transfers/{transfer}",
	 *     tags={"Admin"},
	 *     @OA\Parameter(
	 *          name="transfer",
	 *          in="path",
	 *          required=true,
	 *          description="transferId",
	 *          example="eyJwbGF5ZXJJZCI6ImMy",
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
	 *     )
	 * )
	 * @param string $transfer
	 * @param Request $request
	 */
	public function update(string $transfer, Request $request);
}