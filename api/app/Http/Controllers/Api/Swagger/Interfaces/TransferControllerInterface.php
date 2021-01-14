<?php


namespace App\Http\Controllers\Api\Swagger\Interfaces;


/**
 * Interface TransferControllerInterface
 * @package App\Http\Controllers\Api\Swagger\Interfaces
 */
interface TransferControllerInterface
{
	/**
	 * @OA\Get(
	 *     path="/{lang}/teams/transfers/team/{team}/{season}",
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
	 *         required=false,
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
	 *                          type="object",
	 *         					@OA\Property(
	 *                          	property="transfers",
	 *                          	type="object",
	 *        						@OA\Property(
	 *                              	property="transferId",
	 *                              	type="string",
	 *                              	example="eyJwbGF5ZXJJZCI6ImY3YTRjNGIwLTYzZDEtM2JkZC04YmZiLTI5M2NmZWJlNDdiYyIsInN0YXJ0RGF0ZSI6eyJkYXRlIjoiMjAyMC0wMS0wMSAwMDowMDowMC4wMDAwMDAiLCJ0aW1lem9uZV90eXBlIjoxLCJ0aW1lem9uZSI6IiswMDowMCJ9fQ=="
	 *                           	),
	 *                         		@OA\Property(
	 *                              	property="player",
	 *                              	type="object",
	 *                              	@OA\Property(
	 *                                  	property="id",
	 *                                  	type="string",
	 *                                  	example="4c6f05da-5004-3aed-9cb3-eb9a0d55e591"
	 *                              	),
	 *                              	@OA\Property(
	 *                                  	property="name",
	 *                                  	type="string",
	 *                                  	example="Adonis Breitenberg"
	 *                              	),
	 *                              	@OA\Property(
	 *                                  	property="position",
	 *                                  	type="string",
	 *                                  	example="defender"
	 *                              	)
	 *                          	),
	 *                         		@OA\Property(
	 *                              	property="team",
	 *                              	type="object",
	 *                              	@OA\Property(
	 *                                  	property="to",
	 *                                  	type="object",
	 *                              		@OA\Property(
	 *                                  		property="id",
	 *                                  		type="string",
	 *                                  		example="723a7d16-4e1d-3899-bef1-38680a67f11a"
	 *                              		),
	 *                              		@OA\Property(
	 *                                  		property="name",
	 *                                  		type="string",
	 *                                  		example="Barcelona"
	 *                              		)
	 *                              	),
	 *                              	@OA\Property(
	 *                                  	property="from",
	 *                                  	type="object",
	 *                              		@OA\Property(
	 *                                  		property="id",
	 *                                  		type="string",
	 *                                  		example="fafe4497-b23e-3d7f-95db-da8b7da43ecc"
	 *                              		),
	 *                              		@OA\Property(
	 *                                  		property="name",
	 *                                  		type="string",
	 *                                  		example="real madrid"
	 *                              		)
	 *                              	)
	 *                          	),
	 *                              @OA\Property(
	 *                                  property="marketValue",
	 *                                  type="string",
	 *                                  example="200"
	 *                              ),
	 *                              @OA\Property(
	 *                                  property="startDate",
	 *                                  type="string",
	 *                                  example="1577836800"
	 *                              ),
	 *                              @OA\Property(
	 *                                  property="endDate",
	 *                                  type="string",
	 *                                  example="1610431027"
	 *                              ),
	 *                              @OA\Property(
	 *                                  property="announcedDate",
	 *                                  type="string",
	 *                                  example="1610431027"
	 *                              ),
	 *                              @OA\Property(
	 *                                  property="contractDate",
	 *                                  type="string",
	 *                                  example="1610431027"
	 *                              ),
	 *                              @OA\Property(
	 *                                  property="type",
	 *                                  type="string",
	 *                                  example="'transfer' or  'freeTransfer' or 'backFromLoan' or 'loan' or 'unknown' or 'playerSwap' or 'trial'"
	 *                              ),
	 *                              @OA\Property(
	 *                                  property="like",
	 *                                  type="integer",
	 *                                  example=0
	 *                              ),
	 *                              @OA\Property(
	 *                                  property="dislike",
	 *                                  type="integer",
	 *                                  example=1
	 *                              ),
	 *                              @OA\Property(
	 *                                  property="season",
	 *                                  type="string",
	 *                                  example="2019-2020"
	 *                              )
	 *                         ),
	 *     					   @OA\Property(
	 *                             property="seasons",
	 *                             type="array",
	 *                             example={"2019-2020", "2019-2020"},
	 *     						   @OA\Items()
	 *                         )
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
	 *                      example="TM-404 or TM-001"
	 *                  )
	 *             )
	 *         )
	 *     )
	 * )
	 * @param string $team
	 * @param string|null $season
	 */
	public function listByTeam(string $team, ?string $season = null);

