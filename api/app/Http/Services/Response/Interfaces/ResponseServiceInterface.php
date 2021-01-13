<?php


namespace App\Http\Services\Response\Interfaces;


/**
 * Interface ResponseServiceInterface
 * @package App\Services\Http\Response\Interfaces
 */
interface ResponseServiceInterface
{
    /**
     * Http status codes.
     */
    const STATUS_CODE_SUCCESS = 200;//Success
    const STATUS_CODE_UPDATE = 202;//update
    const STATUS_CODE_VALIDATION_ERROR = 422;//Validation error
    const STATUS_CODE_CONFLICT_ERROR = 409; //Exist Item error
    const STATUS_CODE_NOT_FOUND_ERROR_404 = 404;//Not found

    /**
     * @param $data
     * @return mixed
     */
    public function createSuccessResponseObject($data);

    /**
     * @param null $data
     * @return mixed
     */
    public function createUpdateResponseObject($data = null);

    /**
     * @param $status
     * @param $message
     * @return mixed
     */
    public function createFlowErrorResponseObject($status, $message);
}
