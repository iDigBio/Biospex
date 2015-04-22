<?php
/**
 * DownloadsController.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <bruhnrp@gmail.com>
 * @license    GNU General Public License, version 3
 * @copyright  (c) 2014, Biospex
 * @link       http://biospex.org
 *
 * This file is part of Biospex.
 * Biospex is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Biospex is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Biospex.  If not, see <http://www.gnu.org/licenses/>.
 */
use Biospex\Repo\Expedition\ExpeditionInterface;
use Biospex\Repo\Download\DownloadInterface;

class DownloadsController extends BaseController {

	/**
	 * @var ExpeditionInterface
	 */
	protected $expedition;

	/**
	 * @var DownloadInterface
	 */
	protected $download;

	/**
	 * Instantiate a new DownloadsController
	 *
	 * @param ExpeditionInterface $expedition
	 * @param DownloadInterface $download
	 */
	public function __construct (ExpeditionInterface $expedition,	DownloadInterface $download)
	{
		$this->expedition = $expedition;
		$this->download = $download;

		// Establish Filters
		$this->beforeFilter('auth');
		$this->beforeFilter('csrf', ['on' => 'post']);
		$this->beforeFilter('hasProjectAccess:expedition_view', ['only' => ['download', 'file']]);
	}

	/**
	 * Index showing downloads for Expedition.
	 *
	 * @param $projectId
	 * @param $expeditionId
	 * @return \Illuminate\View\View
	 */
	public function index ($projectId, $expeditionId)
	{
		$expedition = $this->expedition->findWith($expeditionId, ['project.group', 'downloads.actor']);
		return View::make('downloads.index', compact('expedition'));
	}

	public function show ($projectId, $expeditionId, $downloadId)
	{
		$download = $this->download->find($downloadId);
		$download->count = $download->count + 1;
		$this->download->save($download);

		$nfnExportDir = Config::get('config.nfnExportDir');
		$path = "$nfnExportDir/{$download->file}";
		$headers = ['Content-Type' => 'application/x-compressed'];
		return Response::download($path, $download->file, $headers);
	}
}
