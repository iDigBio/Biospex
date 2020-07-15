use biospex;

db.panoptes_transcriptions.count();
db.pusher_transcriptions.count();

// Remove duplicates from pusher_transcriptions
db.pusher_transcriptions.dropIndex("classification_id_1");

let duplicates = [];
db.pusher_transcriptions.aggregate([
        {
            $group: {
                _id: {classification_id: "$classification_id"},
                dups: {"$addToSet": "$_id"},
                count: {"$sum": 1}
            }
        },
        {
            $match: {
                count: {"$gt": 1}
            }
        }
    ], {allowDiskUse: true}
).forEach(function (doc) {
    doc.dups.shift();
    doc.dups.forEach(function (dupId) {
            duplicates.push(dupId);
        }
    )
});

db.pusher_transcriptions.remove({_id: {$in: duplicates}});
db.pusher_transcriptions.createIndex({"classification_id": 1}, {unique: true});

// Unset for transcription_id for pusher_transcriptions
db.pusher_transcriptions.update(
    {},
    {$unset: {"transcription_id": 1}},
    false, true
);

//Unset for expedition_uuid for pusher_transcriptions
db.pusher_transcriptions.update(
    {},
    {$unset: {"expedition_uuid": 1}},
    false, true
)

// Create unique subject_id on reconciles.
db.reconciles.createIndex({"subject_id": 1}, {unique: true});

// Remove all documents in panoptes_transcriptions
db.panoptes_transcriptions.remove({});

// Get final pusher_transcriptions count
db.panoptes_transcriptions.count();
db.pusher_transcriptions.count();