<?php
/*
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Console\Commands;

use App\Services\FixFields\FixFieldsStepEight;
use App\Services\FixFields\FixFieldsStepFive;
use App\Services\FixFields\FixFieldsStepFour;
use App\Services\FixFields\FixFieldsStepNine;
use App\Services\FixFields\FixFieldsStepOne;
use App\Services\FixFields\FixFieldsStepSeven;
use App\Services\FixFields\FixFieldsStepSix;
use App\Services\FixFields\FixFieldsStepThree;
use App\Services\FixFields\FixFieldsStepTwo;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

/**
 * Class UpdateQueries
 *
 * ALTER TABLE properties CHANGE short short VARCHAR(255) BINARY NOT NULL;
 * ALTER TABLE `properties` DROP INDEX `properties_qualified_unique`;
 * ALTER TABLE `properties`DROP `qualified`;
 * ALTER TABLE `properties`DROP `namespace`;
 *
 * @package App\Console\Commands
 */
class UpdateQueries extends Command
{
    use DispatchesJobs;

    /**
     * The console command name.
     */
    protected $signature = 'update:queries {step}';

    /**
     * The console command description.
     */
    protected $description = 'Used for custom queries when updating database';

    /**
     * @var \App\Services\FixFields\FixFieldsStepOne
     */
    private FixFieldsStepOne $fixFieldsStepOne;

    /**
     * @var \App\Services\FixFields\FixFieldsStepTwo
     */
    private FixFieldsStepTwo $fixFieldsStepTwo;

    /**
     * @var \App\Services\FixFields\FixFieldsStepThree
     */
    private FixFieldsStepThree $fixFieldsStepThree;

    /**
     * @var \App\Services\FixFields\FixFieldsStepFour
     */
    private FixFieldsStepFour $fixFieldsStepFour;

    /**
     * @var \App\Services\FixFields\FixFieldsStepFive
     */
    private FixFieldsStepFive $fixFieldsStepFive;

    /**
     * @var \App\Services\FixFields\FixFieldsStepSix
     */
    private FixFieldsStepSix $fixFieldsStepSix;

    /**
     * @var \App\Services\FixFields\FixFieldsStepSeven
     */
    private FixFieldsStepSeven $fixFieldsStepSeven;

    /**
     * @var \App\Services\FixFields\FixFieldsStepEight
     */
    private FixFieldsStepEight $fixFieldsStepEight;

    /**
     * @var \App\Services\FixFields\FixFieldsStepNine
     */
    private FixFieldsStepNine $fixFieldsStepNine;

    /**
     * UpdateQueries constructor.
     *
     * @param \App\Services\FixFields\FixFieldsStepOne $fixFieldsStepOne
     * @param \App\Services\FixFields\FixFieldsStepTwo $fixFieldsStepTwo
     * @param \App\Services\FixFields\FixFieldsStepThree $fixFieldsStepThree
     * @param \App\Services\FixFields\FixFieldsStepFour $fixFieldsStepFour
     * @param \App\Services\FixFields\FixFieldsStepFive $fixFieldsStepFive
     * @param \App\Services\FixFields\FixFieldsStepSix $fixFieldsStepSix
     * @param \App\Services\FixFields\FixFieldsStepSeven $fixFieldsStepSeven
     * @param \App\Services\FixFields\FixFieldsStepEight $fixFieldsStepEight
     * @param \App\Services\FixFields\FixFieldsStepNine $fixFieldsStepNine
     */
    public function __construct(
        FixFieldsStepOne $fixFieldsStepOne,
        FixFieldsStepTwo $fixFieldsStepTwo,
        FixFieldsStepThree $fixFieldsStepThree,
        FixFieldsStepFour $fixFieldsStepFour,
        FixFieldsStepFive $fixFieldsStepFive,
        FixFieldsStepSix $fixFieldsStepSix,
        FixFieldsStepSeven $fixFieldsStepSeven,
        FixFieldsStepEight $fixFieldsStepEight,
        FixFieldsStepNine $fixFieldsStepNine
    ) {
        parent::__construct();

        $this->fixFieldsStepOne = $fixFieldsStepOne;
        $this->fixFieldsStepTwo = $fixFieldsStepTwo;
        $this->fixFieldsStepThree = $fixFieldsStepThree;
        $this->fixFieldsStepFour = $fixFieldsStepFour;
        $this->fixFieldsStepFive = $fixFieldsStepFive;
        $this->fixFieldsStepSix = $fixFieldsStepSix;
        $this->fixFieldsStepSeven = $fixFieldsStepSeven;
        $this->fixFieldsStepEight = $fixFieldsStepEight;
        $this->fixFieldsStepNine = $fixFieldsStepNine;
    }

