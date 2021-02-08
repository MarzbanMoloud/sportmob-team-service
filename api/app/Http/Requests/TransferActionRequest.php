<?php


namespace App\Http\Requests;


use Illuminate\Http\Request;
use Laravel\Lumen\Routing\ProvidesConvenienceMethods;


/**
 * Class TransferActionRequest
 * @package App\Http\Requests
 */
class TransferActionRequest
{
	use ProvidesConvenienceMethods;

	/**
	 * @param Request $request
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function validation(Request $request)
	{
		$this->validate($request, [
			'userId' => 'required|string'
		]);
	}
}