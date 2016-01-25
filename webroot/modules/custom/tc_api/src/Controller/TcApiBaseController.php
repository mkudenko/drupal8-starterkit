<?php namespace Drupal\tc_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class TcApiBaseController.
 */
class TcApiBaseController extends ControllerBase
{

    /**
     * Returns a 200 response.
     *
     * @param array $data
     *
     * @return JsonResponse
     */
    protected function jsonSuccess($data)
    {
        return $this->jsonResponse($data);
    }

    /**
     * Generates a 400 response with an error message.
     *
     * @param string $message
     *
     * @return JsonResponse
     */
    protected function jsonInvalidRequest($message)
    {
        $data = [
            'message' => $message,
        ];

        return $this->jsonResponse($data, 400);
    }

    /**
     * @param mixed $data    The response data
     * @param int   $status  The response status code
     * @param array $headers An array of response headers
     *
     * @return JsonResponse
     */
    protected function jsonResponse($data, $statusCode = 200, $headers = [])
    {
        return new JsonResponse($data, $statusCode, $headers);
    }

}
