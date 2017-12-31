<?php

namespace App\Http\Controllers\Api\V0;

use App\Interfaces\WeDigBioDashboard;
use App\Services\Model\WeDigBioDashboardService;
use Illuminate\Http\Request;
use App\Transformers\WeDigBioDashboardTransformer;

/**
 * WeDigBioDashboards representation.
 *
 * @Resource("WeDigBioDashboard", uri="/wedigbiodashboard")
 *
 * @package App\Http\Controllers\V1
 */

class WeDigBioDashboardsController extends ApiController
{

    /**
     * @var WeDigBioDashboard
     */
    private $weDigBioDashboardContract;

    /**
     * WeDigBioDashboardsController constructor.
     * @param WeDigBioDashboard $weDigBioDashboardContract
     */
    public function __construct(WeDigBioDashboard $weDigBioDashboardContract)
    {

        $this->weDigBioDashboardContract = $weDigBioDashboardContract;
    }

    /**
     * WeDigBioDashboard List.
     *
     * Show JSON representation of WeDigBioDashboard items.
     *
     * @Get("/{?start,rows,date_start,date_end,project_uuid,expedition_uuid}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("start", description="The start of the results to view.", default=0),
     *     @Parameter("rows", description="The amount of rows to return.", default=200),
     *     @Parameter("date_start", description="Return results >= to UTC timestamp."),
     *     @Parameter("date_end", description="Return results <= to UTC timestamp."),
     *     @Parameter("project_uuid", description="Biospex Project ID resource belongs to."),
     *     @Parameter("expedition_uuid", description="Biospex Expedition ID resource belongs to.")
     * })
     *
     * @param Request $request
     * @param WeDigBioDashboardService $service
     * @return mixed
     */
    public function index(Request $request, WeDigBioDashboardService $service)
    {
        $count = $service->listApiDashboardCount($request);
        $data = $service->listApiDashboard($request);

        $rows = $request->has('rows') ? (int) $request->input('rows') : 200;
        $rows = $rows > 500 ? 200 : $rows;
        $start = $request->has('start') ? (int) $request->input('start') : 0;

        $next = (int) ($start + $rows);
        $previous = (int) $start;
        $this->paginate($start, $previous, $next, $count);

        return $this->respondWithDashboardCollection($data, new WeDigBioDashboardTransformer(), 'items');
    }

    /**
     * Create a WeDigBioDashboard Item.
     *
     * Show JSON representation of WeDigBioDashboard items.
     *
     * @POST("/")
     * @Versions({"v1"})
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function create()
    {
        return $this->errorNotFound('This feature has not been implemented at this time.');
    }

    /**
     * WeDigBioDashboard List.
     *
     * Show JSON representation of WeDigBioDashboard items.
     *
     * @Get("/{guid}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("guid", description="GUID of specific resource item", required=true)
     * })
     *
     * @param WeDigBioDashboardService $service
     * @param $guid
     * @return mixed
     */
    public function show(WeDigBioDashboardService $service, $guid)
    {
        $result = $service->showApiDashboard($guid);

        return $result === null ?
            $this->errorNotFound() :
            $this->respondWithItem($result, new WeDigBioDashboardTransformer(), 'items');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param string $guid
     * @return \Illuminate\Support\Facades\Response
     */
    public function update(Request $request, $guid)
    {
        return $this->errorNotFound('This feature has not been implemented at this time.');
    }

    /**
     * Delete resource.
     *
     * @param $guid
     * @return \Illuminate\Support\Facades\Response
     */
    public function delete($guid)
    {
        return $this->errorNotFound('This feature has not been implemented at this time.');
    }
}
