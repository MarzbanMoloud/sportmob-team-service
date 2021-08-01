<?php


namespace App\Http\Requests;


use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Routing\ProvidesConvenienceMethods;


/**
 * Class PersonTransferRequest
 * @package App\Http\Requests
 */
class PersonTransferRequest
{
	use ProvidesConvenienceMethods;

	/**
	 * @param Request $request
	 * @throws ValidationException
	 */
	public function validation(Request $request)
	{
		$this->validate($request, [
			'announcedDate' => 'required|numeric',
			'contractDate' => 'required|numeric',
			'marketValue' => 'required|regex:' . "/^[0-9]+[0-9.$ ]+$/"
		]);
	}
}