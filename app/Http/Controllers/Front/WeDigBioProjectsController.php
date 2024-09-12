<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\WeDigBioEventDate;
use App\Services\Models\WeDigBioEventDateModelService;
use Illuminate\Support\Facades\Response;

class WeDigBioProjectsController extends Controller
{
    public function __construct(protected WeDigBioEventDateModelService $weDigBioEventDateModelService) {}

    /**
     * Returns titles of projects that have transcriptions from WeDigBio.
     *
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function __invoke(?WeDigBioEventDate $event = null)
    {
        if (! \Request::ajax()) {
            return response()->json(['html' => 'Request must be ajax.']);
        }

        return Response::json($this->weDigBioEventDateModelService->getProjectsForWeDigBioRateChart($event));
    }
}
