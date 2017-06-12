<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Toastr;
use App\Http\Controllers\Controller;
use App\Http\Requests\ResourceFormRequest;
use App\Repositories\Contracts\ResourceContract;
use App\Repositories\Contracts\UserContract;
use Illuminate\Support\Facades\Storage;

class ResourcesController extends Controller
{
    /**
     * @var ResourceContract
     */
    private $resourceContract;
    
    /**
     * @var UserContract
     */
    private $userContract;

    /**
     * ResourcesController constructor.
     *
     * ResourcesController constructor.
     * @param ResourceContract $resourceContract
     * @param UserContract $userContract
     */
    public function __construct(ResourceContract $resourceContract, UserContract $userContract)
    {
        $this->resourceContract = $resourceContract;
        $this->userContract = $userContract;
    }

    /**
     * Show Faq list by category.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $user = $this->userContract->with('profile')->find(request()->user()->id);
        $resources = $this->resourceContract->orderBy('order', 'asc')->findAll();
        $trashed = $this->resourceContract->onlyTrashed();
        
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
        $resource = $this->resourceContract->create($request->all());

        if (null !== $request->file('document'))
        {
            $filename = $resource->id . '-' . $request->file('document')->getClientOriginalName();
            Storage::disk('public')->put(
                'resources/' . $filename,
                file_get_contents($request->file('document')->getRealPath())
            );

            $resource = $this->resourceContract->update($resource->id, ['document' => $filename]);
        }

        $resource ? Toastr::success('Resource has been created successfully.', 'Resource Create') :
            Toastr::error('Resource could not be saved.', 'Resource Create');

        return redirect()->route('admin.resources.index');
    }

    /**
     * Edit Resource.
     *
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        $user = $this->userContract->with('profile')->find(request()->user()->id);
        $resources = $this->resourceContract->findAll();
        $resource = $this->resourceContract->find($id);
        $trashed = $this->resourceContract->onlyTrashed();

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
        $resource = $this->resourceContract->find($id);

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

        $resource = $this->resourceContract->update($id, $data);

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
        $this->resourceContract->update($id, ['order' => 0]);
        $result = $this->resourceContract->delete($id);

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
        $resource = $this->resourceContract->onlyTrashed($id);
        Storage::disk('public')->delete('resources/' . $resource->document);

        $result = $this->resourceContract->forceDelete($id);

        $result ? Toastr::success('Resource has been forcefully deleted.', 'Resource Destroy')
            : Toastr::error('Resource could not be forcefully deleted.', 'Resource Destroy');
        
        return redirect()->route('admin.resources.index');
    }

    /**
     * Update ordering on resources.
     *
     * @param $id
     * @param $order
     */
    public function order($id, $order)
    {
        if (request()->ajax())
        {
            $this->resourceContract->update($id, ['order' => $order]);
        }
    }
}
