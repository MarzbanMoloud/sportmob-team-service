<?php


namespace App\Http\Requests;


use Illuminate\Http\Request;
use Laravel\Lumen\Routing\ProvidesConvenienceMethods;


/**
 * Class PlayerTransferRequest
 * @package App\Http\Requests
 */
class PlayerTransferRequest
{
	use ProvidesConvenienceMethods;

	/**
	 * @param Request $request
	 * @throws \Illuminate\Validation\ValidationException
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