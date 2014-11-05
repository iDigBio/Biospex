<?php
/**
 * ProjectsTableSeeder.php
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
class ProjectsTableSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
	public function run()
	{
        Eloquent::unguard();

        $project = Project::create(array(
            'group_id' => 3,
            'title' => 'Florida Plant Hotspot Digitization Blitz',
            'slug' => 'florida-plant-hotspot-digitization-blitz',
            'contact' => 'Austin Mast',
            'contact_email' => 'amast@bio.fsu.edu',
			'website' => 'http://herbarium.bio.fsu.edu/',
            'managed' => 'The Florida State University’s Robert K. Godfrey Herbarium',
            'description_short' => 'Build a dataset for a biodiversity hotspot—help FSU’s Godfrey Herbarium digitize its local plant specimens.',
			'description_long' => 'The Florida State University’s Robert K. Godfrey Herbarium seeks to digitally image and database 100% of its current Florida specimens in a 24-month period starting May 1, 2014 using a combination of staff and volunteers.  The approach is purposefully groundbreaking, involving volunteers in all steps of the digitization process on- and off-site using new tools (e.g., Notes from Nature and BIOSPEX). The herbarium is the most extensive plant collection documenting plant diversity in the Florida panhandle—a national biodiversity hotspot with many very narrowly distributed plant species and subspecies.  As such, it represents an irreplaceable resource to researchers, natural resource managers, policy makers, and nature enthusiasts.  The data will be made available online through the Godfrey Herbarium website, iDigBio, and the Global Biodiversity Information Facility. The project targets approximately # specimens collected in Florida that have not yet been digitally imaged or databased to date. The Godfrey Herbarium will host a series of digital imaging blitzes onsite with a goal of producing 3000 images at each blitz.  These will be wrapped into 500-specimen “expeditions” with themes that make them interesting (e.g., all from swamp habitat) or lead to greater efficiencies (e.g., all from same collector) for online transcription via Notes from Nature and/or similar tools.',
            'incentives' => 'Volunteers who contribute 3 days onsite during the imaging blitzes or >500 online transcriptions will be sent a coffee mug or water bottle with the project logo on it.',
            'geographic_scope' => 'Florida, U.S.A.',
            'taxonomic_scope' => 'Seed Plants',
            'temporal_scope' => '1860–present',
            'keywords' => 'Biodiversity Hotspot, East Gulf Coastal Plain, Florida, Florida State University, Robert K. Godfrey Herbarium, Seed Plants',
            'hashtag' => '#FLDigBlitz',
            'activities' => 'Transcription',
            'language_skills' => 'English and perhaps occasionally Spanish',
        ));

		$actors = Actor::all();
		$i = 1;
		foreach ($actors as $actor)
		{
			$project->actors()->attach($actor->id, ['order_by' => $i++]);
		}
	}

}