	/**
	 * @OA\Get(
	 *     path="/{lang}/teams/transfers/player/{player}",
	 *     tags={"Transfer"},
	 *     @OA\Parameter(
	 *         name="player",
	 *         in="path",
	 *         description="playerId",
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
	 *                          type="object",
	 *        					@OA\Property(
	 *                              property="transferId",
	 *                              type="string",
	 *                              example="eyJwbGF5ZXJJZCI6ImY3YTRjNGIwLTYzZDEtM2JkZC04YmZiLTI5M2NmZWJlNDdiYyIsInN0YXJ0RGF0ZSI6eyJkYXRlIjoiMjAyMC0wMS0wMSAwMDowMDowMC4wMDAwMDAiLCJ0aW1lem9uZV90eXBlIjoxLCJ0aW1lem9uZSI6IiswMDowMCJ9fQ=="
	 *                           ),
	 *                         		@OA\Property(
	 *                                property="player",
	 *                                type="object",
	 *                              	@OA\Property(
	 *                                    property="id",
	 *                                    type="string",
	 *                                    example="4c6f05da-5004-3aed-9cb3-eb9a0d55e591"
	 *                                ),
	 *                              	@OA\Property(
	 *                                    property="name",
	 *                                    type="string",
	 *                                    example="Adonis Breitenberg"
	 *                                ),
	 *                              	@OA\Property(
	 *                                    property="position",
	 *                                    type="string",
	 *                                    example="defender"
	 *                                )
	 *                            ),
	 *                         		@OA\Property(
	 *                                property="team",
	 *                                type="object",
	 *                              	@OA\Property(
	 *                                    property="to",
	 *                                    type="object",
	 *                              		@OA\Property(
	 *                                        property="id",
	 *                                        type="string",
	 *                                        example="723a7d16-4e1d-3899-bef1-38680a67f11a"
	 *                                    ),
	 *                              		@OA\Property(
	 *                                        property="name",
	 *                                        type="string",
	 *                                        example="Barcelona"
	 *                                    )
	 *                                ),
	 *                              	@OA\Property(
	 *                                    property="from",
	 *                                    type="object",
	 *                              		@OA\Property(
	 *                                        property="id",
	 *                                        type="string",
	 *                                        example="fafe4497-b23e-3d7f-95db-da8b7da43ecc"
	 *                                    ),
	 *                              		@OA\Property(
	 *                                        property="name",
	 *                                        type="string",
	 *                                        example="real madrid"
	 *                                    )
	 *                                )
	 *                            ),
	 *                              @OA\Property(
	 *                                  property="marketValue",
	 *                                  type="string",
	 *                                  example="200"
	 *                              ),
	 *                              @OA\Property(
	 *                                  property="startDate",
	 *                                  type="string",
	 *                                  example="1577836800"
	 *                              ),
	 *                              @OA\Property(
	 *                                  property="endDate",
	 *                                  type="string",
	 *                                  example="1610431027"
	 *                              ),
	 *                              @OA\Property(
	 *                                  property="announcedDate",
	 *                                  type="string",
	 *                                  example="1610431027"
	 *                              ),
	 *                              @OA\Property(
	 *                                  property="contractDate",
	 *                                  type="string",
	 *                                  example="1610431027"
	 *                              ),
	 *                              @OA\Property(
	 *                                  property="type",
	 *                                  type="string",
	 *                                  example="'transfer' or  'freeTransfer' or 'backFromLoan' or 'loan' or 'unknown' or 'playerSwap' or 'trial'"
	 *                              ),
	 *                              @OA\Property(
	 *                                  property="like",
	 *                                  type="integer",
	 *                                  example=0
	 *                              ),
	 *                              @OA\Property(
	 *                                  property="dislike",
	 *                                  type="integer",
	 *                                  example=1
	 *                              ),
	 *                              @OA\Property(
	 *                                  property="season",
	 *                                  type="string",
	 *                                  example="2019-2020"
	 *                              )
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
	 *                  )
	 *             )
	 *         )
	 *     )
	 * )
	 * @param string $player
	 */
	public function listByPlayer(string $player);

	public function userActionTransfer(string $action, string $user, string $transfer);
}