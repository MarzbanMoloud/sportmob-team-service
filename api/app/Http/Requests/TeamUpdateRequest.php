<?php


namespace App\Http\Requests;


use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Routing\ProvidesConvenienceMethods;


/**
 * Class TeamUpdateRequest
 * @package App\Http\Requests
 */
class TeamUpdateRequest
{
	use ProvidesConvenienceMethods;

	/**
	 * @param Request $request
	 * @throws ValidationException
	 */
	public function validation(Request $request)
	{
		$this->validate($request, [
			'name.original' => 'required|string|regex:' . "/^[0-9a-zA-Z\.\- ]+$/",
			'name.official' => 'required|string|regex:' . "/^[0-9a-zA-Z\.\- ]+$/",
			'name.short' => 'required|string|regex:' . "/^[0-9a-zA-Z\.\- ]+$/",
		]);
	}
}