<?php


namespace App\Http\Controllers\Api\Swagger\Interfaces;


/**
 * Interface TeamControllerInterface
 * @package App\Http\Controllers\Api\Swagger\Interfaces
 */
interface TeamControllerInterface
{
	/**
	 * @OA\Get(
	 *     path="/tm/{lang}/overview/{team}",
	 *     tags={"Overview"},
	 *     @OA\Parameter(
	 *         name="lang",
	 *         in="path",
	 *         description="language",
	 *         required=true,
	 *         example="en",
	 *         @OA\Schema(type="string")
	 *     ),
	 *     @OA\Parameter(
	 *         name="team",
	 *         in="path",
	 *         description="teamId",
	 *         required=true,
	 *         example="e63ed22f-ebf7-3e2f-92f5-49f0c7c8dafc",
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
	 *                              property="nextMatch",
	 *                            	type="object",
	 *     							@OA\Property(
	 *                                    property="id",
	 *                                    type="string",
	 *                                    example="bb94b02d-a4cd-30f9-a5d9-697cc0142458"
	 *                              ),
	 *	 							@OA\Property(
	 *                                    property="competition",
	 *                                    type="object",
	 *                         			  @OA\Property(
	 *                                        property="name",
	 *                                        type="string",
	 *                                        example="PrimerLeague"
	 *                                    ),
	 *                         			  @OA\Property(
	 *                                        property="id",
	 *                                        type="string",
	 *                                        example="tga692ec-3fac-3858-83e3-2ed5b5bd26rf"
	 *                                    )
	 *                                ),
	 *	 							  @OA\Property(
	 *                                    	property="homeTeam",
	 *                                   	type="object",
	 *                         				@OA\Property(
	 *                                        		property="id",
	 *                                        		type="string",
	 *                                        		example="1fa692ec-3fac-3858-83e3-2ed5b5bd2673"
	 *                                    	),
	 *                         				@OA\Property(
	 *                                        	property="name",
	 *                                        	type="object",
	 *     										@OA\Property(
	 *                                            	property="full",
	 *                                            	type="string",
	 *                                            	example="Blake Breitenberg DDS"
	 *                                        	),
	 *     										@OA\Property(
	 *                                            	property="short",
	 *                                            	type="string",
	 *                                            	example="Bla"
	 *                                        	),
	 *     										@OA\Property(
	 *                                            	property="official",
	 *                                            	type="string",
	 *                                            	example="Blake Breitenberg DDS Team"
	 *                                        	)
	 *                                    	)
	 *                                	),
	 *	 								@OA\Property(
	 *                                    	property="awayTeam",
	 *                                    	type="object",
	 *                         				@OA\Property(
	 *                                        	property="id",
	 *                                        	type="string",
	 *                                        	example="b6f01b9b-610b-3b0b-bd7d-3fae92cf2d32"
	 *                                    	),
	 *                         				@OA\Property(
	 *                                        	property="name",
	 *                                        	type="object",
	 *     										@OA\Property(
	 *                                            	property="full",
	 *                                            	type="string",
	 *                                            	example="Sadye Kuphal"
	 *                                        	),
	 *     										@OA\Property(
	 *                                            	property="short",
	 *                                            	type="string",
	 *                                            	example="Kuphal"
	 *                                        	),
	 *     										@OA\Property(
	 *                                            	property="official",
	 *                                            	type="string",
	 *                                            	example="Sadye Kuphal"
	 *                                        	)
	 *                                    	)
	 *                                	),
	 *     							@OA\Property(
	 *                                    property="date",
	 *                                    type="number",
	 *                                    example=1611315000
	 *                              ),
	 *     							@OA\Property(
	 *                                    property="coverage",
	 *                                    type="string",
	 *                                    example="low"
	 *                              )
	 *                          ),
	 *     						@OA\Property(
	 *                              property="teamForm",
	 *                                type="array",
	 *                                example= {
	 *									"team": {
	 *										"id": "c781de0f-83a7-3f8c-be3a-90e0faa1a654",
	 *										"name": {
	 *											"full": "Ezekiel Block",
	 *											"short": "Ezekiel Block",
	 *											"official": "Ezekiel Block"
	 *										}
	 *									},
	 *									"form": {
	 *										{
	 *											"id": "a6ff3a3c-b66a-333c-9e0f-06e9f01d3f7f",
	 *											"homeTeam": {
	 *												"id": "c781de0f-83a7-3f8c-be3a-90e0faa1a654",
	 *												"name": {
	 *													"full": "Ezekiel Block",
	 *													"short": "Ezekiel Block"
	 *												}
	 *											},
	 *											"awayTeam": {
	 *												"id": "2143fdd0-4871-3777-9eaf-885f6d98bc36",
	 *												"name": {
	 *													"full": "Ruthie Zieme",
	 *													"short": "Ruthie Zieme"
	 *												}
	 *											},
	 *											"competition": {
	 *												"id": "0cb4a362-a5dc-3b6e-b0b5-cd0a6581df8b",
	 *												"name": "Marquis Daniel"
	 *											},
	 *											"date": 1611055800,
	 *											"coverage": "low",
	 *											"result": {
	 *												"total": {
	 *													"home": 2,
	 *													"away": 2
	 *												},
	 *												"penalty": {
	 *													"home": 1,
	 *													"away": 1
	 *												}
	 *											}
	 *										}
	 *	 								}},
	 *								@OA\Items()
	 *                            )
	 *                      )
	 *                 )
	 *         )
	 *     )
	 * )
	 * @param string $team
	 */
	public function overview(string $team);

