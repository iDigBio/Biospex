<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\Faq;

class FaqsController extends Controller
{

    /**
     * @var Faq
     */
    private $faq;

    /**
     * FaqController constructor.
     *
     * @param Faq $faq
     */
    public function __construct(Faq $faq)
    {

        $this->faq = $faq;
    }

    /**
     * Show Faq list by category
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $faqs = $this->faq->with(['categories'])->groupBy('category_id')->get();

        return view('backend.faq.index');
    }

    /**
     * 
     */
    public function create()
    {
        
    }

    /**
     * 
     */
    public function store()
    {
        
    }

    /**
     * 
     */
    public function show()
    {

    }

    /**
     * 
     */
    public function edit()
    {
        
    }

    /**
     * 
     */
    public function update()
    {
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete()
    {
        
    }

}