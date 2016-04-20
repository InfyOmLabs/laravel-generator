<?php

namespace InfyOm\Generator\Controller;

use App\Http\Controllers\Controller as Controller;
use InfyOm\Generator\Utils\ResponseUtil;
use Response;

// @TODO This class need to be removed once we release our first version.
// Because in new version we are publishing this controller.
// Its just there to do not break application of existing users.

class AppBaseController extends Controller
{
    public function sendResponse($result, $message)
    {
        return Response::json(ResponseUtil::makeResponse($message, $result));
    }
}
