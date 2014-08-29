@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{trans('projects.project')}}
@stop

{{-- Content --}}
@section('content')

<div id="banner" style="background: url(http://clientwebstage.com/idigbio/biospex/dev/images/herb-banner.jpg) top left no-repeat; height: 250px;">
    <div class="container">
        <div class="col-md-12">
        <img src="http://clientwebstage.com/idigbio/biospex/dev/images/herb.jpg" alt="biospex" style="border: 5px solid #fff; margin-top: 100px; margin-left: -20px;"/>
        </div>
        
        
    </div>
</div>
<br clear="all" />&nbsp;
<div>
    <!-- Container -->
    <div class="container">
        <!-- Notifications -->
                <!-- ./ notifications -->

        <!-- Content -->
        <div class="row">
            <h1 class="banner">Florida Plant Hotspot Digitization Blitz</h1>
                
                
            <div class="col-md-7">
                
                 
                <p class="description">Build a dataset for a biodiversity hotspot—help FSU’s Godfrey Herbarium digitize its local plant specimens.</p>
                
                <p>The Florida State University’s Robert K. Godfrey Herbarium seeks to digitally image and database 100% of its current Florida specimens in a 24-month period starting May 1, 2014 using a combination of staff and volunteers.  The approach is purposefully groundbreaking, involving volunteers in all steps of the digitization process on- and off-site using new tools (e.g., Notes from Nature and BIOSPEX). The herbarium is the most extensive plant collection documenting plant diversity in the Florida panhandle—a national biodiversity hotspot with many very narrowly distributed plant species and subspecies.  As such, it represents an irreplaceable resource to researchers, natural resource managers, policy makers, and nature enthusiasts.  The data will be made available online through the Godfrey Herbarium website, iDigBio, and the Global Biodiversity Information Facility.</p>
                
                <h2 style="color: #8dc63f; font-size: 18px; font-weight: bold; margin: 45px 0 10px 0;">How to Participate</h2>
                
                <p>This project has the following active expeditions:</p>
                
                <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                <tr>
                    <th>Expedition</th>
                    <th class="nowrap">% Complete</th>
                    <th>Join In</th>
                </tr>
                </thead>
                <tbody>
                                <tr>
                    <td>Apalachicola National Forest #1</td>
                    <td class="nowrap">85% <span class="complete"><span class="complete85">&nbsp;</span></span></td>
                    <td><a href="">Notes from Nature</a></td>
                </tr><tr>
                    <td>Apalachicola National Forest #1</td>
                    <td class="nowrap">35% <span class="complete"><span class="complete35">&nbsp;</span></span></td>
                    <td><a href="">GeoLocate</a></td>
                </tr><tr>
                    <td>Apalachicola National Forest #2</td>
                    <td class="nowrap">15% <span class="complete"><span class="complete25">&nbsp;</span></span></td>
                    <td><a href="">Notes from Nature</a></td>
                </tr><tr>
                    <td>Apalachicola National Forest #3</td>
                    <td class="nowrap">00% <span class="complete">&nbsp;</span></td>
                    <td><a href="">Notes from Nature</a></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <span title="3" id="1" class="collapse out"></span></td>
                </tr>
                                </tbody>
            </table>
        </div>
                
              
            </div>
            <div class="col-md-5"> 
                
                <dl>
                <dt class="firstdl">Managed by</dt>
                <dd class="firstdl">The Florida State University’s Robert K. Godfrey Herbarium</dd>
                
                <dt>Contact</dt>
                <dd><a href="">Austin Mast</a></dd>
                
                <dt>Website</dt>
                <dd><a href="">http://herbarium.bio.fsu.edu</a></dd>
                
                
                <dt>Circumscription</dt>
                <dd>The project targets approximately # specimens collected in Florida that have not yet been digitally imaged or databased to date.</dd>
                
                <dt>Strategy</dt>
                <dd>The Godfrey Herbarium will host a series of digital imaging blitzes onsite with a goal of producing 3000 images at each blitz.  These will be wrapped into 500-specimen “expeditions” with themes that make them interesting (e.g., all from swamp habitat) or lead to greater efficiencies (e.g., all from same collector) for online transcription via Notes from Nature and/or similar tools.</dd>
                
                <dt>Incentives</dt>
                <dd>Volunteers who contribute 3 days onsite during the imaging blitzes or >500 online transcriptions will be sent a coffee mug or water bottle with the project logo on it.</dd>
                
                <dt>Geographic Scope</dt>
                <dd>Florida, U.S.A.</dd>
                
                <dt>Taxonomic Scope</dt>
                <dd>Seed Plants</dd>
                
                <dt>Temporal Scope</dt>
                <dd>1860–present</dd>
                
                <dt>Language Skills Required</dt>
                <dd>English and perhaps occasionally Spanish</dd>
                
                </dl>
               
                

                

                
            </div>
        </div>


        
        <!-- ./ content -->
    </div>
</div>

@stop