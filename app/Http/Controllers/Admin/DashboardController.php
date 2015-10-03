<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

/**
 * Class DashboardController
 *
 * @Resource("home", only={"index", "project/{slug}"})
 * @Controller(prefix="locale")
 * @Middleware("localizationRedirect")
 * @Middleware("localeSessionRedirect")
 * @package App\Http\Controllers
 */
class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * Get("/", as="home")
     *
     * @return Response
     */
    public function index()
    {
        return view('admin.app');
    }
}