    /**
     * Fire command
     */
    public function handle()
    {
        // Step db: run db queries
        if ($this->argument('step') === "db") {
            \DB::statement("ALTER TABLE properties CHANGE short short VARCHAR(255) BINARY NOT NULL;");
            \DB::statement("ALTER TABLE `properties` DROP INDEX `properties_qualified_unique`");
            \DB::statement("ALTER TABLE `properties`DROP `qualified`");
            \DB::statement("ALTER TABLE `properties`DROP `namespace`");

            return;
        }

        // Step 1: generate properties with counts
        if ($this->argument('step') === "1") {
            $this->fixFieldsStepOne->start();

            return;
        }

        // Step 2: Removed unused fields from header array in headers table
        if ($this->argument('step') === "2") {
            $this->fixFieldsStepTwo->start();

            return;
        }

        // Step 3: Remove property fields that have no values from properties table
        if ($this->argument('step') === "3") {
            $this->fixFieldsStepThree->start();

            return;
        }

        // Step 4: remove empty fields
        if ($this->argument('step') === "4") {
            $this->fixFieldsStepFour->start();

            return;
        }

        // Step 5: Check if field & alternate fields exist together in same record
        if ($this->argument('step') === "5") {
            $this->fixFieldsStepFive->start();

            return;
        }

        // Step 6: Fix dup image subjects
        if ($this->argument('step') === "6") {
            $this->fixFieldsStepSix->start();

            return;
        }

        // Step 7: Fix dup occurrence subjects
        if ($this->argument('step') === "7") {
            $this->fixFieldsStepSeven->start();

            return;
        }

        // Step 8: Fix dup mixed subjects
        if ($this->argument('step') === "8") {
            $this->fixFieldsStepEight->start();

            return;
        }

        // Step 9: Fix dup mixed occurrence subjects
        if ($this->argument('step') === "9") {
            $this->fixFieldsStepNine->start();
        }
    }

}

