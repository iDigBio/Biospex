const axios = require('axios');
const Pusher = require('pusher-js');
const https = require('https');
const pusher = new Pusher('ZOONIVERSE_PUSHER_ID', { cluster: 'PUSHER_APP_CLUSTER' });
const channel = pusher.subscribe('panoptes');

const config = {
    httpsAgent: new https.Agent({
        rejectUnauthorized: false,
    }),
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'Authorization': 'Bearer '.concat('API_TOKEN')
    }
}

channel.bind('classification', function (data) {
    axios.post(
        'API_URL/API_VERSION/panoptes-pusher',
        JSON.stringify(data),
        config
    ).catch(console.log);
});