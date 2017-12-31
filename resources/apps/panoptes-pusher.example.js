let axios = require('axios');
let Pusher = require('pusher-client');
let https = require('https');


let pusher = new Pusher('79e8e05ea522377ba6db');
let channel = pusher.subscribe('my-channel');

const instance = axios.create({
    baseURL: 'https://api.biospex2.loc',
    httpsAgent: new https.Agent({
        rejectUnauthorized: false
    }),
    headers: {
        'Content-Type': 'application/vnd.biospex.v1+json',
        'Accept': 'application/vnd.biospex.v1+json'
    }
});

channel.bind('classification', function (data) {
    if (data['project_id'] !== "1558")
        return;

    instance.post('/panoptes-pusher', JSON.stringify(data))
        .catch(err => console.log(`error: ${err.stack}`));
});