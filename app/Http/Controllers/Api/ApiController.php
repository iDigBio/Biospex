<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

/**
 * Class HomeController
 *
 * @Resource("home", only={"index", "project/{slug}"})
 * @Controller(prefix="locale")
 * @Middleware("localizationRedirect")
 * @Middleware("localeSessionRedirect")
 * @package App\Http\Controllers
 */
class ApiController extends Controller
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
        return "admin";
    }
}
