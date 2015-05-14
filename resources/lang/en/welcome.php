<?php
/**
 * welcome.php
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

return array(
	'welcome'   => 'Welcome to Biospex!',
	'started'   => '<p>Let\'s get started.</p>
	                <p>Biospex works by using groups, projects and expeditions. Here is an introduction.</p>',
	'groups'    => '<h4>Groups</h4>
	                <p>Groups let you work with other people. If you joined Biospex by invitation, then you 
	                are part of someone else\'s group. Otherwise, we made you a group of your very own. You can invite
	                colleagues to your own group.</p>',
	'projects'    => '<h4>Projects</h4>
	                <p>Projects are sets of your data that you want processed. The project also contains the 
	                descriptions of your targets for processing. The description you provide will be used when 
	                your data are sent out for transcription (Notes from Nature) and/or georeferencing
					(GEOLocate) and will be seen by citizen scientists who will use this information to decide if
					they are interested in and qualified to participate.</p>',
	'expeditions'    => '<h4>Expeditions</h4>
	                <p>Expeditions are sub sets of your data that get sent to various digitization tools. While a 
	                project may contain tens of thouands of records, expeditions may contain only a few hundred. 
	                You may choose to separate the expeditions based on year, taxa, location or another variable. 
	                You may also separate at random. We recommend dividing your data into several expeditions that 
	                are more manageable for the digitization tools and more exciting for citizen scientists.</p>',
	'ready'     => '<p>Ready to start? Go ahead, make your first project.</p>',
	
	'intro' => '<p>The system is pretty straightforward to use — there are just a few things you need to know.</p>

<p>Specimen digitization can involve a lot of different activities, but the most common are digital imaging, label
transcription, and georeferencing the collection locality.  The first of these is an onsite task, but the other two can
be completed online with the right crowdsourcing tools.  Biospex and our parent project, iDigBio, are leading efforts
to build standards and protocols for interoperability within a constellation of these useful tools.  This Biospex
release features growing interoperability with <a href="http://www.notesfromnature.org/" target="_blank">Notes from Nature</a>
(for transcription), and we have begun to build interoperability with <a href="http://www.museum.tulane.edu/geolocate/community/" target="_blank">GEOLocate</a>
(for georeferencing).</p>

<p>There are a few basic ideas to keep in mind when using the system.</p>

<p><strong>Groups</strong> manage <strong>projects</strong>.  Maybe you\'d like to manage a project by yourself.
In that case the group would have one member.  But maybe you want to share responsibility.  In that case you would invite
friends or colleagues to the group.</p>

<p><strong>Projects</strong> can be small (with just a few hundred specimens to digitize organized into one expedition)
or big (with tens of thousands of specimens to digitize organized into many expeditions).  We recommend dividing your
specimen digitization tasks into small <strong>expeditions</strong> of at most maybe 2000 specimens.  Think about how
you can package these to make them interesting.  For example, you might want to group the pre-1900 collections as a
"time-traveller" expedition.  Or maybe you might group the expedition taxonomically—a creepy spider expedition anyone?
What seems like a compelling theme to you?  Keeping the expeditions within a couple thousand specimens means more successes
to celebrate and more frequent reasons to advertise.  In this Biospex release we have not enabled filtering of specimens by
keyword, but we are on pace to have that deployed soon.  At the moment, you can specify a number of specimens for each
expedition, and Biospex will apportion them to the expeditions randomly from the data that you\'ve uploaded to the project.</p>

<p>How do you upload data to a project?  Biospex is designed to ingest what are called Darwin Core Archives.  These have
become a nearly ubiquitous interchange format for specimen data.  If you are a curator interested in getting specimen
images from your collection queued up in Notes from Nature for transcription, here\'s one way to go: use
<a href="https://www.idigbio.org/wiki/index.php/CYWG_iDigBio_Image_Ingestion_Appliance" target="_blank">iDigBio\'s Image
Ingestion Appliance</a> to register the images to the iDigBio Cloud, then do a search for those images in the
<a href="https://www.idigbio.org/portal/search" target="_blank">iDigBio Portal</a> and download the results.  These will
be supplied in the Darwin Core Archive format required by Biospex.  Click on the Add Data button on the Project\'s
administrative page and upload the zip file that the iDigBio Portal supplied.  You will receive an email once the file
is processed (a task that happens as frequently as every 15 minutes).  Watch this space for a how-to video on this
workflow and others in the coming months, along with a guide to inserting Optical Character Recognition into the workflow
so that you can sort your images into thematic expeditions.</p>

<p>Creating a project involves providing images and a somewhat detailed description of the project, but it\'s worth it.
The content is used to produce an attractive project homepage (<a href="/project/florida-plant-hotspot-digitization-blitz" target="_blank">see example</a>), and the content can be repurposed for advertising in the
future.  Biospex is participating in the development of standards and protocols for sharing information about projects
with our friends at the go-to sites for learning about citizen science opportunities, including
<a href="http://www.scistarter.com" target="_blank">scistarter.com</a> and
<a href="http://www.birds.cornell.edu/citscitoolkit" target="_blank">Citizen Science Central</a>.  We hope to see this
firmed up in the next months and interoperability for advertisement early next year.</p>

<p>Each project has a designated workflow that determines which crowdsourcing and other tools are used and in which order.
For the moment, there is a single workflow available—transcription with Notes from Nature.  In the next few months we
plan to introduce a second actor for the workflows—GeoLocate for georeferencing.  A project\'s workflow can then involve
transcription followed by georeferencing with Biospex keeping track of the status of specimens in the workflow for you.</p>

<p>The idea for Biospex emerged from iDigBio\'s Public Participation in Digitization of Biodiversity Research Specimens
Workshop in September 2012.  We thank the participants at that workshop, as well as at the 2013 CitScribe and 2014
CitStitch Hackathons for helping us develop greater interoperability and coordination in the community.  iDigBio is the
US National Science Foundation\'s Resource for Advancing Digitization of Biodiversity Research Specimens.</p>

<p>Still have questions? Feel free to contact us with any questions or concerns.</p>'


);