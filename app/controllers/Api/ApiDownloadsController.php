<?php

namespace Api;

use Biospex\Repo\Download\DownloadInterface;

class ApiDownloadsController extends \BaseController {
    /**
     * @var DownloadInterface
     */
    private $download;

    /**
     * @param DownloadInterface $download
     */
    public function __construct(DownloadInterface $download)
	{

        $this->download = $download;
    }

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
        return 'Sorry! Resource method is not allowed.';
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
        return 'Sorry! Resource method is not allowed.';
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        return 'Sorry! Resource method is not allowed.';
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
        $download = $this->download->find($id);
        $download->count = $download->count + 1;
        $download->save();

        if ( ! empty($download->data)){
            $headers = [
                'Content-type' => 'application/json; charset=utf-8',
                'Content-disposition' => 'attachment; filename="' . $download->file . '"'
            ];

			$view = \View::make('manifest', unserialize($download->data))->render();

            return \Response::make(stripslashes($view), 200, $headers);
        } else {

            $nfnExportDir = \Config::get('config.nfnExportDir');
            $path = $nfnExportDir . '/' . $download->file;
            $headers = ['Content-Type' => 'application/x-compressed'];

            return \Response::download($path, $download->file, $headers);
        }

        return App::abort(403, 'Unauthorized action.');
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
        return 'Sorry! Resource method is not allowed.';
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        return 'Sorry! Resource method is not allowed.';
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        return 'Sorry! Resource method is not allowed.';
	}


}
