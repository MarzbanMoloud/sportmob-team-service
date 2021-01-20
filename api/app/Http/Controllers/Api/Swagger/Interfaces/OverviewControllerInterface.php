<?php


namespace App\Http\Controllers\Api\Swagger\Interfaces;


/**
 * Interface OverviewControllerInterface
 * @package App\Http\Controllers\Api\Swagger\Interfaces
 */
interface OverviewControllerInterface
{
	/**
	 * @OA\Get(
	 *     path="/{lang}/teams/overview/{team}",
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
	 *                                property="upcoming",
	 *                            type="object",
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
	 *     							  @OA\Property(
	 *                                    property="team",
	 *                                    type="object",
	 *	 									@OA\Property(
	 *                                    	property="home",
	 *                                    	type="object",
	 *                         					@OA\Property(
	 *                                        		property="id",
	 *                                        		type="string",
	 *                                        		example="1fa692ec-3fac-3858-83e3-2ed5b5bd2673"
	 *                                    		),
	 *                         					@OA\Property(
	 *                                        		property="name",
	 *                                        		type="object",
	 *     											@OA\Property(
	 *                                            		property="original",
	 *                                            		type="string",
	 *                                            		example="Blake Breitenberg DDS"
	 *                                        		),
	 *     											@OA\Property(
	 *                                            		property="short",
	 *                                            		type="string",
	 *                                            		example="Bla"
	 *                                        		)
	 *                                    		)
	 *                                		),
	 *	 									@OA\Property(
	 *                                    		property="away",
	 *                                    		type="object",
	 *                         					@OA\Property(
	 *                                        		property="id",
	 *                                        		type="string",
	 *                                        		example="b6f01b9b-610b-3b0b-bd7d-3fae92cf2d32"
	 *                                    		),
	 *                         					@OA\Property(
	 *                                        		property="name",
	 *                                        		type="object",
	 *     											@OA\Property(
	 *                                            		property="original",
	 *                                            		type="string",
	 *                                            		example="Sadye Kuphal"
	 *                                        		),
	 *     											@OA\Property(
	 *                                            		property="short",
	 *                                            		type="string",
	 *                                            		example="Kuphal"
	 *                                        		)
	 *                                    		)
	 *                                		),
	 *     							),
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
	 *                              property="finished",
	 *                                type="array",
	 *                                example={
	 *                                    {
	 *                                        "team": {
	 *                                            "id": "1fa692ec-3fac-3858-83e3-2ed5b5bd2673",
	 *                                            "name": {
	 *                                                "original": "Dr. Blake Breitenberg DDS",
	 *                                                "short": "Dr. Blake Breitenberg DDS"
	 *                                            }
	 *                                        },
	 *                                        "date": 1611055800,
	 *                                        "result": {
	 *                                            "home": 2,
	 *                                            "away": 2
	 *                                        }
	 *                                    }
	 *                                },
	 *								@OA\Items()
	 *                            )
	 *                      )
	 *                 )
	 *         )
	 *     )
	 * )
	 * @param string $team
	 */
	public function index(string $team);
}