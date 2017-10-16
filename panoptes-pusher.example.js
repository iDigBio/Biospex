var request = require('request-json');
var client = request.createClient('http://api.biospex.loc');

var Pusher = require('pusher-client');
var socket = new Pusher('79e8e05ea522377ba6db');
var panoptes = socket.subscribe('panoptes');

panoptes.bind('classification',
    function(data) {
        if (data['project_id'] !== "1558")
            return;

        client.post('panoptes-pusher', JSON.stringify(data), function(err, res, body) {
            if (err)
                console.log(err.message);
        });
    }
);