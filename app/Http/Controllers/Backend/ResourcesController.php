<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Flash;
use App\Http\Controllers\Controller;
use App\Http\Requests\ResourceFormRequest;
use App\Repositories\Interfaces\Resource;
use App\Repositories\Interfaces\User;
use Illuminate\Support\Facades\Storage;

class ResourcesController extends Controller
{

    /**
     * @var Resource
     */
    private $resourceContract;

    /**
     * @var User
     */
    private $userContract;

    /**
     * ResourcesController constructor.
     *
     * @param Resource $resourceContract
     * @param User $userContract
     */
    public function __construct(Resource $resourceContract, User $userContract)
    {
        $this->userContract = $userContract;
        $this->resourceContract = $resourceContract;
    }

    /**
     * Show Faq list by category.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $user = $this->userContract->findWith(request()->user()->id, ['profile']);
        $resources = $this->resourceContract->getResourcesOrdered();
        $trashed = $this->resourceContract->getTrashedResourcesOrdered();

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

            $resource = $this->resourceContract->update(['document' => $filename], $resource->id);
        }

        $resource ? Flash::success('Resource has been created successfully.') :
            Flash::error('Resource could not be saved.');

        return redirect()->route('admin.resources.index');
    }

    /**
     * Edit Resource.
     *
     * @param $resourceId
     * @return mixed
     */
    public function edit($resourceId)
    {
        $user = $this->userContract->findWith(request()->user()->id, ['profile']);
        $resources = $this->resourceContract->all();
        $resource = $this->resourceContract->find($resourceId);
        $trashed = $this->resourceContract->findOnlyTrashed();

        return view('backend.resources.index', compact('user', 'resources', 'resource', 'trashed'));
    }

    /**
     * Update Resource.
     *
     * @param ResourceFormRequest $request
     * @param $resourceId
     * @return mixed
     */
    public function update(ResourceFormRequest $request, $resourceId)
    {
        $resource = $this->resourceContract->find($resourceId);

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
            'title'       => $request->get('title'),
            'description' => $request->get('description'),
            'document'    => $resource->document
        ];

        $resource = $this->resourceContract->update($data, $resourceId);

        $resource ? Flash::success('Resource has been updated successfully.')
            : Flash::error('Resource could not be updated.');

        return redirect()->route('admin.resources.index');
    }

    /**
     * Delete resource.
     *
     * @param $resourceId
     * @return mixed
     */
    public function delete($resourceId)
    {
        $this->resourceContract->update(['order' => 0], $resourceId);
        $result = $this->resourceContract->delete($resourceId);

        $result ? Flash::success('The resource has been deleted.')
            : Flash::error('Resource could not be deleted.');

        return redirect()->route('admin.resources.index');
    }

    /**
     * Force delete soft deleted records.
     *
     * @param $resourceId
     * @return mixed
     */
    public function trash($resourceId)
    {
        $resource = $this->resourceContract->findOnlyTrashed($resourceId);
        Storage::disk('public')->delete('resources/' . $resource->document);

        $result = $this->resourceContract->destory($resource);

        $result ? Flash::success('Resource has been forcefully deleted.')
            : Flash::error('Resource could not be forcefully deleted.');

        return redirect()->route('admin.resources.index');
    }

    /**
     * Update ordering on resources.
     *
     * @param $resourceId
     * @param $order
     */
    public function order($resourceId, $order)
    {
        if (request()->ajax())
        {
            $this->resourceContract->update(['order' => $order], $resourceId);
        }
    }
}
