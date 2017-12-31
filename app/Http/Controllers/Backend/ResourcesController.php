<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Flash;
use App\Http\Controllers\Controller;
use App\Http\Requests\ResourceFormRequest;
use App\Interfaces\Resource;
use App\Interfaces\User;
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
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        $user = $this->userContract->findWith(request()->user()->id, ['profile']);
        $resources = $this->resourceContract->all();
        $resource = $this->resourceContract->find($id);
        $trashed = $this->resourceContract->findOnlyTrashed();

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
            'title'       => $request->get('title'),
            'description' => $request->get('description'),
            'document'    => $resource->document
        ];

        $resource = $this->resourceContract->update($data, $id);

        $resource ? Flash::success('Resource has been updated successfully.')
            : Flash::error('Resource could not be updated.');

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
        $this->resourceContract->update(['order' => 0], $id);
        $result = $this->resourceContract->delete($id);

        $result ? Flash::success('The resource has been deleted.')
            : Flash::error('Resource could not be deleted.');

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
        $resource = $this->resourceContract->findOnlyTrashed($id);
        Storage::disk('public')->delete('resources/' . $resource->document);

        $result = $this->resourceContract->destory($resource);

        $result ? Flash::success('Resource has been forcefully deleted.')
            : Flash::error('Resource could not be forcefully deleted.');

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
            $this->resourceContract->update(['order' => $order], $id);
        }
    }
}
