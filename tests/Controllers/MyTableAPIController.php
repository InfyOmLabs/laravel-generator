<?php
/**
 * Company: InfyOm Technologies, Copyright 2019, All Rights Reserved.
 * Author: Vishal Ribdiya
 * Email: vishal.ribdiya@infyom.com
 * Date: 29-07-2019
 * Time: 11:45 AM.
 */

namespace Tests\Controllers;

use Illuminate\Http\Request;
use Tests\Repositories\MyTableNameRepository;

/**
 * Class MyModelAPIController.
 */
class MyTableAPIController extends AppBaseController
{
    /** @var MyTableNameRepository */
    private $myTableRepo;

    public function __construct(MyTableNameRepository $myTableNameRepository)
    {
        $this->myTableRepo = $myTableNameRepository;
    }

    public function index(Request $request)
    {
        $myTableRecords = $this->myTableRepo->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse($myTableRecords->toArray(), 'My Tables details retrieved successfully');
    }
}
