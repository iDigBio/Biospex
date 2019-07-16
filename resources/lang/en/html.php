<?php

return [
    'biospex_home' => 'BIOSPEX is a base camp for launching, advertising, and managing targeted efforts to digitize
            the world\'s 3 billion biodiversity research specimens in ways that involve the public. Such specimens 
            include fish in jars, plants on sheets, fossils in drawers, insects on pins, and many other types. “Digitization” 
            is a broad reference to creating digital data about the physical specimens and includes things like recording the what, when, 
            where from the specimen label or describing the life stage of the specimen at time of collection.
            BIOSPEX enables you to package projects in one or a series of digitization expeditions, launch the expeditions at crowdsourcing
            tools, widely recruit others to participate, and layer resources on the experience to advance science
            literacy. In the end, you can download the new data for specimen curation, research, conservation, natural
                                                                   resource management, public policy, or other activities.',
    'export_processing'       => '<div class="processes"><span class="title">:title</span><br>:stage :processedRecords</div>',
    'export_queued'           => '<div class="processes"><span class="title">:title</span><br>:count export remains in queue before processing begins.</div>|
                                        <div class="processes"><span class="title">:title</span><br>:count exports remain in queue before processing begins.</div>',
    'import_recordset_desc'   => '<li>Go to<a href="https://www.idigbio.org/portal/publishers" target="_blank" class="link">iDigBio.org Publishers Page</a></li>
                           <li>Find the Publisher you want and select. (e.g. https://herbarium.bio.fsu.edu:8443/)</li>
                           <li>Click the Collection you are interested in. (e.g. Robert K. Godfrey Herbarium at Florida State University)</li>
                           <li>iDiogBio does not actually show the recordset id in the page, so it must be retrieved via the URL.
                            <ol>
                                <li>Url: https://www.idigbio.org/portal/recordsets/b2b294ed-1742-4479-b0c8-a8891fccd7eb</li>
                                <li>Record Id: b2b294ed-1742-4479-b0c8-a8891fccd7eb</li>
                            </ol>
                           </li>',
    'ocr_processing'          => '<div class="processes"><span class="title">:title</span><br>:ocr :batches',
    'ocr_queue'               => ':batches_queued process remains in queue before processing begins|:batches_queued processes remain in queue before processing begins',
    'ocr_records'             => ':processed record of :total completed.|:processed records of :total completed.',
    'processed_records'       => '| - :processed of :total completed.',
];
