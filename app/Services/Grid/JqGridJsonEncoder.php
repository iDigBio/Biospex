<?php  namespace Biospex\Services\Grid;
/**
 * JqGridJsonEncoder.php
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

use Maatwebsite\Excel\Excel;
use Biospex\Repositories\Contracts\SubjectInterface;
use Biospex\Repositories\Contracts\ExpeditionInterface;
use Exception;

class JqGridJsonEncoder {

	/**
	 * @var SubjectInterface
	 */
	protected $subject;

	/**
	 * @var ExpeditionInterface
	 */
	protected $expedition;

	/**
	 * @var Excel
	 */
	protected $excel;

	/**
	 * Construct
	 *
	 * @param SubjectInterface $subject
	 * @param ExpeditionInterface $expedition
	 * @param Excel $excel
	 */
	public function __construct(
		SubjectInterface $subject,
		ExpeditionInterface $expedition,
		Excel $excel
	)
	{
		$this->subject = $subject;
		$this->expedition = $expedition;
		$this->excel = $excel;
	}

	/**
	 * Load grid model.
	 *
	 * @return string
	 */
	public function loadGridModel()
	{
		return json_encode($this->subject->loadGridModel());
	}

	/**
	 * Echo in a jqGrid compatible format the data requested by a grid.
	 *
	 * @param $postedData
	 * @throws Exception
	 */
	public function encodeRequestedData($postedData)
	{
		if(isset($postedData['page']))
		{
			$page = $postedData['page'];
		}
		else
		{
			$page = 1;
		}

		if(isset($postedData['rows']))
		{
			$limit = $postedData['rows'];
		}
		else
		{
			$limit = null;
		}

		if(isset($postedData['sidx']))
		{
			$sidx = $postedData['sidx'];
		}
		else
		{
			$sidx = null;
		}

		if(isset($postedData['sord']))
		{
			$sord = $postedData['sord'];
		}

		if(isset($postedData['filters']) && !empty($postedData['filters']))
		{
			$filters = json_decode(str_replace('\'','"',$postedData['filters']), true);
		}

		if(!$sidx || empty($sidx))
		{
			$sidx = null;
			$sord = null;
		}

		if(isset($filters['rules']) && is_array($filters['rules']))
		{
			foreach ($filters['rules'] as &$filter)
			{
				switch ($filter['op'])
				{
					case 'eq': //equal
						$filter['op'] = '=';
						break;
					case 'ne': //not equal
						$filter['op'] = '!=';
						break;
					case 'lt': //less
						$filter['op'] = '<';
						break;
					case 'le': //less or equal
						$filter['op'] = '<=';
						break;
					case 'gt': //greater
						$filter['op'] = '>';
						break;
					case 'ge': //greater or equal
						$filter['op'] = '>=';
						break;
					case 'bw': //begins with
						$filter['op'] = 'like';
						$filter['data'] = $filter['data'] . '%';
						break;
					case 'bn': //does not begin with
						$filter['op'] = 'not like';
						$filter['data'] = $filter['data'] . '%';
						break;
					case 'in': //is in
						$filter['op'] = 'is in';
						break;
					case 'ni': //is not in
						$filter['op'] = 'is not in';
						break;
					case 'ew': //ends with
						$filter['op'] = 'like';
						$filter['data'] = '%' . $filter['data'];
						break;
					case 'en': //does not end with
						$filter['op'] = 'not like';
						$filter['data'] = '%' . $filter['data'];
						break;
					case 'cn': //contains
						$filter['op'] = 'like';
						$filter['data'] = '%' . $filter['data'] . '%';
						break;
					case 'nc': //does not contains
						$filter['op'] = 'not like';
						$filter['data'] = '%' . $filter['data'] . '%';
						break;
				}
			}
		}
		else
		{
			$filters['rules'] = array();
		}

		$count = $this->subject->getTotalNumberOfRows($filters['rules']);

		if(empty($limit))
		{
			$limit = $count;
		}

		if(!is_int($count))
		{
			throw new Exception('The method getTotalNumberOfRows must return an integer');
		}

		if( $count > 0 )
		{
			$totalPages = ceil($count/$limit);
		}
		else
		{
			$totalPages = 0;
		}

		if ($page > $totalPages)
		{
			$page = $totalPages;
		}

		if ($limit < 0 )
		{
			$limit = 0;
		}

		$start = $limit * $page - $limit;

		if ($start < 0)
		{
			$start = 0;
		}

		$limit = $limit * $page;

		if(empty($postedData['pivotRows']))
		{
			$rows = $this->subject->getRows($limit, $start, $sidx, $sord, $filters['rules']);
		}
		else
		{
			$rows = json_decode($postedData['pivotRows'], true);
		}

		if(!is_array($rows) || (isset($rows[0]) && !is_array($rows[0])))
		{
			throw new Exception('The method getRows must return an array of arrays, example: array(array("column1"  =>  "1-1", "column2" => "1-2"), array("column1" => "2-1", "column2" => "2-2"))');
		}

		if(isset($postedData['exportFormat']))
		{
			$this->excel->create($postedData['name'], function($excel) use ($rows, $postedData)
			{
				foreach (json_decode($postedData['fileProperties'], true) as $key => $value)
				{
					$method = 'set' . ucfirst($key);

					$excel->$method($value);
				}

				$excel->sheet($postedData['name'], function($Sheet) use ($rows, $postedData)
				{
					$columnCounter = 0;

					foreach (json_decode($postedData['model'], true) as $a => $model)
					{
						if(isset($model['hidden']) && $model['hidden'] !== true)
						{
							$columnCounter++;
						}

						if(isset($model['hidedlg']) && $model['hidedlg'] === true)
						{
							continue;
						}

						if(empty($postedData['pivot']))
						{
							foreach ($rows as $b => &$row)
							{
								if(isset($model['hidden']) && $model['hidden'] === true)
								{
									unset($row[$model['index']]);
								}
								else
								{
									if(isset($model['label']))
									{
										$row = array_add($row, $model['label'], $row[$model['index']]);
										unset($row[$model['index']]);
									}
									else
									{
										$temp = $row[$model['index']];
										unset($row[$model['index']]);
										$row = array_add($row, $model['index'], $temp);
									}
								}
							}
						}

						if(isset($model['align']) && isset($model['hidden']) && $model['hidden'] !== true)
						{
							$Sheet->getStyle($this->num_to_letter($columnCounter, true))->getAlignment()->applyFromArray(
								array('horizontal' => $model['align'])
							);
						}
					}

					foreach (json_decode($postedData['sheetProperties'], true) as $key => $value)
					{
						$method = 'set' . ucfirst($key);

						$Sheet->$method($value);
					}

					$Sheet->fromArray($rows);

					$Sheet->row(1, function($Row) {
						$Row->setFontWeight('bold');
					});
				});
			})->export($postedData['exportFormat']);
		}
		else
		{
			echo json_encode([
				'page' => $page,
				'total' => $totalPages,
				'records' => $count,
				'rows' => $rows,
			]);
		}
	}

	/**
	 * Update selected rows
	 *
	 * @param $id
	 * @param $data
	 * @return string
	 */
	public function updateSelectedRows($id, $data)
	{
		$expedition = $this->expedition->find($id);

		if ($data['selected'] == "true")
		{
			$expedition->subjects()->sync($data['ids'], false);
		}
		else
		{
			$this->subject->detachSubjects($data['ids'], $id);
		}

		$count = $expedition->getSubjectsCountAttribute();

		return json_encode($count);
	}

	/**
	 * Takes a number and converts it to a-z,aa-zz,aaa-zzz, etc with uppercase option
	 *
	 * @access	public
	 * @param	int	number to convert
	 * @param	bool	upper case the letter on return?
	 * @return	string	letters from number input
	 */
	protected function num_to_letter($num, $uppercase = FALSE)
	{
		$num -= 1;

		$letter = 	chr(($num % 26) + 97);
		$letter .= 	(floor($num/26) > 0) ? str_repeat($letter, floor($num/26)) : '';
		return 		($uppercase ? strtoupper($letter) : $letter);
	}
}
