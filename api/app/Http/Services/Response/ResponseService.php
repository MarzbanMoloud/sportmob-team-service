<?php


namespace App\Http\Services\Response;


use App\Http\Services\Response\Interfaces\ResponseServiceInterface;


/**
 * Class ResponseService
 * @package App\Services\Http\Response
 */
class ResponseService implements ResponseServiceInterface
{
    /**
     * @param $data
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function createSuccessResponseObject($data)
    {
        return response()->json($data, self::STATUS_CODE_SUCCESS);
    }

    public function createUpdateResponseObject($data = null)
    {
        return response()->json($data, self::STATUS_CODE_UPDATE);
    }

    /**
     * @param $status
     * @param $message
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function createFlowErrorResponseObject($status, $message)
    {
        $content = ['error' => $message];
        return response()->json($content, $status);
    }
}
