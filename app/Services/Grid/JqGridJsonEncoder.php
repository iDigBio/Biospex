<?php  namespace App\Services\Grid;

use App\Repositories\Contracts\Subject;
use App\Repositories\Contracts\Expedition;

class JqGridJsonEncoder
{
    /**
     * @var Subject
     */
    protected $subject;

    /**
     * @var Expedition
     */
    protected $expedition;

    /**
     * Construct
     *
     * @param Subject $subject
     * @param Expedition $expedition
     */
    public function __construct(
        Subject $subject,
        Expedition $expedition
    ) {
        $this->subject = $subject;
        $this->expedition = $expedition;
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
        if (isset($postedData['page'])) {
            $page = $postedData['page'];
        } else {
            $page = 1;
        }

        if (isset($postedData['rows'])) {
            $limit = $postedData['rows'];
        } else {
            $limit = null;
        }

        if (isset($postedData['sidx'])) {
            $sidx = $postedData['sidx'];
        } else {
            $sidx = null;
        }

        if (isset($postedData['sord'])) {
            $sord = $postedData['sord'];
        }

        if (isset($postedData['filters']) && !empty($postedData['filters'])) {
            $filters = json_decode(str_replace('\'', '"', $postedData['filters']), true);
        }

        if (!$sidx || empty($sidx)) {
            $sidx = null;
            $sord = null;
        }

        if (isset($filters['rules']) && is_array($filters['rules'])) {
            foreach ($filters['rules'] as &$filter) {
                switch ($filter['op']) {
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
        } else {
            $filters['rules'] = [];
        }

        $count = $this->subject->getTotalNumberOfRows($filters['rules']);

        if (empty($limit)) {
            $limit = $count;
        }

        if (!is_int($count)) {
            throw new \Exception('The method getTotalNumberOfRows must return an integer');
        }

        if ($count > 0) {
            $totalPages = ceil($count/$limit);
        } else {
            $totalPages = 0;
        }

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        if ($limit < 0) {
            $limit = 0;
        }

        $start = $limit * $page - $limit;

        if ($start < 0) {
            $start = 0;
        }

        $limit = $limit * $page;

        if (empty($postedData['pivotRows'])) {
            $rows = $this->subject->getRows($limit, $start, $sidx, $sord, $filters['rules']);
        } else {
            $rows = json_decode($postedData['pivotRows'], true);
        }

        if (!is_array($rows) || (isset($rows[0]) && !is_array($rows[0]))) {
            throw new \Exception('The method getRows must return an array of arrays, example: array(array("column1"  =>  "1-1", "column2" => "1-2"), array("column1" => "2-1", "column2" => "2-2"))');
        }

        if (isset($postedData['exportFormat'])) {
        } else {
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

        if ($data['selected'] == "true") {
            $expedition->subjects()->sync($data['ids'], false);
        } else {
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
    protected function num_to_letter($num, $uppercase = false)
    {
        $num -= 1;

        $letter =    chr(($num % 26) + 97);
        $letter .=    (floor($num/26) > 0) ? str_repeat($letter, floor($num/26)) : '';
        return        ($uppercase ? strtoupper($letter) : $letter);
    }
}
