<?php
/**
 * SubjectsDocsTableSeeder.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <79e6ef82@opayq.com>
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

use Illuminate\Database\Seeder;
use Biospex\Services\Subject\Subject;

class SubjectsDocsTableSeeder extends Seeder {

    /**
     * Default project Id
     *
     * @var int
     */
    protected $projectId = 1;

    /**
     * Constructor
     *
     * @param SubjectsImport $subjectsImport
     */
    public function __construct (Subject $subject)
    {
        $this->subject = $subject;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run ()
    {
        Eloquent::unguard();

        DB::table('metas')->truncate();
        DB::connection('mongodb')->collection('subjectsdocs')->delete();

        $xml = $this->subject->loadDom('app/database/seeds/data/meta.xml');

        $this->subject->setFiles();

        $multiMediaFile = $this->subject->getMultiMediaFile();
        $occurrenceFile = $this->subject->getOccurrenceFile();

        $multimedia = $this->subject->loadCsv("app/database/seeds/data/$multiMediaFile", 'multimedia');
        $occurrence = $this->subject->loadCsv("app/database/seeds/data/$occurrenceFile", 'occurrence');

        $meta = $this->subject->saveMeta($xml, $this->projectId);

        $subjects = $this->subject->buildSubjectsArray($multimedia, $occurrence, $this->projectId, $meta->id);

        $this->subject->insertDocs($subjects);
    }
}