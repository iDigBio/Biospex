<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\Grid\JqGridEncoder;
use Request;
use Response;
use Throwable;

class ProjectGridController extends Controller
{
    /**
     * GridController constructor.
     */
    public function __construct(protected JqGridEncoder $grid) {}

    /**
     * Display a listing of the resource.
     */
    public function __invoke(Project $project): mixed
    {
        try {
            return $this->grid->encodeGridRequestedData(Request::all(), 'explore', $project->id);
        } catch (Throwable $throwable) {
            return Response::make($throwable->getMessage(), 404);
        }
    }
}
