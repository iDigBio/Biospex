<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Toastr;
use App\Http\Controllers\Controller;
use App\Http\Requests\NoticeFormRequest;
use App\Repositories\Contracts\Actor;
use App\Repositories\Contracts\User;
use App\Repositories\Contracts\Notice;
use Illuminate\Http\Request;

class NoticesController extends Controller
{

    /**
     * @var Notice
     */
    private $notice;

    /**
     * @var User
     */
    private $user;

    /**
     * NoticesController constructor.
     *
     * @param Notice $notice
     * @param User $user
     */
    public function __construct(Notice $notice, User $user)
    {
        $this->notice = $notice;
        $this->user = $user;
    }

    /**
     * Notice index.
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $user = $this->user->with(['profile'])->find($request->user()->id);
        $notices = $this->notice->all();
        $trashed = $this->notice->trashed();

        return view('backend.notices.index', compact('user', 'notices', 'trashed'));
    }

    /**
     * Edit notice.
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function edit(Request $request, $id)
    {
        $user = $this->user->with(['profile'])->find($request->user()->id);
        $notices = $this->notice->all();
        $trashed = $this->notice->trashed();
        $notice = $this->notice->find($id);

        return view('backend.notices.index', compact('user', 'notices', 'notice', 'trashed'));
    }

    /**
     * Update notice.
     *
     * @param NoticeFormRequest $request
     * @param $id
     * @return mixed
     */
    public function update(NoticeFormRequest $request, $id)
    {
        $notice = $this->notice->update($request->all(), $id);
        
        $notice ? Toastr::success('Notice has been updated.', 'Notice Update')
            : Toastr::error('Notice could not be updated.', 'Notice Update');

        return redirect()->route('admin.notices.index');
    }

    /**
     * Redirect to index.
     *
     * @return mixed
     */
    public function create()
    {
        return redirect()->route('admin.notices.index');
    }

    /**
     * Create Notice.
     *
     * @param NoticeFormRequest $request
     * @return mixed
     */
    public function store(NoticeFormRequest $request)
    {
        $notice = $this->notice->create($request->all());
        
        $notice ? Toastr::success('Notice has been created.', 'Notice Create')
            : Toastr::error('Notice could not be created.', 'Notice Create');

        return redirect()->route('admin.notices.index');
    }

    /**
     * Soft delete notice.
     *
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        $this->notice->update(['enabled' => 0], $id);
        $result = $this->notice->delete($id);

        $result ? Toastr::success('Notice has been deleted.', 'Notice Delete')
            : Toastr::error('Notice could not be deleted.', 'Notice Delete');
        
        return redirect()->route('admin.notices.index');
    }

    /**
     * Force delete soft deleted records.
     *
     * @param $id
     * @return mixed
     */
    public function trash($id)
    {
        $result = $this->notice->forceDelete($id);

        $result ? Toastr::success('Notice has been forcefully deleted.', 'Notice Delete')
            : Toastr::error('Notice could not be forcefully deleted.', 'Notice Delete');

        return redirect()->route('admin.notices.index');
    }

    /**
     * Enable Actor.
     *
     * @param $id
     * @return mixed
     */
    public function enable($id)
    {
        $result = $this->notice->update(['enabled' => 1], $id);

        $result ? Toastr::success('Notice has been enabled.', 'Notice Enable')
            : Toastr::error('Notice could not be enabled.', 'Notice Enable');

        return redirect()->route('admin.notices.index');
    }

    /**
     * Disable Notice.
     *
     * @param $id
     * @return mixed
     */
    public function disable($id)
    {
        $result = $this->notice->update(['enabled' => 0], $id);

        $result ? Toastr::success('Notice has been disabled.', 'Notice Disable')
            : Toastr::error('Notice could not be disabled.', 'Notice Disable');

        return redirect()->route('admin.notices.index');
    }
}
