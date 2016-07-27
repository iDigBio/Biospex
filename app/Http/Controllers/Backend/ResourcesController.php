<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Toastr;
use App\Http\Controllers\Controller;
use App\Http\Requests\ResourceFormRequest;
use App\Repositories\Contracts\Resource as Repo;
use App\Repositories\Contracts\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResourcesController extends Controller
{
    /**
     * @var Resource
     */
    private $resource;
    
    /**
     * @var User
     */
    private $user;

    /**
     * ResourcesController constructor.
     *
     * ResourcesController constructor.
     * @param Repo $resource
     * @param User $user
     */
    public function __construct(Repo $resource, User $user)
    {
        $this->resource = $resource;
        $this->user = $user;
    }

    /**
     * Show Faq list by category.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = $this->user->with(['profile'])->find($request->user()->id);
        $resources = $this->resource->orderBy(['order' => 'asc'])->get();
        $trashed = $this->resource->trashed();
        
        return view('backend.resources.index', compact('user', 'resources', 'trashed'));
    }

    /**
     * Redirect show route.
     * 
     * @return mixed
     */
    public function show()
    {
        return redirect()->route('admin.resources.index');
    }

    /**
     * Create route.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create()
    {
        return redirect()->route('admin.resources.index');
    }

    /**
     * Create Resource.
     *
     * @param ResourceFormRequest $request
     * @return mixed
     */
    public function store(ResourceFormRequest $request)
    {
        $resource = $this->resource->create($request->all());

        if (null !== $request->file('document'))
        {
            $filename = $resource->id . '-' . $request->file('document')->getClientOriginalName();
            Storage::disk('public')->put(
                'resources/' . $filename,
                file_get_contents($request->file('document')->getRealPath())
            );

            $resource = $this->resource->update(['document' => $filename], $resource->id);
        }

        $resource ? Toastr::success('Resource has been created successfully.', 'Resource Create') :
            Toastr::error('Resource could not be saved.', 'Resource Create');

        return redirect()->route('admin.resources.index');
    }

    /**
     * Edit Resource.
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function edit(Request $request, $id)
    {
        $user = $this->user->with(['profile'])->find($request->user()->id);
        $resources = $this->resource->all();
        $resource = $this->resource->find($id);
        $trashed = $this->resource->trashed();

        return view('backend.resources.index', compact('user', 'resources', 'resource', 'trashed'));
    }

    /**
     * Update Resource.
     *
     * @param ResourceFormRequest $request
     * @param $id
     * @return mixed
     */
    public function update(ResourceFormRequest $request, $id)
    {
        $resource = $this->resource->find($id);

        if (null !== $request->file('document'))
        {
            if ($resource->document)
            {
                Storage::disk('public')->delete('resources/' . $resource->document);
            }

            $resource->document = $resource->id . '-' . $request->file('document')->getClientOriginalName();
            Storage::disk('public')->put(
                'resources/' . $resource->document,
                file_get_contents($request->file('document')->getRealPath())
            );
        }

        $data = [
            'title' => $request->get('title'),
            'description' => $request->get('description'),
            'document' => $resource->document
        ];

        $resource = $this->resource->update($data, $id);

        $resource ? Toastr::success('Resource has been updated successfully.', 'Resource Update')
            : Toastr::error('Resource could not be updated.', 'Resource Update');

        return redirect()->route('admin.resources.index');
    }

    /**
     * Delete resource.
     *
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        $this->resource->update(['order' => 0], $id);
        $result = $this->resource->delete($id);

        $result ? Toastr::success('The resource has been deleted.', 'Resource Delete')
                : Toastr::error('Resource could not be deleted.', 'Resource Delete');

        return redirect()->route('admin.resources.index');
    }

    /**
     * Force delete soft deleted records.
     *
     * @param $id
     * @return mixed
     */
    public function trash($id)
    {
        $resource = $this->resource->withTrashed($id);
        Storage::disk('public')->delete('resources/' . $resource->document);

        $result = $this->resource->forceDelete($id);

        $result ? Toastr::success('Resource has been forcefully deleted.', 'Resource Destroy')
            : Toastr::error('Resource could not be forcefully deleted.', 'Resource Destroy');
        
        return redirect()->route('admin.resources.index');
    }

    /**
     * Update ordering on resources.
     *
     * @param Request $request
     * @param $id
     * @param $order
     */
    public function order(Request $request, $id, $order)
    {
        if ($request->ajax())
        {
            $this->resource->update(['order' => $order], $id);
        }
    }
}