/*
Image Field Count for accessRights: 0
Image Field Count for accessRights60851163df2fd69f13ba37d71c72f838: 0
Occurrence Field Count for accessRights: 581476
Occurrence Field Count for accessRights60851163df2fd69f13ba37d71c72f838: 0

Image Field Count for associatedCollectors: 0
Image Field Count for associatedCollectorse791965746ddaf7a81b3fc29f1f7a261: 0
Occurrence Field Count for associatedCollectors: 721111
Occurrence Field Count for associatedCollectorse791965746ddaf7a81b3fc29f1f7a261: 80396

Image Field Count for associatedSequences: 0
Image Field Count for associatedSequences38382421b95c2f7e6c0b6838c5282c8e: 0
Occurrence Field Count for associatedSequences: 0
Occurrence Field Count for associatedSequences38382421b95c2f7e6c0b6838c5282c8e: 140188

Image Field Count for associatedTaxa: 0
Image Field Count for associatedTaxaa6b2711796c6febd37188d8ecdd89548: 0
Occurrence Field Count for associatedTaxa: 1358723
Occurrence Field Count for associatedTaxaa6b2711796c6febd37188d8ecdd89548: 0

Image Field Count for collID: 0
Image Field Count for collIDa4cb5e4567a23d4ff27c6bf416bab135: 0
Occurrence Field Count for collID: 0
Occurrence Field Count for collIDa4cb5e4567a23d4ff27c6bf416bab135: 80396

Image Field Count for CountryCode: 0
Image Field Count for CountryCodef2ce: 0
Occurrence Field Count for CountryCode: 0
Occurrence Field Count for CountryCodef2ce: 0

Image Field Count for creator: 88624
Image Field Count for creator4f55: 0
Occurrence Field Count for creator: 0
Occurrence Field Count for creator4f55: 0

Image Field Count for cultivationStatus: 0
Image Field Count for cultivationStatusbe8e2f093a574ec81f6b70e804f1feab: 0
Occurrence Field Count for cultivationStatus: 721111
Occurrence Field Count for cultivationStatusbe8e2f093a574ec81f6b70e804f1feab: 80396

Image Field Count for dateEntered: 0
Image Field Count for dateEntered48bc85b8c7e56a76914286de1a73302d: 0
Occurrence Field Count for dateEntered: 508930
Occurrence Field Count for dateEntered48bc85b8c7e56a76914286de1a73302d: 1925

Image Field Count for duplicateQuantity: 0
Image Field Count for duplicateQuantitye814c75529b020b77af593122f0d0576: 0
Occurrence Field Count for duplicateQuantity: 508930
Occurrence Field Count for duplicateQuantitye814c75529b020b77af593122f0d0576: 1925

Image Field Count for eventDate: 0
Image Field Count for eventDate358a0bcae27ae88883e74e02c436cf96: 0
Occurrence Field Count for eventDate: 1358723
Occurrence Field Count for eventDate358a0bcae27ae88883e74e02c436cf96: 0

Image Field Count for format: 0
Image Field Count for format76d1: 1371157
Occurrence Field Count for format: 0
Occurrence Field Count for format76d1: 0

Image Field Count for genus: 0
Image Field Count for genusb6c5: 0
Occurrence Field Count for genus: 1687595
Occurrence Field Count for genusb6c5: 0

Image Field Count for Identifier: 0
Image Field Count for Identifiercc07f750fa45a11ca3b24525c2761d01: 0
Occurrence Field Count for Identifier: 0
Occurrence Field Count for Identifiercc07f750fa45a11ca3b24525c2761d01: 0

Image Field Count for language: 0
Image Field Count for language14f5643db829930ee537d6a4249a625f: 0
Occurrence Field Count for language: 1358723
Occurrence Field Count for language14f5643db829930ee537d6a4249a625f: 0

Image Field Count for language: 0
Image Field Count for language791b: 0
Occurrence Field Count for language: 1358723
Occurrence Field Count for language791b: 0

Image Field Count for localitySecurity: 0
Image Field Count for localitySecuritya623df43e509dc137563a1d62ff15c0c: 0
Occurrence Field Count for localitySecurity: 721111
Occurrence Field Count for localitySecuritya623df43e509dc137563a1d62ff15c0c: 80396

Image Field Count for localitySecurityReason: 0
Image Field Count for localitySecurityReason56ec449a8b8677da7e90de4eb7502dcb: 0
Occurrence Field Count for localitySecurityReason: 721111
Occurrence Field Count for localitySecurityReason56ec449a8b8677da7e90de4eb7502dcb: 80396

Image Field Count for modified: 2555
Image Field Count for modified24ff21e4ac9671d885b67312d0b2ade5: 0
Occurrence Field Count for modified: 1358723
Occurrence Field Count for modified24ff21e4ac9671d885b67312d0b2ade5: 0

Image Field Count for observerUid: 0
Image Field Count for observerUidec58f83c8c03e9bf217410a20dcf2f26: 0
Occurrence Field Count for observerUid: 508930
Occurrence Field Count for observerUidec58f83c8c03e9bf217410a20dcf2f26: 1925

Image Field Count for preparations: 0
Image Field Count for preparationse45fdcadeca5aee3cb6f49f114ed8e1b: 0
Occurrence Field Count for preparations: 1358729
Occurrence Field Count for preparationse45fdcadeca5aee3cb6f49f114ed8e1b: 944

Image Field Count for processingStatus: 0
Image Field Count for processingStatus8b5228dbe6d9c0ccef2fe0f490ae7215: 0
Occurrence Field Count for processingStatus: 508930
Occurrence Field Count for processingStatus8b5228dbe6d9c0ccef2fe0f490ae7215: 1925

Image Field Count for provider: 0
Image Field Count for provider74d2: 0
Occurrence Field Count for provider: 0
Occurrence Field Count for provider74d2: 0

Image Field Count for recordEnteredBy: 0
Image Field Count for recordEnteredByfbee1981a786cd01fafece99f739d6a6: 0
Occurrence Field Count for recordEnteredBy: 1239669
Occurrence Field Count for recordEnteredByfbee1981a786cd01fafece99f739d6a6: 119054

Image Field Count for recordID: 0
Image Field Count for recordID1fd02e92c4bd9b40ed8041b690de4bb3: 0
Occurrence Field Count for recordID: 0
Occurrence Field Count for recordID1fd02e92c4bd9b40ed8041b690de4bb3: 43385

Image Field Count for recordID: 0
Image Field Count for recordIDce2dbfd038a66c3b7aa7a8e4a56fc1ac: 0
Occurrence Field Count for recordID: 0
Occurrence Field Count for recordIDce2dbfd038a66c3b7aa7a8e4a56fc1ac: 75669

Image Field Count for rights: 0
Image Field Count for rights4a059cd1b10f14c2604009c22c3f0f1a: 0
Occurrence Field Count for rights: 581476
Occurrence Field Count for rights4a059cd1b10f14c2604009c22c3f0f1a: 0

Image Field Count for rights: 0
Image Field Count for rightsf2e8: 1371157
Occurrence Field Count for rights: 581476
Occurrence Field Count for rightsf2e8: 0

Image Field Count for rightsHolder: 0
Image Field Count for rightsHolderacd51d2b6a51b0ef43c0acb6adfb172a: 0
Occurrence Field Count for rightsHolder: 581476
Occurrence Field Count for rightsHolderacd51d2b6a51b0ef43c0acb6adfb172a: 0

Image Field Count for source: 0
Image Field Count for source6e38: 0
Occurrence Field Count for source: 0
Occurrence Field Count for source6e38: 0

Image Field Count for sourcePrimaryKey-dbpk: 0
Image Field Count for sourcePrimaryKey-dbpkaa2260bdf8ef33b49b225bdd43459521: 0
Occurrence Field Count for sourcePrimaryKey-dbpk: 700050
Occurrence Field Count for sourcePrimaryKey-dbpkaa2260bdf8ef33b49b225bdd43459521: 80396

Image Field Count for specificEpithet: 0
Image Field Count for specificEpithete0c2: 0
Occurrence Field Count for specificEpithet: 0
Occurrence Field Count for specificEpithete0c2: 1687595

Image Field Count for substrate: 0
Image Field Count for substratef3f38c008a02d8afb50aa6637e07efc5: 0
Occurrence Field Count for substrate: 721111
Occurrence Field Count for substratef3f38c008a02d8afb50aa6637e07efc5: 80396

Image Field Count for taxonID: 0
Image Field Count for taxonIDd400489e5292977038aa17dc95d33d29: 0
Occurrence Field Count for taxonID: 0
Occurrence Field Count for taxonIDd400489e5292977038aa17dc95d33d29: 1274036

Image Field Count for type: 1368602
Image Field Count for type109f: 2555
Occurrence Field Count for type: 0
Occurrence Field Count for type109f: 0

Image Field Count for verbatimAttributes: 0
Image Field Count for verbatimAttributesb633ef219930c00bb441d929c2a5bb0e: 0
Occurrence Field Count for verbatimAttributes: 721111
Occurrence Field Count for verbatimAttributesb633ef219930c00bb441d929c2a5bb0e: 80396


 */