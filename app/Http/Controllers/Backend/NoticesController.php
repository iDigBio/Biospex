<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Flash;
use App\Http\Controllers\Controller;
use App\Http\Requests\NoticeFormRequest;
use App\Interfaces\User;
use App\Interfaces\Notice;

class NoticesController extends Controller
{

    /**
     * @var Notice
     */
    private $noticeContract;

    /**
     * @var User
     */
    private $userContract;

    /**
     * NoticesController constructor.
     *
     * @param Notice $noticeContract
     * @param User $userContract
     */
    public function __construct(Notice $noticeContract, User $userContract)
    {
        $this->noticeContract = $noticeContract;
        $this->userContract = $userContract;
    }

    /**
     * Notice index.
     *
     * @return mixed
     */
    public function index()
    {
        $user = $this->userContract->findWith(request()->user()->id, ['profile']);
        $notices = $this->noticeContract->all();
        $trashed = $this->noticeContract->getOnlyTrashed();

        return view('backend.notices.index', compact('user', 'notices', 'trashed'));
    }

    /**
     * Edit notice.
     *
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        $user = $this->userContract->findWith(request()->user()->id, ['profile']);
        $notices = $this->noticeContract->all();
        $trashed = $this->noticeContract->getOnlyTrashed();
        $notice = $this->noticeContract->find($id);

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
        $notice = $this->noticeContract->update($request->all(), $id);
        
        $notice ? Flash::success('Notice has been updated.')
            : Flash::error('Notice could not be updated.');

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
        $notice = $this->noticeContract->create($request->all());
        
        $notice ? Flash::success('Notice has been created.')
            : Flash::error('Notice could not be created.');

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
        $this->noticeContract->delete($id) ?
            Flash::success('Notice has been deleted.') :
            Flash::error('Notice could not be deleted.');
        
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
        $this->noticeContract->destroy($id) ?
            Flash::success('Notice has been forcefully deleted.') :
            Flash::error('Notice could not be forcefully deleted.');

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
        $this->noticeContract->update(['enabled' => 1], $id) ?
            Flash::success('Notice has been enabled.') :
            Flash::error('Notice could not be enabled.');

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
        $this->noticeContract->update(['enabled' => 0], $id) ?
            Flash::success('Notice has been disabled.') :
            Flash::error('Notice could not be disabled.');

        return redirect()->route('admin.notices.index');
    }
}
