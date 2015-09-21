<?php
use Illuminate\Database\Migrations\Migration;

class RenameSubjectdocsToSubjects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $connection = DB::connection('mongodb');
        $client = $connection->getMongoClient();
        $db = $connection->getMongoDB();
        $client->admin->command([
            'renameCollection' => $db . '.subjectdocs',
            'to'               => $db . '.subjects'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $connection = DB::connection('mongodb');
        $client = $connection->getMongoClient();
        $db = $connection->getMongoDB();
        $client->admin->command([
            'renameCollection' => $db . '.subjects',
            'to'               => $db . '.subjectdocs'
        ]);
    }
}