	/**
	 * @OA\Get(
	 *     path="/tm/{lang}/favorite/{team}",
	 *     tags={"Favorite"},
	 *     @OA\Parameter(
	 *         name="lang",
	 *         in="path",
	 *         description="language",
	 *         required=true,
	 *         example="en",
	 *         @OA\Schema(type="string")
	 *     ),
	 *     @OA\Parameter(
	 *         name="team",
	 *         in="path",
	 *         description="teamId",
	 *         required=true,
	 *         example="e63ed22f-ebf7-3e2f-92f5-49f0c7c8dafc",
	 *         @OA\Schema(type="string")
	 *     ),
	 *     @OA\Response(
	 *          response=200,
	 *          description="when response is OK",
	 *          @OA\MediaType(
	 *             mediaType="application/json",
	 *             @OA\Schema(
	 *                 type="object",
	 *                 @OA\Property(
	 *                     property="links",
	 *                     type="array",
	 *                     @OA\Items()
	 *                 ),
	 *                 @OA\Property(
	 *                     property="data",
	 *                     type="object",
	 *     				   @OA\Property(
	 *                              property="nextMatch",
	 *                            	type="object",
	 *     							@OA\Property(
	 *                                  property="id",
	 *                                  type="string",
	 *                                  example="212f51e6-b803-3eac-bfe4-a451d78fa94f"
	 *                              ),
	 *	 							@OA\Property(
	 *                                  property="homeTeam",
	 *                                  type="object",
	 *                         			@OA\Property(
	 *                                       property="id",
	 *                                       type="string",
	 *                                       example="1fa692ec-3fac-3858-83e3-2ed5b5bd2673"
	 *                                   ),
	 *                         			 @OA\Property(
	 *                                        property="name",
	 *                                        type="object",
	 *     									  @OA\Property(
	 *                                            property="full",
	 *                                            type="string",
	 *                                            example="Blake Breitenberg DDS"
	 *                                        ),
	 *     									  @OA\Property(
	 *                                            property="short",
	 *                                            type="string",
	 *                                            example="Bla"
	 *                                        ),
	 *     									  @OA\Property(
	 *                                            property="official",
	 *                                            type="string",
	 *                                            example="Blake Breitenberg DDS"
	 *                                        )
	 *                                   )
	 *                             ),
	 *	 						   @OA\Property(
	 *                                 property="awayTeam",
	 *                                 type="object",
	 *                         		   @OA\Property(
	 *                                       property="id",
	 *                                       type="string",
	 *                                       example="b6f01b9b-610b-3b0b-bd7d-3fae92cf2d32"
	 *                                 ),
	 *                         		   @OA\Property(
	 *                                       property="name",
	 *                                       type="object",
	 *     									 @OA\Property(
	 *                                           property="full",
	 *                                           type="string",
	 *                                           example="Sadye Kuphal"
	 *                                        ),
	 *     									  @OA\Property(
	 *                                            property="short",
	 *                                            type="string",
	 *                                            example="Kuphal"
	 *                                        ),
	 *     									  @OA\Property(
	 *                                            property="official",
	 *                                            type="string",
	 *                                            example="Sadye Kuphal"
	 *                                        )
	 *                                 )
	 *                             ),
	 *     						   @OA\Property(
	 *                                 property="date",
	 *                                 type="number",
	 *                                 example=1611315000
	 *                             )
	 *                          ),
	 *     						@OA\Property(
	 *                                property="previousMatch",
	 *								  type="object",
	 *     							  @OA\Property(
	 *                                    property="team",
	 *                                    type="object",
	 *                                    @OA\Property(
	 *                              			property="id",
	 *											type="string",
	 *     										example="6ad8f80e-bc4b-390e-b68c-82c50dca9243"
	 * 									  ),
	 *                                    @OA\Property(
	 *                              			property="name",
	 *											type="object",
	 *                                    		@OA\Property(
	 *                              				property="full",
	 *												type="string",
	 *     											example="Sylvia1"
	 * 									  		),
	 *                                    		@OA\Property(
	 *                              				property="short",
	 *												type="string",
	 *     											example="Syl1"
	 * 									  		),
	 *                                    		@OA\Property(
	 *                              				property="official",
	 *												type="string",
	 *     											example="Sylvia1"
	 * 									  		)
	 * 									 ),
	 *                               ),
	 *     							 @OA\Property(
	 *                                    property="form",
	 *                                    type="object",
	 *                                    @OA\Property(
	 *                              			property="id",
	 *											type="string",
	 *     										example="6ad8f80e-bc4b-390e-b68c-82c50dca9211"
	 * 									  ),
	 *                                    @OA\Property(
	 *                              			property="homeTeam",
	 *											type="object",
	 *                                    		@OA\Property(
	 *                              				property="id",
	 *												type="string",
	 *     											example="5551f69f-05d4-394c-bf8f-3d1cc5d725e6"
	 * 									  		),
	 *                                    		@OA\Property(
	 *                              				property="name",
	 *												type="object",
	 *     											@OA\Property(
	 *                              					property="full",
	 *													type="string",
	 *     												example="Samara Keebler"
	 * 									  			),
	 *     											@OA\Property(
	 *                              					property="short",
	 *													type="string",
	 *     												example="Samara Keebler"
	 * 									  			)
	 * 									  		),
	 * 									  ),
	 *                                    @OA\Property(
	 *                              			property="awayTeam",
	 *											type="object",
	 *                                    		@OA\Property(
	 *                              				property="id",
	 *												type="string",
	 *     											example="1205ceb2-e574-35e4-a789-4094317fb95e"
	 * 									  		),
	 *                                    		@OA\Property(
	 *                              				property="name",
	 *												type="object",
	 *     											@OA\Property(
	 *                              					property="full",
	 *													type="string",
	 *     												example="Otha Price"
	 * 									  			),
	 *     											@OA\Property(
	 *                              					property="short",
	 *													type="string",
	 *     												example="Otha Price"
	 * 									  			)
	 * 									  		),
	 * 									  ),
	 *      							  @OA\Property(
	 *                              		property="result",
	 *										type="object",
	 *     									@OA\Property(
	 *                              			property="score",
	 *											type="object",
	 *     										@OA\Property(
	 *                              				property="home",
	 *												type="number",
	 *     											example=1
	 * 											),
	 *     										@OA\Property(
	 *                              				property="away",
	 *												type="number",
	 *     											example=1
	 * 											)
	 * 										),
	 *     									@OA\Property(
	 *                              			property="penalty",
	 *											type="object",
	 *     										@OA\Property(
	 *                              				property="home",
	 *												type="number",
	 *     											example=1
	 * 											),
	 *     										@OA\Property(
	 *                              				property="away",
	 *												type="number",
	 *     											example=1
	 * 											)
	 * 										)
	 * 									),
	 *      							@OA\Property(
	 *                              		property="date",
	 *										type="number",
	 *     									example=1611055800
	 * 									),
	 *      							@OA\Property(
	 *                              		property="coverage",
	 *										type="string",
	 *     									example="low"
	 * 									),
	 *      							@OA\Property(
	 *                              		property="competition",
	 *										type="object",
	 *     									@OA\Property(
	 *                              			property="id",
	 *											type="string",
	 *     										example="699547fe-f848-3c23-99b4-fe3646aa3a0b"
	 * 										),
	 *     									@OA\Property(
	 *                              			property="name",
	 *											type="string",
	 *     										example="Waino Littel"
	 * 										),
	 * 									),
	 *                              ),
	 *                          ),
	 *     						@OA\Property(
	 *                              property="teamFormSymbols",
	 *								type="object",
	 *     							@OA\Property(
	 *                                    property="team",
	 *                                    type="object",
	 *                                    @OA\Property(
	 *                              			property="id",
	 *											type="string",
	 *     										example="6ad8f80e-bc4b-390e-b68c-82c50dca9200"
	 * 									  ),
	 *                                    @OA\Property(
	 *                              			property="name",
	 *											type="object",
	 *                                    		@OA\Property(
	 *                              				property="full",
	 *												type="string",
	 *     											example="Sylvia3"
	 * 									  		),
	 *                                    		@OA\Property(
	 *                              				property="short",
	 *												type="string",
	 *     											example="Syl3"
	 * 									  		),
	 *                                    		@OA\Property(
	 *                              				property="official",
	 *												type="string",
	 *     											example="Sylvia3"
	 * 									  		)
	 * 									  ),
	 *                              ),
	 *     							@OA\Property(
	 *                              	property="form",
	 *     								type="array",
	 *									example = {"D", "D", "L","D", "W"},
	 *     								@OA\Items()
	 * 								)
	 * 							)
	 *                 )
	 *             )
	 *         )
	 *    )
	 * )
	 * @param string $team
	 */
	public function favorite(string $team);
}