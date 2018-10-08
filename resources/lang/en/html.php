<?php

return array (
  'export_processing' => '<div class="processes"><span class="title">:title</span><br>:stage :processedRecords</div>',
  'export_queued' => '<div class="processes"><span class="title">:title</span><br>:count export remains in queue before processing begins.</div>|
                                        <div class="processes"><span class="title">:title</span><br>:count exports remain in queue before processing begins.</div>',
  'footer-text' => 'BIOSPEX is funded by a grant from the National Science Foundation’s Advances in Biological 
    Informatics Program (Award Number 1458550). iDigBio is funded by a grant from the National Science Foundation\'s 
    Advancing Digitization of Biodiversity Collections Program (Cooperative Agreement EF-1115210). Any opinions, 
    findings, and conclusions or recommendations expressed in this material are those of the author(s) and do not 
    necessarily reflect the views of the National Science Foundation.',
  'homepage-header' => '<h1>Use BIOSPEX to provision, advertise, and lead<br/>public Biodiversity Specimen Digitization Expeditions</h1>',
  'homepage-text' => '<h2>Liberate data from the cabinets</h2>

                    <p>The world\'s 3 billion biodiversity research specimens provide the historical baseline for
                        understanding the patterns of Life\'s diversity and distribution today and projecting future
                        changes to it. But information about the majority of these specimens languishes in cabinets.
                        BIOSPEX is a basecamp for launching, advertising, and managing targeted efforts to digitize
                        these
                        specimens. We recognize that motivations to digitize the data can vary a lot, from the museum
                        curator to the descendants of a collector reconstructing their ancestor\'s steps to an
                        environmental
                        group interested in the health of a local water body. BIOSPEX enables each of these to package
                        their projects in one or a series of digitization expeditions, launch the expeditions at
                        crowdsourcing
                        tools, and widely recruit others to participate. In the end, you can download the new data AND
                        the
                        data goes back to the museum that curates the physical specimen.</p>

                    <p>Get started -> <a href="/login">Lead an Expedition</a></p>',
  'ocr_processing' => '<div class="processes"><span class="title">:title</span><br>:ocr :batches',
  'ocr_queue' => ':batches_queued process remains in queue before processing begins|:batches_queued processes remain in queue before processing begins',
  'ocr_records' => ':processed record of :total completed.|:processed records of :total completed.',
  'processed_records' => '| - :processed of :total completed.',
  'processing_empty' => 'No processes running at this time.',
  'recordset_modal_message' => '<ol>
                    <li>Go to <a href="https://www.idigbio.org/portal/publishers" target="_blank">iDigBio.org Publishers Page</a></li>
                    <li>Find the Publisher you want and select. (e.g. https://herbarium.bio.fsu.edu:8443/)</li>
                    <li>Click the Collection you are interested in. (e.g. Robert K. Godfrey Herbarium at Florida State University)
                    </li>
                    <li>iDiogBio does not actually show the recordset id in the page, so it must be retrieved via the URL.
                        <ol>
                            <li>Url: https://www.idigbio.org/portal/recordsets/b2b294ed-1742-4479-b0c8-a8891fccd7eb</li>
                            <li>Record Id: b2b294ed-1742-4479-b0c8-a8891fccd7eb</li>
                        </ol>
                    </li>
                </ol>',
  'total-contributors' => 'Contributors to Biospex-launched Projects',
  'total-transcriptions' => 'Transcriptions in Biospex-launched Projects',
  'welcome' => 'Welcome to BIOSPEX!',
  'welcome_event' => 'We will start by establishing an <strong>Event</strong> for you. <strong>Events</strong> contain single or multiple <strong>Groups</strong> consisting of users. Create a single group for a class event or create multiple <strong>Groups</strong> for competitions. After creating your <strong>Event</strong> and <strong>Groups</strong>, you will be given invite urls where users can sign up for a particular group using their <strong>Notes From Nature</strong> user id. <br><br>If you have not yet read the BIOSPEX help content, including the <a href="/ourvision">Our Vision</a>, <a href="/faq">FAQ</a>, and <a href="/resource">Resource</a> pages, we encourage you to do so now. They will help you to use the system efficiently.<br><br>Still have questions? Feel free to contact us with any questions or concerns using the <a href="/contact">Contact</a> page.<br><br>Ready to start? Go ahead, create your first Event.',
  'welcome_event_title' => 'BIOSPEX Events',
  'welcome_project' => 'We will start by establishing a group for you. <strong>Groups</strong> manage <strong>Projects</strong> which launch <strong>Expeditions</strong>. Maybe you\'d like to manage a project by yourself. In that case, your group will have one member. But maybe you want to share responsibility. In that case you will invite collaborators to the group. You can have as many groups as are necessary to manage your project portfolio.<br><br>If you have not yet read the BIOSPEX help content, including the <a href="/ourvision">Our Vision</a>, <a href="/faq">FAQ</a>, and <a href="/resource">Resource</a> pages, we encourage you to do so now. They will help you to use the system efficiently.<br><br>Still have questions? Feel free to contact us with any questions or concerns using the <a href="/contact">Contact</a> page.<br><br>Ready to start? Go ahead, create your first Group.',
  'welcome_project_title' => 'BIOSPEX Projects',
);
